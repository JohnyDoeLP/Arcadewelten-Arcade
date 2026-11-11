# Roadmap

Ideas for upcoming versions of Arcadewelten Arcade, roughly in the planned order.
Suggestions are welcome – the list grows with the community.

## Done

- 0.11 – automatic arcade section in the board's privacy policy
- 0.12 – game ratings (1–5 stars), sortable game list
- 0.13 – cinema mode ("lights off"), mobile/touch games with fullscreen start, "recently played" bar
- 0.14 – permission "Can use the arcade", categories visible only for selected user groups
- 0.15 – duels with phpBB notifications, "NEW" badge, ACP version check
- 0.16 – forum integration (ArcadeBot): game topics as discussion threads, season top 10
- 0.17 – favourites, medals next to posts, bulk editing in the ACP, "report game"
- 0.18 – tournaments with overall ranking, countdown and prizes; importer recognises wrongly filled installation files
- 0.19 – tournament changes as replies in the start topic, optional deletion of game folders
- 0.20 – drop-down menu in the header with “random game”, privacy note on browser storage

## Next

1. **Jackpot (Ultimate Points, switchable)**: part of every play cost goes into a pot per game; whoever beats the all-time record of that game wins the whole pot, then it starts again at 0. Share set in three levels like the other points settings: global default (with on/off switch), overridable per category and per game (0 = no jackpot there). Current pot shown on the game card and the play page; records from the review list are only paid after approval; hidden without Ultimate Points
2. **Outlier detection** for the plausibility check: results far above the previous record or the player's own best (e.g. 20 times as high) are flagged with a reason; only with enough history per game, factor adjustable, "notice only" mode in which the score counts immediately and is only marked for the admin – nobody is blocked by mistake
3. **Dark styles**: arcade CSS adapts to the colours of any style instead of fixed light colours
4. **Arcade block on the board index**: optional block with new games and categories next to the latest records
5. **Statistics page** per member: favourite game, play time, best ranks
6. **Highscore certificates** for season winners (image, generated with GD)
7. **Warning for games that load external content**: the importer looks through a game's own files (locally, no lookups on the internet) for addresses on other servers, e.g. ad or tracking scripts, or games that are only an embed of an external game portal, and shows a note in the import list such as "loads content from portal.cdn.example.com". The admin decides; nothing is blocked automatically. While such a game is active, the privacy section no longer claims "no data is sent to third parties", and the ACP reminds the admin to add the external service to the privacy policy. Optionally, players are asked before such a game starts ("This game loads content from ajax.aspnetcdn.com. Continue?"); only after their click is the game loaded, the consent applies to that game only and is kept in the browser at most, never on the server. Optional "strict mode": a Content Security Policy on the play page lets the browser load game files only from the own server, so ads or trackers that a game would load later are blocked as well (games that really need an external file then need that file copied into the game folder)

## phpBB 4

phpBB 4.0 changes a lot under the hood (no ACP/MCP/UCP modules any more, Doctrine instead of the phpBB DBAL, services only by dependency injection, Symfony 7). The arcade will get its own branch for phpBB 4 once phpBB 4 reaches beta / release candidate and its interfaces are stable. Both versions are then offered side by side – the phpBB 3.3 version keeps getting fixes (and new features where possible) as long as phpBB 3.3 itself is supported.

## Later / optional

- To think about: own placement on every game card for members ("You: place 4 of 9", "not played yet")
- To think about: one line "Last record: <member> in <game>" on the category tiles, hidden on small screens so the tiles do not get crowded
- Notification for administrators when a game is reported (phpBB bell, e-mail optional in the UCP) – switchable, so nobody gets more notifications than wanted
- Points interface: other points systems can be connected (detected automatically when installed, or attached by other extensions through a phpBB event). The existing Ultimate Points support stays exactly as it is – nothing changes in Ultimate Points itself or in how the arcade uses it.
- Shoutbox support: new records and duel results in mChat or AJAX Chat, only offered in the ACP when one of them is installed – built once somebody actually uses it
- Group duels (group against group, average of the members) – only once the normal duels are well used; fairness with very different group sizes needs a good answer first
- Game downloads, switchable by the board admin
- ZIP upload of games in the ACP (only allowed file types, no PHP files in the web folder)
- Discord webhook for new records – optional and off by default, because it sends data to an external service
- Own HTML5 games and a free starter pack (separate repository)

## Decided against

- Own backup/export of the arcade: everything lives in the board's database and the `arcade/` folder, so a normal phpBB backup already contains games, highscores and trophies, and a board move takes the arcade along.
- Embedding external games via iframe (e.g. itch.io): this would load third-party content and cookies and break the "no external connections" promise. Instead, games whose licence allows it are hosted on the board itself.
