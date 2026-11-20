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
- 0.21 – jackpot per game with three-level settings
- 0.22 – outlier detection, external content check with player notice, resets for ratings and play counters, results of 0 leave the highscores
- 0.23 – arcade adapts to light and dark styles
- 0.24 – arcade block on the board index, powered-by line
- 0.25 – sitemap.xml for search engines
- 0.26 – notification when a record is beaten
- 0.27 – categories with a period (Christmas, Easter, Halloween)
- 0.28 – statistics page per member
- 0.29 – import of large game collections, protection for install files
- 0.30 – upload a game as a ZIP in the ACP

## Next

1. **Highscore certificates** for season winners (image, generated with GD)
2. **Strict mode for games**: an optional Content Security Policy on the play page lets the browser load game files only from the own server, so ads or trackers that a game would load later are blocked as well (games that really need an external file then need that file copied into the game folder)

Then comes a round of cleaning up, testing and going through the documentation, and with it **1.0.0**: the end of the beta, and the moment to submit the arcade to the phpBB extension database.

## 1.1 and later

Ideas for after 1.0.0, in no particular order:

- **Arcade in the profile and in posts**: a line under the avatar in topics (“Records: 12”) and a tab in the member's profile, both leading to the statistics page
- **Game of the week**: a game picked in the ACP that is shown on the arcade start page and in the block on the board index
- **Badges** beyond the medals, for things like 100 rounds played or a record in every game of a category
- **Comments on the game page** instead of only the link to the forum topic (the topic stays, so nothing is lost for the forum)
- **Games for two players** at the same game, which would need games that can do it – a big piece of work, listed here as a wish rather than a plan

What the arcade will not get: a page in front of the game, of the kind other arcades use to show their own advertising.

## phpBB 4

phpBB 4.0 changes a lot under the hood (no ACP/MCP/UCP modules any more, Doctrine instead of the phpBB DBAL, services only by dependency injection, Symfony 7). The arcade will get its own branch for phpBB 4 once phpBB 4 reaches beta / release candidate and its interfaces are stable. Both versions are then offered side by side – the phpBB 3.3 version keeps getting fixes (and new features where possible) as long as phpBB 3.3 itself is supported.

## Later / optional

- To think about: jackpot top-up by the admin – a field "top up the jackpot by …" when editing a game (one-off, e.g. for a weekend event)
- To think about: jackpot base amount – after a win the pot starts at a fixed amount instead of 0 (global, per category, per game like the other points settings); note that these points are created by the board, not paid by players
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
