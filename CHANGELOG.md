# Changelog

## Unreleased

- Privacy section: the paragraph about external connections now clearly refers to the arcade itself; advertising the board shows on all pages is covered by the rest of the privacy policy

## 0.20.0-beta (2026-11-11)

- **Drop-down menu in the header:** a click on “Arcade” opens a menu with arcade, tournaments (with a “live” marker while one runs), duels (members only, with the number of open challenges) and “random game”. Tournaments and duels only appear if switched on; the whole menu and “random game” can be switched off in the settings (then the link leads straight to the arcade as before)
- **Random game:** picks an active game from the categories the visitor may see – never from categories with a password; guests only get games released for guests
- Privacy section: new paragraph about the browser’s local storage (cinema mode setting, save data of Flash games stored by Ruffle)

## 0.19.0-beta (2026-11-11)

- **Tournament changes in the forum:** a tournament remembers its start topic. If the end, the games or the prizes of a running tournament change in the ACP, the ArcadeBot replies there (“Tournament change: the tournament now ends on … (previously …)”, “New: …”, “No longer included: …”, “New prizes: …”), so subscribers get a notification. Games from categories with a password or group visibility are only counted. When the tournament ends, the result stays a separate topic and the start topic gets a short final reply linking to it. Deleted or locked start topics are left alone
- **Delete game files (optional):** deleting a game in the ACP now offers the checkbox “Also delete the game folder from the server” (unticked by default). Only the game’s own folder directly inside the games folder is removed – never a folder another game uses, never anything outside, symbolic links are removed without following them. Files PHP may not delete (e.g. uploaded by FTP under a different user) are listed so they can be removed by FTP

## 0.18.2-beta (2026-11-10)

- Fix: in tournaments a result of 0 earned a place and tournament points. Only results above 0 count now; existing 0 results disappear from the ranking at once

## 0.18.1-beta (2026-11-10)

- Fix: a running tournament could not be saved in the ACP (“Please enter start and end”), because the browser does not send the locked start field
- Tournament pages: values are centred under their column headings, the prize column shows the points name (e.g. “900 Points”)

## 0.18.0-beta (2026-11-10)

- **Tournaments:** the admin picks several games and a period (ACP → Tournaments). Per game every member’s best result within the period counts; places 1–8 earn tournament points (10, 8, 6, 5, 4, 3, 2, 1), their total is the overall ranking (tie: first, second, third places, then games played). Normal highscores are not affected
  - Tournament list (running, planned, finished) and a page per tournament with live countdown, overall ranking and the places per game; banner in the arcade while a tournament runs, hint on the play page of every tournament game, note after a result that counted
  - Optional prizes in Ultimate Points for places 1–3, paid automatically when the tournament ends
  - ArcadeBot: topic “[Tournament]” at the start and “[Tournament result]” with the top 10 at the end (switchable). Games from categories with a password or group visibility are only counted there, not named
  - Games from group-limited categories are only shown to members who may see them; approved results from the review list count with the time they were played
  - Start and end run through phpBB’s cron and on the tournament pages, protected against double payouts; a running tournament keeps its start, finished ones can only be deleted
  - New switches “Tournaments” and “Post tournaments” in the settings; privacy section, “Who is online” and member deletion cover tournaments
- **Import:** wrongly filled installation files are recognised. If the installation file names an interface we do not support (e.g. `ARCADELIB_GAME`), the importer still looks into the game file; if it finds IBPro, IBPro v3 or Activity Mod there, the game is imported with that interface and the list says “interface detected from the game file”
- **Import:** clearer reasons for games that cannot be imported: game file damaged or not a Flash file, strongly compressed (ZWS) and not checkable, no known score interface found; unknown values from the installation file are shown by name instead of a raw language key
- ACP → Categories: the category list shows “hidden in “Who is online”” next to the password column for categories with that switch

## 0.17.1-beta (2026-11-09)

- **“Who is online”:** arcade pages no longer appear as “Board index”. The list shows “Is in the arcade”, “Playing “Game”” and “Viewing the highscores of “Game”” (with link), plus duels and records pages. Game names only appear if the viewer may see the game: never for categories with a password, for group-limited categories only to members of those groups, and for guests only games released for guests
- ACP → Categories: new switch “Hide game names in “Who is online”” per category
- README: Ruffle note on ActionScript 3 updated

## 0.17.0-beta (2026-11-09)

- **Favourites:** members mark games with a heart (on the game cards and the play page); “My favourites” appear at the top of the arcade. Up to 24 per member, only visible to the member. ACP switch (on by default); privacy section mentions favourites
- **Medals next to posts:** instead of only the number of records, posts and profiles show first, second and third places (🥇 2 · 🥈 1 · 🥉 4) over all games, “lower wins” games included. Members with only 2nd/3rd places now get the badge too. ACP switch (on by default)
- **Bulk editing in the ACP:** set minimum play time, maximum score and point values for all selected games at once; empty fields stay unchanged, “-” resets a point value to category/global
- **“Report game”:** members report problems on the play page (scores not saved, graphics/sound, phone/tablet, does not load, other) with an optional text; one open report per member and game. Reports appear in the ACP review list with a link to the game and “Done”. ACP switch (on by default); privacy section mentions reports
- README/INSTALL: optional `arcade.php` step in the “Switching from JV” section

## 0.16.2-beta (2026-11-08)

