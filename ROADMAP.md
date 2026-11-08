# Roadmap

Ideas for upcoming versions of Arcadewelten Arcade, roughly in the planned order.
Suggestions are welcome – the list grows with the community.

## Done

- 0.11 – automatic arcade section in the board's privacy policy
- 0.12 – game ratings (1–5 stars), sortable game list
- 0.13 – cinema mode ("lights off"), mobile/touch games with fullscreen start, "recently played" bar
- 0.14 – permission "Can use the arcade", categories visible only for selected user groups
- 0.15 – duels with phpBB notifications, "NEW" badge, ACP version check

## Next

1. **Tournaments / championships**: the admin picks several games and a period; overall ranking across the games, countdown, prizes (Ultimate Points)
2. **Forum integration (ArcadeBot)**: a bot user chosen in the ACP posts arcade news into a chosen forum
   - "[Game installed] – name": one topic per game with image, category, description, size and a working play link (stored as finished text, no placeholders); the topic doubles as the game's discussion thread, linked from the play page ("Tips & discussion"); optional collective post for big imports
   - "[Top 10 of the arcade] – season": new topic at every season close with places, records, season prizes (Ultimate Points) and the end of the next season – an archive of monthly winners
   - news forum selectable per category, so 18+ games are announced in a forum only the 18+ group can read
   - every post type can be switched off; the privacy section mentions the posts
3. **Statistics page** per member: favourite game, play time, best ranks
4. **Highscore certificates** for season winners (image, generated with GD)

## Later / optional

- Game downloads, switchable by the board admin
- ZIP upload of games in the ACP (only allowed file types, no PHP files in the web folder)
- Discord webhook for new records – optional and off by default, because it sends data to an external service
- Own HTML5 games and a free starter pack (separate repository)

## Decided against

- Embedding external games via iframe (e.g. itch.io): this would load third-party content and cookies and break the "no external connections" promise. Instead, games whose licence allows it are hosted on the board itself.
