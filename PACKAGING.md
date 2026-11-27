# Packing a game for the Arcadewelten Arcade

This is for anyone who wants to put a game into the arcade: one you made
yourself, or one you are bringing over from another arcade. It describes what
an archive has to look like so the upload under **ACP → Arcade → Add games**
takes it.

Where the arcade hands games out, the quickest way to see a correct archive is
to download a game that already works and open it.

## The short version

```
MyGame.zip
└── MyGame/                     ← the game folder, named after the game
    ├── MyGame.php              ← the installation file, same name
    ├── MyGame.swf              ← or index.html for an HTML5 game
    ├── MyGame.gif              ← the picture shown in the list
    └── gamedata/               ← only if the game needs it
        └── MyGame/
            └── MyGame.txt
```

The folder, the installation file and the score variable carry the same name.
That is the single most common cause of a game that uploads but then saves no
score.

A `gamedata` folder may travel inside the game folder. The arcade takes it out
on upload and puts it in `arcade/gamedata/`, where a game looks for it while it
runs, and says so in the message afterwards.

The archive may also hold the game folder on its own, without a folder around
it, or sit inside an `arcade/games/` tree. The arcade looks for the
installation file rather than expecting one shape.

## The installation file

A plain PHP file that sets `$game_data` and nothing else. It is read, never
run.

### A Flash game

```php
<?php
if (!defined('IN_PHPBB'))
{
	exit;
}

$game_data = array(
	'game_name'       => 'My Game',
	'game_desc'       => 'What the game is about, one or two sentences.',
	'game_image'      => 'MyGame.gif',
	'game_swf'        => 'MyGame.swf',
	'game_width'      => 640,
	'game_height'     => 480,
	'game_scorevar'   => 'MyGame',
	'game_type'       => GAME_TYPE_FLASH,
	'game_save_type'  => IBPROV3_GAME,
	'game_scoretype'  => SCORETYPE_HIGH,
);
```

### An HTML5 game

```php
	'game_image'      => 'MyGame.gif',
	'game_width'      => 800,
	'game_height'     => 600,
	'game_scorevar'   => 'MyGame',
	'game_type'       => GAME_TYPE_HTML5,
	'game_scoretype'  => SCORETYPE_HIGH,
```

An HTML5 game needs an `index.html` in the game folder. `game_swf` and
`game_save_type` are left out: the arcade's own score interface is used.

### What the fields mean

| Field | What it is |
| --- | --- |
| `game_name` | The title, as it appears in the list |
| `game_desc` | A short description; HTML entities are decoded |
| `game_image` | Picture in the game folder. Leave it out and the arcade takes one it finds |
| `game_swf` | The file that is started. May carry a path, e.g. `gamedata/MyGame/index.html` |
| `game_width`, `game_height` | Size of the playing area in pixels |
| `game_scorevar` | The name the game calls itself by when it reports a score |
| `game_type` | `GAME_TYPE_FLASH` or `GAME_TYPE_HTML5` |
| `game_save_type` | `IBPROV3_GAME`, `IBPRO_GAME`, `AMOD_GAME` or `NOSCORE_GAME` |
| `game_scoretype` | `SCORETYPE_HIGH` (more is better) or `SCORETYPE_LOW` |

## Games from other arcades

Installation files from **SMF Arcade** (`$config` with `gname`, `gtitle`,
`savetype`) and from **JV Arcade** are read as they are. So is an HTML5 game
that keeps itself under `gamedata/<name>/` with the game folder holding only
the installation file and the pictures, the way those arcades pack one.

What such a game cannot bring along is a score interface this arcade does not
answer. The old IBPro address (`index.php?act=Arcade&do=newscore` with `gname`
and `gscore`) is answered, and so are the v3 and v32 ones.

## A v3 or v32 Flash game

Such a game reads `arcade/gamedata/<scorevar>/<scorevar>.txt` when it starts,
to learn which variable in the film holds the score:

```
&scoreVar=_root.score&blah=1
```

Without that file the game runs and reports nothing, or reports zero. The game
list warns about a missing one; pack it along inside the game folder and the
arcade puts it in place.

## What does not belong in the archive

- **PHP files other than the installation file.** They are left out of the
  unpacking, and the game is not installed if the only PHP file is one the
  arcade does not recognise.
- **Addresses of other servers.** A game that fetches something from elsewhere
  shows every player a warning before it starts, naming the servers. Ad
  networks and tracking end up there. Copy what the game really needs into the
  game folder instead.
- **Files of another game.** A stray `index.html` from a different game is
  quickly packed along and then loads nothing.

## Before you hand it on

Upload it yourself once and play it to the end. Four things to look at:

1. Does the game appear in the list with the right name, size and picture?
2. Does the game list warn about a missing gamedata file?
3. Does the game list warn about other servers?
4. Does your score appear in the highscore list afterwards?

If the score does not arrive, check whether the folder, the installation file
and `game_scorevar` really carry the same name, spelling and capitals
included. On a Linux server `MyGame` and `mygame` are two different things.
