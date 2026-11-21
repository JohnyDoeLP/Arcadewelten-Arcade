# Changelog

## 0.31.0-beta (2026-11-21)

- **Own page “Add games” in the ACP**, between “Games” and “Categories”. The upload field and the list of games found in the games folder moved there. The games page now holds only the installed games and loads faster, because the install files of the games found are no longer read every time you open it
- The games page shows a line when new games are lying in the games folder, with a link to the new page – for everybody who uploads over FTP and would otherwise not notice
- In the list of games found, games that load something from another server are now at the top of the page instead of in alphabetical order, so they are seen before the whole page is imported

## 0.30.0-beta (2026-11-20)

- **Upload a game as a ZIP:** for boards without FTP access there is now an upload field in the ACP above the list of games found. The archive holds the game folder with its install file, the arcade unpacks it into the games folder, and the game then appears in the list and is imported as usual. The ACP names the largest file size the server allows; for a big collection FTP stays the way
- The archive is treated as something from the outside: paths that point out of the game folder are refused, and of all the files only the install file may be a PHP program. Anything else of that kind – further PHP files, `.htaccess`, shell scripts – is left out and named in the report, so a prepared archive cannot plant a program on the server

## 0.29.0-beta (2026-11-20)

- **Large game collections:** the list of games found in the games folder is now shown 50 at a time, and only the install files of the current page are read. A folder with thousands of games no longer slows the page down (tested with 120 new games: half a second)
- **“Import all games found”** for big collections: the games are imported in chunks of 25 while the page sends itself on, with a progress line, so no time limit of the server is reached. Games that cannot be read are skipped and counted at the end. No topics are announced for these, which would flood the forum (tested: 120 games in 7 seconds)
- **Protect install files:** a button in the settings writes an `.htaccess` into the games folder so the install files of games cannot be called from the address bar – they are data for the arcade, which only ever reads them. Images, SWF and HTML files stay reachable, and the protection can be removed again with one click (Apache only)

## 0.28.3-beta (2026-11-20)

- Settings: the two optional lines (`arcade/gamedata/` folder and `newscore.php`) no longer say “not found” in red, but “not there – only needed for the games named above”, so a fresh board does not look broken
- Settings: when the games folder is missing, the full path the arcade expects is now named, along with the usual reasons (wrong place, upper case, permissions)

## 0.28.2-beta (2026-11-19)

- Statistics page: “played most” has a “show all” that folds out the remaining games, and “best places” links to the member's full list of records

## 0.28.1-beta (2026-11-19)

- Statistics page, “best places”: within one place the newest result comes first instead of alphabetical order, so with many first places the fresh records are the ones on show. The date of the result is named as well

## 0.28.0-beta (2026-11-19)

- **Statistics page per member:** rounds played, games played, favourites and ratings, the gold, silver and bronze places, the games played most, the best places, and the duel and tournament record. Reachable with the “My statistics” button in the arcade and from the hall of fame; members only, like the highscores. Everything is worked out from what the arcade stores anyway – no play time or anything else is recorded for it. Lists that name games only hold games the reader may see

## 0.27.1-beta (2026-11-18)

- Category form: the two date fields of a period sit on one line, and the explanation below them has some air

## 0.27.0-beta (2026-11-18)

- **Categories with a period:** a category can be set to be visible only in a certain period – once between two dates, or every year between two days, for Christmas, Easter or Halloween. A yearly period may run over new year (1 December to 6 January). Outside it the category and its games are invisible to everybody: no tile, no games in the list, no highscores, no entry in the arcade block and the sitemap, and a direct link answers 404. Records, ratings and comments are kept and are back next time. Administrators still see the category, marked “hidden right now” in the category list

## 0.26.0-beta (2026-11-17)

- **Notification when a record is beaten:** whoever loses first place in a game gets a notification with the new record and their own score, and a link to the highscores. Only first place counts; being overtaken further down the list, improving your own record and an equal score trigger nothing. Every member can switch it off (bell and email separately) in their notification settings, and there is an ACP switch for the whole board

## 0.25.0-beta (2026-11-17)

- **Sitemap for search engines:** the arcade provides its own `sitemap.xml` with its start page, the categories and the games guests may play, plus the tournament page. It always describes what a guest sees, whoever asks for it; categories with a password, categories limited to user groups, games without guest access and the highscores (which need a login) stay out. The addresses come from the board's own server settings and carry no session id. New ACP setting with the address and the line for robots.txt
- The sitemap answers 404 while it is switched off or guests may not use the arcade

## 0.24.0-beta (2026-11-16)

