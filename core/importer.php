<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\core;

/**
 * Reads game install files (games/<dir>/<dir>.php) in the common
 * phpBB-arcade format. The files are parsed as text, never executed.
 */
class importer
{
	/** @var int[] ids of the games added by the last import() */
	public $last_imported = [];

	/** Fixed location old v3/v32 games load their data from. */
	const GAMEDATA_PATH = 'arcade/gamedata/';

	protected $games;
	protected $config;
	protected $root_path;

	public function __construct(games $games, \phpbb\config\config $config, $root_path)
	{
		$this->games = $games;
		$this->config = $config;
		$this->root_path = $root_path;
	}

	/**
	 * List all game folders with their parsed data and import status.
	 *
	 * @return array[]
	 */
	public function scan()
	{
		$base = $this->games->games_fs_path();
		$installed = array_flip($this->games->installed_dirs());
		$found = [];

		if (!is_dir($base))
		{
			return [];
		}

		foreach (new \DirectoryIterator($base) as $entry)
		{
			if ($entry->isDot() || !$entry->isDir())
			{
				continue;
			}

			$dir = $entry->getFilename();

			if (!preg_match('/^[A-Za-z0-9_\-]+$/', $dir) || !is_file($base . $dir . '/' . $dir . '.php'))
			{
				continue;
			}

			$game = $this->parse($dir);
			$game['installed'] = isset($installed[$dir]);
			$found[$dir] = $game;
		}

		ksort($found, SORT_NATURAL | SORT_FLAG_CASE);

		return $found;
	}

	/**
	 * Parse one install file.
	 */
	public function parse($dir)
	{
		$base = $this->games->games_fs_path() . $dir . '/';
		$source = (string) file_get_contents($base . $dir . '.php');
		$raw = [];

		// 'key' => 'string' | "string" | number | CONSTANT | $game_file . '.ext'
		if (preg_match_all('/\'(game_[a-z_]+)\'\s*=>\s*(\$game_file\s*\.\s*([\'"])(.*?)\3|([\'"])((?:\\\\.|(?!\5).)*)\5|-?\d+(?:\.\d+)?|[A-Z][A-Z0-9_]*)\s*,?/s', $source, $m, PREG_SET_ORDER))
		{
			foreach ($m as $set)
			{
				if (isset($set[4]) && $set[3] !== '')
				{
					$value = $dir . $set[4];
				}
				else if (isset($set[6]) && $set[5] !== '')
				{
					$value = preg_replace('/\\\\(.)/s', '$1', $set[6]);
				}
				else
				{
					$value = $set[2];
				}

				$raw[$set[1]] = $value;
			}
		}

		$type_const = isset($raw['game_type']) ? $raw['game_type'] : 'GAME_TYPE_FLASH';
		$save_const = isset($raw['game_save_type']) ? $raw['game_save_type'] : 'NOSCORE_GAME';

		$game = [
			'dir'			=> $dir,
			'name'			=> isset($raw['game_name']) ? html_entity_decode($raw['game_name'], ENT_QUOTES, 'UTF-8') : $dir,
			'desc'			=> isset($raw['game_desc']) ? html_entity_decode($raw['game_desc'], ENT_QUOTES, 'UTF-8') : '',
			'scorevar'		=> isset($raw['game_scorevar']) ? $raw['game_scorevar'] : $dir,
			'file'			=> isset($raw['game_swf']) ? $raw['game_swf'] : $dir . '.swf',
			'image'			=> isset($raw['game_image']) ? $raw['game_image'] : '',
			'width'			=> isset($raw['game_width']) ? max(100, min(4000, (int) $raw['game_width'])) : 640,
			'height'		=> isset($raw['game_height']) ? max(100, min(4000, (int) $raw['game_height'])) : 480,
			'scoretype'		=> (isset($raw['game_scoretype']) && $raw['game_scoretype'] === 'SCORETYPE_LOW') ? games::SCORE_LOW : games::SCORE_HIGH,
			'type'			=> $type_const === 'GAME_TYPE_HTML5' ? games::TYPE_HTML5 : games::TYPE_FLASH,
			'save_type'		=> games::SAVE_NONE,
			'supported'		=> true,
			'reason'		=> '',
		];

		switch ($save_const)
		{
			case 'IBPRO_GAME':
				$game['save_type'] = games::SAVE_IBPRO;
			break;

			case 'IBPROV3_GAME':
				$game['save_type'] = games::SAVE_IBPROV3;
			break;

			case 'AMOD_GAME':
				$game['save_type'] = games::SAVE_AMOD;
			break;

			case 'NOSCORE_GAME':
				$game['save_type'] = games::SAVE_NONE;
			break;

			default:
				$game['supported'] = false;
				$game['reason'] = $save_const;
		}

		// Many packages label v32 games wrongly; a v32game.txt in gamedata is decisive.
		if (is_file($this->root_path . self::GAMEDATA_PATH . $game['scorevar'] . '/v32game.txt'))
		{
			$game['save_type'] = games::SAVE_IBPROV3;
			$game['supported'] = true;
			$game['reason'] = '';
		}

		if ($game['type'] === games::TYPE_FLASH && !is_file($base . $game['file']))
		{
			$game['supported'] = false;
			$game['reason'] = 'FILE_MISSING';
		}

		if ($game['type'] === games::TYPE_HTML5)
		{
			// HTML5 games need an index.html in their folder and our own score API.
			$game['file'] = 'index.html';

			if (!is_file($base . 'index.html'))
			{
				$game['supported'] = false;
				$game['reason'] = 'HTML5_FOREIGN';
			}
			else
			{
				$game['save_type'] = games::SAVE_HTML5;
			}
		}

		// The declared score interface is often wrong; the SWF itself tells which URLs it calls.
		if ($game['type'] === games::TYPE_FLASH && $game['supported'])
		{
			$detected = $this->detect_save_type($base . $game['file']);

			if ($detected === -1)
			{
				$game['supported'] = false;
				$game['reason'] = 'PHPBBARCADE_GAME';
			}
			else if ($detected !== null)
			{
				$game['save_type'] = $detected;
			}
		}

		if ($game['image'] === '' || !is_file($base . $game['image']))
		{
			$game['image'] = '';

			foreach (['.gif', '1.gif', '2.gif', '.png', '1.png', '.jpg'] as $suffix)
			{
				if (is_file($base . $dir . $suffix))
				{
					$game['image'] = $dir . $suffix;
					break;
				}
			}
		}

		return $game;
	}