- Old links of the previous (JV) arcade keep working: copy the new file `contrib/root/arcade.php` to the board root (next to `index.php`). `arcade.php?mode=stats&u=…` (player statistics, e.g. in the old “Top 10” posts) then leads to the member's records page, all other old arcade links to the arcade (permanent redirect, also tells search engines the new address)
- “[Top 10 of the arcade]” posts link every player to their records page, in their group colour
- ACP → Categories: link “Clear selection (visible for everybody)” below the group selection
- ACP → Categories: the news forum choice shows which forum the “default news forum” is (or that none is chosen yet)

## 0.16.1-beta (2026-11-08)

- Forum integration: no more PHP warning “Undefined array key "enable_markdown"” from the extension “Markdown” (alfredoramos) when the ArcadeBot posts (e.g. at the season close). Bot posts now tell such extensions explicitly that they use BBCode, not Markdown.

## 0.16.0-beta (2026-11-08)

- **Forum integration (ArcadeBot):** a user chosen in the ACP posts arcade news as normal forum posts (subscriptions, notifications, search and feeds work as usual; stored as finished text in the board's default language, no placeholders)
  - “[Game installed] – name”: one topic per installed game with image, category, description, size, dimensions and links to play and to the highscores. The topic doubles as the game's discussion thread: “Tips & discussion” button on the play page and the highscores page (only for members who may read that forum)
  - collective topic “[Games installed] – x new games” when one import installs many games (ACP, default from 5 games, 0 = always single topics)
  - “[Top 10 of the arcade] – date” at every season close: top 10 players by records with their season prizes – an archive of monthly winners
  - default news forum plus an own news forum per category (e.g. an 18+ forum only the 18+ group can read)
  - bulk action “post to the forum” for games installed earlier
  - every post type can be switched off; the privacy section mentions the season ranking while it is posted

## 0.15.0-beta (2026-11-08)

- **Duels:** members challenge others from the highscores page of a game in which they have a score. Their personal best is frozen; the opponent has to beat it with a genuine result (same play session and plausibility check as the highscores, “lower wins” games included) before the deadline – otherwise the challenger wins. The opponent may decline.
  - phpBB notifications for the challenge and the result (bell; e-mail if chosen in the UCP, new group “Arcade notifications”)
  - new page “Duels” with challenges to answer, own challenges and finished duels; counter on the arcade page and a banner on the play page
  - duels past their deadline are closed by phpBB's cron (and whenever the duels page is opened)
  - only opponents who may use the arcade and see the game's category can be challenged; one open duel per pair and game; limit of open challenges per member
  - ACP: switch (on by default), longest duration (default 7 days), open challenges per member (default 5)
  - duels of deleted members or games are removed; the privacy section mentions duels while they are switched on
- **“New” badge** on games installed in the last X days (ACP, default 14, 0 = off); category tiles show “3 new”. Bulk actions in the game list: “mark as new” and “remove ‘New’ badge” (e.g. after a big import)
- ACP → Extensions: the version check now finds new versions of the arcade (via `version.json` on GitHub; only when you open the extension details or click “Re-check version”)
- Code checked with EPV, the phpBB extension coding standard (PHP_CodeSniffer), PHPCompatibility (PHP 8.2+) and a YAML check: no errors

## 0.14.1-beta (2026-11-07)

- Search engines can use the arcade again, as before 0.14: the permission “Can use the arcade” is now also granted to the group “Bots” (they see exactly what guests see – no highscores, no protected or group-limited categories). Remove it there if you want to keep search engines out.
- Visits of search engines no longer increase the “played x times” counter
- ACP: explanation on the categories page mentions the group visibility

## 0.14.0-beta (2026-11-07)

- New phpBB permission **“Can use the arcade”** (user permissions, tab “Misc”). Granted to guests, registered users and the usual user roles on installation, so nothing changes until you adjust it. Without it the arcade link is hidden, guests are asked to log in and members get a notice.
- Categories can be limited to **user groups** (ACP → Categories, “Visible for groups”). For everybody else the category and its games do not exist: no tile, not in the game list or search, not in “recently played”, the records page or the latest records on the board index; direct links answer “not found”. Administrators always see everything. Members of the groups see a small group icon on the tile.
- Category list in the ACP shows who can see each category

## 0.13.0-beta (2026-11-07)

- Cinema mode: "Lights off" button on the play page darkens the whole forum around the game; the choice is remembered in the browser. Click on the dark area or the button switches the light back on.
- Mobile/touch games: new switch per game (and as bulk action) in the ACP. Such games get a phone icon in the game list; on phones and tablets they start with a big "Play in fullscreen" button, wide games try to switch to landscape. Other games show a hint on touch devices that they need a keyboard or mouse.
- Fullscreen also works on browsers without element fullscreen (iPhone): the game then covers the whole window and gets a close button.
- "Recently played" bar at the top of the arcade for members (the last 6 games; at most 10 are stored per member, only visible to the member). Can be switched off in the ACP; included in the privacy section.
- Ratings: games in the "recently played" list count as played
- New `ROADMAP.md` with the planned features

## 0.12.0-beta (2026-11-07)

- Game ratings: members rate games with 1–5 stars (one changeable rating per game, only after playing it). Average and number of ratings on the play page, the highscores page and in the game list; without JavaScript the rating works as a normal form.
- Game list can be sorted by name, best rating, most played and newest
- ACP: ratings can be switched off (on by default), rating column in the game list, ratings of a game can be reset when editing it
- Ratings of deleted members are removed and the averages recalculated; the privacy section mentions ratings while they are switched on

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
