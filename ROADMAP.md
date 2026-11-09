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

## Next

1. **Tournaments / championships**: the admin picks several games and a period; overall ranking across the games, countdown, prizes (Ultimate Points)
2. **Favourites**: members mark games with a heart; their favourites appear at the top of the arcade
3. **Medals next to posts**: first, second and third places (🥇🥈🥉) instead of only the number of records
4. **Bulk editing in the ACP**: set minimum play time, maximum score and point values for many games at once
5. **"Report game"**: button with reasons (score not saved, graphics error, not working on mobile …) and a list in the ACP
6. **Outlier detection** for the plausibility check: results far above the previous record or the player's own best (e.g. 20 times as high) are flagged with a reason; only with enough history per game, factor adjustable, "notice only" mode in which the score counts immediately and is only marked for the admin – nobody is blocked by mistake
7. **Dark styles**: arcade CSS adapts to the colours of any style instead of fixed light colours
8. **Arcade block on the board index**: optional block with new games and categories next to the latest records
9. **Statistics page** per member: favourite game, play time, best ranks
10. **Highscore certificates** for season winners (image, generated with GD)

## phpBB 4

phpBB 4.0 changes a lot under the hood (no ACP/MCP/UCP modules any more, Doctrine instead of the phpBB DBAL, services only by dependency injection, Symfony 7). The arcade will get its own branch for phpBB 4 once phpBB 4 reaches beta / release candidate and its interfaces are stable. Both versions are then offered side by side – the phpBB 3.3 version keeps getting fixes (and new features where possible) as long as phpBB 3.3 itself is supported.

## Later / optional

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