	/**
	 * Look for the score URLs inside a (zlib compressed) SWF.
	 *
	 * @return int|null games::SAVE_* or null if unknown
	 */
	public function detect_save_type($file)
	{
		$data = @file_get_contents($file);

		if ($data === false || strlen($data) < 8)
		{
			return null;
		}

		$sig = substr($data, 0, 3);

		if ($sig === 'CWS')
		{
			$data = @gzuncompress(substr($data, 8));
		}
		else if ($sig !== 'FWS')
		{
			// LZMA compressed (ZWS) or unknown: cannot inspect.
			return null;
		}

		if (!is_string($data))
		{
			return null;
		}

		if (stripos($data, 'autocom=arcade') !== false)
		{
			return games::SAVE_IBPROV3;
		}

		if (stripos($data, 'act=Arcade&do=newscore') !== false)
		{
			return games::SAVE_IBPRO;
		}

		if (stripos($data, 'newscore.php') !== false)
		{
			return games::SAVE_AMOD;
		}

		if (stripos($data, 'phpBBArcadeUrl') !== false)
		{
			return -1;
		}

		return null;
	}

	/**
	 * Import the given folders.
	 *
	 * @return int number of imported games
	 */
	public function import(array $dirs, $cat_id = 0, $active = true)
	{
		$this->last_imported = [];
		$scan = $this->scan();
		$count = 0;

		foreach ($dirs as $dir)
		{
			if (!isset($scan[$dir]) || $scan[$dir]['installed'] || !$scan[$dir]['supported'])
			{
				continue;
			}

			$g = $scan[$dir];

			try
			{
				$this->last_imported[] = $this->games->add_game([
					'cat_id'			=> (int) $cat_id,
					'game_name'			=> utf8_normalize_nfc($g['name']),
					'game_desc'			=> utf8_normalize_nfc($g['desc']),
					'game_dir'			=> $g['dir'],
					'game_scorevar'		=> $g['scorevar'],
					'game_file'			=> $g['file'],
					'game_image'		=> $g['image'],
					'game_type'			=> $g['type'],
					'game_save_type'	=> $g['save_type'],
					'game_scoretype'	=> $g['scoretype'],
					'game_width'		=> $g['width'],
					'game_height'		=> $g['height'],
					'game_active'		=> $active ? 1 : 0,
					'game_plays'		=> 0,
				]);
				$count++;
			}
			catch (\Exception $e)
			{
				// Duplicate score variable: skip this game.
				continue;
			}
		}

		return $count;
	}
}
