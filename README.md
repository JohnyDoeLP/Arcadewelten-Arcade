# Arcadewelten Arcade for phpBB 3.3

**Version 0.11.0-beta – in development, please test on a copy of your board first.**

A self-hosted arcade for phpBB 3.3: game list with categories, highscores, trophies and a hall of fame.
No external connections, no ads, no licence server, no play limits. Open source under the GPL-2.0.

Step-by-step installation: see `INSTALL.en.md`. Deutsche Anleitung: `README.de.md` und `INSTALL.de.md`.

## Features

- Game list with category tiles (image, number of games, category champion), search and pagination; optional password per category; 18 bundled category images, own images can simply be put into `images/arcade/cats/`
- Highscores per game with trophies for places 1–3, hall of fame (podium with avatars)
- Records in the forum: next to posts, in profiles, latest records on the board index
- Guests only see games you release for them; no highscores or protected categories for guests
- Flash games via [Ruffle](https://ruffle.rs) (self-hosted, no CDN)
- Import of existing games from a games folder (install files are parsed, never executed); the score interface is detected from the game file itself
- Optional: [Ultimate Points](https://github.com/dmzx/Ultimate-Points-Extension) integration – cost per game, reward per round, season prizes for places 1–3, monthly season close with preview
- Plausibility check against manipulated scores: minimum play time and maximum score; suspicious results go to a review list in the ACP
- ACP: settings, games, categories, review list, season close, play counter reset, diagnostics log
- GDPR friendly: only the best score, date and number of tries per member and game are stored; data of deleted members is removed

## Supported score interfaces

| Game type | Interface |
| --- | --- |
| Flash | IBPro (`index.php?act=Arcade&do=newscore`) |
| Flash | IBPro v3 / v32 (`index.php?autocom=arcade&do=…`) |
| Flash | Activity Mod (`newscore.php`) – copy `contrib/root/newscore.php` into the board root |
| HTML5 | Own interface: the game calls `parent.awArcade.submitScore(score)` |

Not supported: games bound to the JV-Arcade server (`*_jvh5` HTML5 packages, `PHPBBARCADE_GAME`).

## Requirements

- phpBB 3.3.x
- PHP 8.2 or newer
- Optional: Ultimate Points extension (dmzx)

## Installation

Short version (details with folder overview in `INSTALL.en.md`):

1. Upload the folder `arcadewelten` into `ext/` of your board, so you get `ext/arcadewelten/arcade/`.
2. Put your games into `arcade/games/<name>/` in the board root (per game: `<name>.php`, `<name>.swf`, image).
   IBPro v3 games also need their data in `arcade/gamedata/<name>/`.
3. ACP → Customise → Manage extensions → enable “Arcadewelten Arcade”.
4. ACP → Extensions → Arcade → Games → “Import games from the games folder”.
5. Only for Activity Mod games: copy `contrib/root/newscore.php` into the board root.

The arcade is reached at `app.php/arcadehall` (or `/arcadehall` with URL rewriting) and via the “Arcade” link in the navigation bar.

## Switching from JV phpBB Arcade

1. Back up your database.
2. Disable “phpBB Arcade” and “JVA Core” in the ACP and choose “Delete data”.
3. Delete `ext/jv/`. In the folder `arcade/` keep only `games/` and `gamedata/`.
4. Install this extension and import your games.

## HTML5 games

An HTML5 game lives in `arcade/games/<name>/` with an `index.html`, an install file `<name>.php` with `'game_type' => GAME_TYPE_HTML5` and an image.
At game over it calls:

```javascript
parent.awArcade.submitScore(finalScore);
```

The arcade page takes care of the session and the form token.

## Privacy

The arcade automatically appends an “Arcade” section to the board’s privacy policy (`ucp.php?mode=privacy`). The text follows the settings (guests, plausibility check, category passwords, points, diagnostics) and can be switched off in the ACP. If you maintain your own privacy policy, use the text block in `contrib/privacy.en.txt`.

## Known limitations

- Flash games run through the Ruffle emulator. Older ActionScript 1/2 games work well, ActionScript 3 support in Ruffle is still incomplete.
- Scores of browser games can never be fully protected against manipulation. The arcade checks play sessions, the IBPro v3 checksum, a minimum play time and an optional maximum score per game.
- Games that send their score from inside an ActionScript function do not save scores under Ruffle without a patch. Patch them with `contrib/tools/ruffle_function_geturl_patch.py` (Python 3, keep a backup of the original). The diagnostics switch in the ACP shows `super=…` / `arguments=` for affected games.

## Licence

GPL-2.0-only, see `license.txt`.
Ruffle is licensed under MIT / Apache-2.0, see `styles/all/theme/ruffle/LICENSE_*`.

© 2026 Patrick Overmann – [arcadewelten.eu](https://arcadewelten.eu)

## Category images

The 18 images in `images/cats/` were made for Arcadewelten with AI assistance (ChatGPT) and are released under **CC0** – they may be used, modified and redistributed without restriction, also independently of this extension. They contain no third-party characters or trademarks.
