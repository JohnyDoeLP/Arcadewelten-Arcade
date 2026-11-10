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

## Next

1. **Import: detect wrongly filled installation files**: if the installation file declares an unknown score interface (e.g. `ARCADELIB_GAME` or a missing/unknown constant), the importer still looks into the game file itself instead of sorting the game out right away; if it finds IBPro, IBPro v3 or Activity Mod calls there, the game is imported with that interface and the ACP shows a note "interface detected from the game file". Example: four games that were labelled as Arcadelib but are really IBPro games. If nothing usable is found, the import list says why: game file missing, file damaged or not a Flash file, compressed format that cannot be checked (ZWS), no known score interface found, or an unknown constant from the installation file (shown by name instead of a raw language key)
2. **Tournaments / championships**: the admin picks several games and a period; overall ranking across the games, countdown, prizes (Ultimate Points)
3. **Outlier detection** for the plausibility check: results far above the previous record or the player's own best (e.g. 20 times as high) are flagged with a reason; only with enough history per game, factor adjustable, "notice only" mode in which the score counts immediately and is only marked for the admin – nobody is blocked by mistake
4. **Dark styles**: arcade CSS adapts to the colours of any style instead of fixed light colours
5. **Arcade block on the board index**: optional block with new games and categories next to the latest records
6. **Statistics page** per member: favourite game, play time, best ranks
7. **Highscore certificates** for season winners (image, generated with GD)

## phpBB 4

phpBB 4.0 changes a lot under the hood (no ACP/MCP/UCP modules any more, Doctrine instead of the phpBB DBAL, services only by dependency injection, Symfony 7). The arcade will get its own branch for phpBB 4 once phpBB 4 reaches beta / release candidate and its interfaces are stable. Both versions are then offered side by side – the phpBB 3.3 version keeps getting fixes (and new features where possible) as long as phpBB 3.3 itself is supported.

## Later / optional

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
