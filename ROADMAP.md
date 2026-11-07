# Roadmap

Ideas for upcoming versions of Arcadewelten Arcade, roughly in the planned order.
Suggestions are welcome – the list grows with the community.

## Done

- 0.11 – automatic arcade section in the board's privacy policy
- 0.12 – game ratings (1–5 stars), sortable game list
- 0.13 – cinema mode ("lights off"), mobile/touch games with fullscreen start, "recently played" bar
- 0.14 – permission "Can use the arcade", categories visible only for selected user groups

## Next

1. **Challenges / duels**: member A challenges member B in a game; B gets a phpBB notification (optionally by e-mail) and has X days to beat A's score
2. **Tournaments / championships**: the admin picks several games and a period; overall ranking across the games, countdown, prizes (Ultimate Points)
3. **Comments on games**: one forum topic per game, created automatically – moderation, reporting and notifications come from phpBB
4. **Statistics page** per member: favourite game, play time, best ranks
5. **New records in a forum topic or shoutbox**
6. **Highscore certificates** for season winners (image, generated with GD)

## Later / optional

- Game downloads, switchable by the board admin
- ZIP upload of games in the ACP (only allowed file types, no PHP files in the web folder)
- Discord webhook for new records – optional and off by default, because it sends data to an external service
- Own HTML5 games and a free starter pack (separate repository)

## Decided against

- Embedding external games via iframe (e.g. itch.io): this would load third-party content and cookies and break the "no external connections" promise. Instead, games whose licence allows it are hosted on the board itself.