- **Arcade block on the board index:** at the bottom of the board index the arcade now shows its newest games as tiles, a line with all visible categories and their game counts, and – while a tournament runs – a line with its name and end. Visible to everybody who may use the arcade, guests and search engines included; hidden, password and group-only categories stay out. Three new ACP settings: block on/off, number of games (1–12), category line on/off
- Game cards: long game names end with “…” before the favourite heart; the full name shows on hover
- Credit line is now “Spielhalle powered by Arcadewelten Arcade” and links to the arcade section on arcadewelten.eu
- Duels: the table columns line up under their headings, like in the tournament tables

## 0.23.1-beta (2026-11-15)

- Styles with a dark page background but dark body text (e.g. Silver Xmas Modern): the game list with search and sorting, favourites, “recently played” and the cost line on the game page now sit in phpBB panels, so the style's own panel colours keep all text readable; the games heading on the tournament page is a page heading

## 0.23.0-beta (2026-11-15)

- **Dark styles:** the arcade now follows the colours of the board style. Tiles, cards and lines are neutral and see-through instead of white, the selected category and hover frames use the style's link colour, table headings and the note about other servers on the pay page use the style's text colour. Light styles look nearly unchanged
- Style authors can adjust the arcade colours with CSS variables (`--aw-line`, `--aw-tile-bg`, `--aw-note-bg`, … at the top of `arcade.css`)

## 0.22.5-beta (2026-11-14)

- Pay page note about other servers: the text uses the full width, names the real button (“Play for 100 Points”) and has a tick box “Do not show this note for this game again” (stored in the player's browser only, shared with the prompt of free games)

## 0.22.4-beta (2026-11-14)

- Four new bundled category images for seasonal categories: Easter (bunny, egg) and Christmas (gift, bauble), selectable in the category image list; 22 bundled images in total (CC0 like the others)

## 0.22.3-beta (2026-11-13)

- Games with costs and other servers: the note about the other servers now appears on the pay page, right above the button. Nothing is charged before the player has seen it, and after paying the game starts at once without a second prompt. Free games keep the prompt on the game page
- After a round, the reward for the round is now named before a jackpot win, so the two amounts no longer read like one

## 0.22.2-beta (2026-11-13)

- ACP → Games loads much faster with many games in the folder: the import list remembers the results of reading and checking each game folder until a file in it changes, and already installed games are no longer inspected on every visit
- ACP → Games: “Check all games for external content” is now a button instead of a small text link
- External content check: the board's own domain is recognised with and without “www.” as well as all its subdomains, on any domain

## 0.22.1-beta (2026-11-13)

- External content check: far fewer false alarms. Plain links (`<a href>`, e.g. “share on Twitter”) and placeholder addresses (`.invalid`, `.test`, …) are no longer reported; in Flash games only addresses of files a game could load (e.g. `.swf`, `.xml`, `.php`) count, not the lists of game sites or “more games” links inside old games. Please run “check all games” once after the update

## 0.22.0-beta (2026-11-13)

- **Results of 0** no longer go into the highscores: they count as played, but give no place, no trophy, no duel and no reward per round. Existing 0 entries are removed on update
- **Reset ratings and play counters** in three levels: per game (game edit), per category (category edit, for all its games) and globally (ACP → Settings, with confirmation)
- **Outlier detection:** results far beyond the game’s record or the player’s own best (default 20 times, “lowest wins” correspondingly) go to the review list with the reason. Only with enough history (default 5 entries / own attempts), factor and history adjustable, 0 = off. Optional “only report”: the result counts at once and the review list shows a note with “Done”
- **External content check:** the arcade searches a game’s own files for addresses of other servers (ads, tracking, CDN libraries, embeds of game portals) – locally only, no lookups on the internet
  - Warning in the import list, the games list and the game edit page; checked automatically on import and when saving a game, plus “check all games” in the ACP (needed once after the update and after FTP changes)
  - While an active game names other servers, the privacy section no longer says “no data to third parties” and the settings page reminds the admin to add the services to the privacy policy
  - Optional (on by default): players see a note with the servers before such a game starts; the game is only loaded after their click, “do not ask again” is kept in their browser only

## 0.21.0-beta (2026-11-12)

- **Jackpot (Ultimate Points, off by default):** part of every play cost goes into a pot per game. Whoever beats the all-time record of that game wins the whole pot; then it starts again at 0
  - Share in percent with three levels like the other points settings: global default (ACP → Settings, with on/off switch), per category and per game (empty = inherit, 0 = no jackpot there); also in the bulk editing
  - Current pot on the game card and the play page; message “JACKPOT! New all-time record …” after a win; admin log entry
  - The all-time record is kept on the game itself, so it survives season resets; the current best scores become the record on update. Resetting a game’s highscores in the ACP also starts a fresh record (the pot stays)
  - Records from the review list are only paid after approval; simultaneous results can never pay a pot twice; without Ultimate Points the jackpot does not appear at all
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
