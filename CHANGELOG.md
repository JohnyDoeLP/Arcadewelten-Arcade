# Changelog

## 0.11.0-beta (2026-11-07)

- Privacy policy: an “Arcade” section is appended automatically to the board’s privacy policy (`ucp.php?mode=privacy`). The text follows the settings (guest play, plausibility check, category passwords, Ultimate Points, diagnostic mode). On by default, can be switched off in the ACP settings.
- Text blocks in `contrib/privacy.*.txt`: optional paragraph for the cookie of password-protected categories

## 0.10.5-beta (2026-11-07)

- Category tiles wrap into rows instead of a horizontal scroll bar
- Empty categories are no longer shown as tiles (they stay visible in the ACP)

## 0.10.4-beta (2026-11-06)

- Fix (Firefox): game keys no longer open the quick find bar ("search for text when you start typing") while the mouse is over an HTML5 game or the game is in fullscreen.

## 0.10.3-beta (2026-11-06)

- Fix: HTML5 games get the keyboard focus automatically (on load and when the mouse enters the game). Before, keys of some games (e.g. WASD in Radius Raid) went to the forum page and opened the browser's quick find.

## 0.10.2-beta (2026-11-06)

- Fix: the fullscreen button enlarged an invisible element instead of the game (Flash games), the game could then not be clicked. Now the whole game area goes fullscreen; a second click or Esc ends it.

## 0.10.1-beta (2026-11-06)

- 18 bundled category images (`images/cats/`, 144 × 144 WebP)
- Checked with the official phpBB Extension Pre-Validator (EPV): no errors; `stripslashes` replaced, two queries restructured, Ruffle licence files renamed to `.txt`
- ACP: category image picker with preview; own images in `images/arcade/cats/` appear in the list automatically; a custom path is still possible

## 0.10.0-beta (2026-11-06)

- Category tiles: image per category (own image or the image of the most played game), number of games and category champion
- Plausibility check: minimum play time (global and per game) and maximum score per game
- Review list in the ACP: implausible results are kept for approval instead of entering the highscores
- Installation guides `INSTALL.de.md` / `INSTALL.en.md`

## 0.9.2-beta (2026-11-06)

- Game files are loaded with `?v=<modification time>`, so a replaced game file is never taken from the browser cache.

## 0.9.1-beta (2026-11-06)

- Fix: scores of games that call getURL from inside an ActionScript function did not arrive under Ruffle (Ruffle sends the function's locals). New tool `contrib/tools/ruffle_function_geturl_patch.py` patches such SWF files.
- Score handler ignores variables with the value "undefined" and accepts comma decimals.

## 0.9.0-beta (2026-11-06)

First public development version.

- Arcade with categories, search, pagination and optional category passwords
- Highscores with trophies for places 1–3, hall of fame with avatars, records page per member
- Records next to posts, in profiles and on the board index (members only)
- Guest mode: only games released for guests, no highscores for guests
- Flash games via self-hosted Ruffle 0.7.1, long browser caching via `.htaccess`
- Score interfaces: IBPro, IBPro v3/v32, Activity Mod (`contrib/root/newscore.php`), HTML5 (`parent.awArcade.submitScore()`)
- Import of games from a games folder; score interface detected from the SWF
- Ultimate Points: cost per game, reward per round, season prizes, season close with preview and log entry
- Play counter reset (with season close, globally or per game)
- Diagnostics switch: logs incoming score requests to the admin log
- Optional credit line on arcade pages (can be switched off)
- Members' arcade data is removed when a member is deleted
- Requires phpBB 3.3 and PHP 8.2+
