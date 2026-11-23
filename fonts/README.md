# Fonts

The certificates are drawn with GD, and GD needs a font file on disk. These
six are DejaVu, cut down to the characters a certificate can hold: Latin with
its accents, Greek, Cyrillic, Vietnamese, punctuation and currency signs.
Whole DejaVu is 2.8 MB, this is 488 KB, and nothing a European board writes
is missing.

`LICENSE_DEJAVU.txt` is the licence they come under. The files are modified (glyphs
removed), which that licence allows.

## Using your own font

A board whose members write in a script DejaVu does not carry – Chinese,
Japanese, Korean, Thai – puts its own font in `arcade/fonts/` in the board
folder, under the same name as the file it replaces:

    arcade/fonts/serif.ttf
    arcade/fonts/serif-bold.ttf
    arcade/fonts/serif-italic.ttf
    arcade/fonts/sans.ttf
    arcade/fonts/sans-bold.ttf
    arcade/fonts/sans-light.ttf

A file that is there is used instead of the one here, and only for the names
it replaces – one file is enough, the rest stay as they are. The folder sits
outside the extension, so an update of the arcade leaves it alone.

Arabic and Hebrew need more than a font: GD draws the letters one after
another without joining them and without turning the line around, so the
result is wrong whatever font is used.
