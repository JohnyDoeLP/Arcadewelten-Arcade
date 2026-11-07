# Installation guide – Arcadewelten Arcade

This guide walks you through installation, game import and setup step by step.
Time needed: about 15 minutes plus uploading your games.

> **Before you start:** back up your database (ACP → Maintenance → Backup, or phpMyAdmin).
> This is a beta version – ideally test it on a copy of your board first.

## 1. Check the requirements

- phpBB **3.3.x** (ACP → index → “Board version”)
- PHP **8.2 or newer** (ACP → index → “PHP version”)
- FTP access to your web space
- optional: the **Ultimate Points** extension (dmzx) if games should cost or earn points

## 2. Upload the extension

1. Unzip the archive. It contains the folder `arcadewelten`.
2. Upload the whole folder `arcadewelten` via FTP into the `ext/` folder of your board.
3. It must look like this:

```
your-board/
└── ext/
    └── arcadewelten/
        └── arcade/
            ├── composer.json
            ├── ext.php
            └── …
```

If `composer.json` ends up one level deeper (e.g. `ext/arcadewelten/arcade/arcade/`), unzipping created one folder too many and phpBB will not detect the extension.

## 3. Enable the extension

1. ACP → **Customise** → **Manage extensions**
2. Click **Enable** next to “Arcadewelten Arcade”.
3. The navigation bar now shows an **Arcade** link, and the ACP has an **Arcade** section under **Extensions**.

## 4. Upload games

Put your games into the board root, one folder per game:

```
your-board/
└── arcade/
    ├── games/
    │   └── tetris2/
    │       ├── tetris2.php    ← install file
    │       ├── tetris2.swf    ← game
    │       └── tetris2.gif    ← image
    └── gamedata/              ← only for IBPro v3 games
        └── <gamename>/
```

- Install files in the common phpBB arcade format (`$game_data = array(...)`) are understood.
- IBPro v3 games also need their folder in `arcade/gamedata/`.
- Recommended permissions: folders **755**, files **644** (777 is not needed).

## 5. Import games

1. ACP → Extensions → Arcade → **Categories**: create categories (e.g. Action, Puzzle, Classics).
2. ACP → Extensions → Arcade → **Games**: at the bottom you find “Import games from the games folder”.
3. Tick the games, choose a category, click **Import selected games**.
4. Games marked red cannot be imported (e.g. game file missing or JV specific interface).

The score interface is detected from the game file itself during import. You can change it for every game under “Edit”.

## 6. Activity Mod games (only if you have any)

Games using the “Activity Mod” interface send their scores to `newscore.php` in the board root.
Copy `ext/arcadewelten/arcade/contrib/root/newscore.php` into the board root (next to `index.php`).
ACP → Arcade → Settings shows a green tick when it is in the right place.

## 7. Settings

ACP → Extensions → Arcade → **Settings**:

| Setting | Recommendation |
| --- | --- |
| Guests may play released games | Yes – and pick single games via “release for guests” |
| Minimum play time | 5 seconds (faster results go to the review list) |
| “Arcade: Arcadewelten Arcade” credit | appreciated, not required |
| Diagnostics | only for troubleshooting |
| Points (Ultimate Points) | cost, round reward, season prizes – optional |

Per category you can set an **image** (path relative to the board, e.g. `images/arcade/puzzle.png`) and a **password**.
Per game you can set **minimum play time**, **maximum score** and point values.

## 8. Update your privacy policy

A ready-made text block is in `contrib/privacy.en.txt`.

## 9. Test

1. Play a game until the end – the highscores must appear with “New personal best”.
2. If not: ACP → Settings → **Diagnostics** on, play again, then check ACP → Maintenance → **Admin log**.
   - No entry: the game did not send a score.
   - Entry with `super=…` / `arguments=`: the game sends from inside a function → patch it with `contrib/tools/ruffle_function_geturl_patch.py` (see README).
3. Switch diagnostics off again.

## Updating to a new version

1. ACP → Manage extensions → **Disable** “Arcadewelten Arcade” (not “Delete data”!).
2. Replace the folder `ext/arcadewelten/` via FTP with the new one.
3. **Enable** the extension again – new database fields are created automatically.
4. ACP → General → **Purge the cache**.

## Switching from JV phpBB Arcade

1. Back up your database.
2. Disable “phpBB Arcade” and then “JVA Core” in the ACP and choose **Delete data** for both.
3. Delete `ext/jv/` via FTP. In the folder `arcade/` keep only `games/` and `gamedata/`.
4. Install this extension (steps 2–9).

Note: JV's own HTML5 games (`*_jvh5`) and games using the JV interface only work with the JV server and cannot be imported.

## Uninstalling

ACP → Manage extensions → disable → **Delete data**. Then delete `ext/arcadewelten/` and, if present, `newscore.php` in the root. Your games in the `arcade/` folder are kept.
