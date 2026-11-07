"""Arcadewelten Arcade – Ruffle workaround for old Flash arcade games.

Problem: When a game calls getURL("newscore.php", "_self", "POST") from inside an
ActionScript function, Ruffle sends the function's local variables (super,
arguments) instead of the timeline variables (game_name, score, ...). The
original Flash Player sent the timeline variables, so the score never arrives.

Fix: Before every such getURL inside a function, this tool inserts code that
copies game_name, score, gname and gscore into local variables. Ruffle then
sends them. The arcade ignores names with the value "undefined".

Usage:  python3 ruffle_function_geturl_patch.py original.swf patched.swf
Prints the number of patched call sites (0 = file not affected).
Always keep a backup of the original file.

(c) 2026 Patrick Overmann, arcadewelten.eu – GPL-2.0-only
"""
import sys, zlib, struct
NAMES = ['game_name', 'score', 'gname', 'gscore']

def ins_code():
    out = b''
    for n in NAMES:
        body = b'\x00' + n.encode() + b'\x00' + b'\x00' + n.encode() + b'\x00'
        out += b'\x96' + struct.pack('<H', len(body)) + body + b'\x1c' + b'\x3c'
    return out
INS = ins_code()

def parse(buf, start, end, depth, acts):
    """Collect actions as dicts with absolute positions; recurse into function bodies."""
    i = start
    while i < end:
        op = buf[i]; s = i; i += 1
        if op < 0x80:
            acts.append({'op': op, 's': s, 'e': i, 'depth': depth}); continue
        ln = struct.unpack('<H', buf[i:i+2])[0]; ds = i + 2; i = ds + ln
        a = {'op': op, 's': s, 'e': i, 'ds': ds, 'depth': depth}
        acts.append(a)
        if op in (0x9b, 0x8e):
            data = buf[ds:ds+ln]
            size = struct.unpack('<H', data[-2:])[0]
            a['cs'] = i; a['ce'] = i + size
            parse(buf, i, i + size, depth + 1, acts); i += size
        elif op == 0x94:  # With
            size = struct.unpack('<H', buf[ds:ds+2])[0]; a['cs'] = i; a['ce'] = i + size
        elif op == 0x8f:  # Try
            flags = buf[ds]; ts, cs_, fs = struct.unpack('<HHH', buf[ds+1:ds+7])
            a['try'] = (ts, cs_, fs)
    return acts

def patch_actions(buf):
    acts = parse(buf, 0, len(buf), 0, [])
    points = sorted(a['s'] for a in acts if a['op'] == 0x9a and a['depth'] > 0 and (buf[a['ds']] & 3))
    if not points:
        return buf, 0
    def np(x):
        return x + len(INS) * sum(1 for p in points if p < x)
    out = bytearray(); i = 0
    by_start = {a['s']: a for a in acts}
    while i < len(buf):
        if i in points:
            out += INS
        a = by_start.get(i)
        if a is None:  # should not happen; copy rest
            out += buf[i:]; break
        op = a['op']
        if op < 0x80:
            out.append(op); i = a['e']; continue
        data = bytearray(buf[a['ds']:a['e']])
        if op in (0x99, 0x9d):  # Jump / If
            off = struct.unpack('<h', bytes(data[:2]))[0]
            tgt = a['e'] + off
            data[:2] = struct.pack('<h', np(tgt) - np(a['e']))
        elif op in (0x9b, 0x8e):
            data[-2:] = struct.pack('<H', np(a['ce']) - np(a['cs']))
        elif op == 0x94:
            data[:2] = struct.pack('<H', np(a['ce']) - np(a['cs']))
        elif op == 0x8f:
            ts, cs_, fs = a['try']; b0 = a['e']
            t_end = b0 + ts; c_end = t_end + cs_; f_end = c_end + fs
            data[1:7] = struct.pack('<HHH', np(t_end) - np(b0), np(c_end) - np(t_end), np(f_end) - np(c_end))
        out.append(op); out += struct.pack('<H', len(data)); out += data
        # function bodies are emitted by the following loop iterations (they are separate actions)
        i = a['e']
    return bytes(out), len(points)

def rd_tags(d):
    pos = 0; res = []
    while pos + 2 <= len(d):
        tc = struct.unpack('<H', d[pos:pos+2])[0]; code = tc >> 6; ln = tc & 0x3f; hl = 2
        if ln == 0x3f: ln = struct.unpack('<I', d[pos+2:pos+6])[0]; hl = 6
        res.append((code, d[pos+hl:pos+hl+ln])); pos += hl + ln
        if code == 0: break
    return res, d[pos:]
def wr_tag(code, body):
    if len(body) < 0x3f and code not in (12, 59, 39):
        return struct.pack('<H', (code << 6) | len(body)) + body
    return struct.pack('<HI', (code << 6) | 0x3f, len(body)) + body

def patch_tags(d):
    tags, rest = rd_tags(d); out = b''; total = 0
    for code, body in tags:
        if code == 12:
            body, n = patch_actions(body); total += n
        elif code == 59:
            nb, n = patch_actions(body[2:]); body = body[:2] + nb; total += n
        elif code == 39:
            inner, n = patch_tags(body[4:]); body = body[:4] + inner; total += n
        out += wr_tag(code, body)
    return out + rest, total

def main(src, dst):
    b = open(src, 'rb').read(); sig = b[:3]; ver = b[3]
    d = zlib.decompress(b[8:]) if sig == b'CWS' else b[8:]
    nb = d[0] >> 3; hl = (5 + nb * 4 + 7) // 8 + 4
    tags, n = patch_tags(d[hl:])
    nd = d[:hl] + tags
    length = 8 + len(nd)
    payload = zlib.compress(nd, 9) if sig == b'CWS' else nd
    open(dst, 'wb').write(sig + bytes([ver]) + struct.pack('<I', length) + payload)
    print(n)
if __name__ == '__main__':
    main(sys.argv[1], sys.argv[2])
