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

	/** @var external|null */
	protected $external;

	public function __construct(games $games, \phpbb\config\config $config, $root_path, ?external $external = null)
	{
		$this->external = $external;
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

			// Installed games are only listed, not inspected: no need to read their files again.
			if (isset($installed[$dir]))
			{
				$found[$dir] = ['dir' => $dir, 'installed' => true, 'supported' => true];
				continue;
			}

			$game = $this->external ? $this->external->remember('parse', $base . $dir, function () use ($dir) {
				return $this->parse($dir);
			}) : $this->parse($dir);
			$game['installed'] = false;
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
			// Argument for the reason text (e.g. the declared constant) and a note for ready games.
			'reason_arg'	=> '',
			'note'			=> '',
		];

		// A constant we do not support is not final: the game file is checked further down.
		$declared_unknown = '';

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
				$declared_unknown = (string) $save_const;
		}

		// Many packages label v32 games wrongly; a v32game.txt in gamedata is decisive.
		if (is_file($this->root_path . self::GAMEDATA_PATH . $game['scorevar'] . '/v32game.txt'))
		{
			$game['save_type'] = games::SAVE_IBPROV3;
			$game['supported'] = true;
			$game['reason'] = '';
		}

		if ($game['save_type'] === games::SAVE_IBPROV3 && $declared_unknown !== '')
		{
			// Decided by v32game.txt.
			$declared_unknown = '';
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

			// The declared Flash interface does not matter for HTML5 games.
			$declared_unknown = '';
		}

		// The declared score interface is often wrong; the SWF itself tells which URLs it calls.
		if ($game['type'] === games::TYPE_FLASH && $game['supported'])
		{
			$check = $this->inspect_swf($base . $game['file']);

			if ($check['problem'] === 'DAMAGED')
			{
				$game['supported'] = false;
				$game['reason'] = 'FILE_DAMAGED';
			}
			else if ($check['type'] === -1)
			{
				$game['supported'] = false;
				$game['reason'] = 'PHPBBARCADE_GAME';
			}
			else if ($check['type'] !== null)
			{
				if ($declared_unknown !== '')
				{
					$game['note'] = 'DETECTED';
					$game['reason_arg'] = $declared_unknown;
				}

				$game['save_type'] = $check['type'];
			}
			else if ($declared_unknown !== '')
			{
				// Neither the installation file nor the game file names an interface we know.
				$game['supported'] = false;
				$game['reason'] = $check['problem'] === 'COMPRESSED' ? 'DECLARED_COMPRESSED' : 'DECLARED_NO_IFACE';
				$game['reason_arg'] = $declared_unknown;
			}
		}
		else if ($declared_unknown !== '' && $game['supported'])
		{
			$game['supported'] = false;
			$game['reason'] = 'DECLARED_NO_IFACE';
			$game['reason_arg'] = $declared_unknown;
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
	 * Score interface named inside a SWF (kept for callers of older versions).
	 *
	 * @return int|null games::SAVE_*, -1 for the JV interface or null if unknown
	 */
	public function detect_save_type($file)
	{
		return $this->inspect_swf($file)['type'];
	}

	/**
	 * Look into a SWF: which score interface does it call, and can it be read at all?
	 *
	 * @return array ['type' => games::SAVE_*|-1|null, 'problem' => ''|'DAMAGED'|'COMPRESSED'|'NONE']
	 */
	public function inspect_swf($file)
	{
		$data = @file_get_contents($file);

		if ($data === false || strlen($data) < 8)
		{
			return ['type' => null, 'problem' => 'DAMAGED'];
		}

		$sig = substr($data, 0, 3);

		if ($sig === 'ZWS')
		{
			// LZMA compressed: Ruffle plays it, but we cannot look inside.
			return ['type' => null, 'problem' => 'COMPRESSED'];
		}

		if ($sig === 'CWS')
		{
			$data = @gzuncompress(substr($data, 8));
		}
		else if ($sig !== 'FWS')
		{
			return ['type' => null, 'problem' => 'DAMAGED'];
		}

		if (!is_string($data))
		{
			return ['type' => null, 'problem' => 'DAMAGED'];
		}

		if (stripos($data, 'autocom=arcade') !== false)
		{
			return ['type' => games::SAVE_IBPROV3, 'problem' => ''];
		}

		if (stripos($data, 'act=Arcade&do=newscore') !== false)
		{
			return ['type' => games::SAVE_IBPRO, 'problem' => ''];
		}

		if (stripos($data, 'newscore.php') !== false)
		{
			return ['type' => games::SAVE_AMOD, 'problem' => ''];
		}

		if (stripos($data, 'phpBBArcadeUrl') !== false)
		{
			return ['type' => -1, 'problem' => ''];
		}

		return ['type' => null, 'problem' => 'NONE'];
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
					'game_external'			=> $this->external ? implode(',', $this->external->scan_dir($this->games->games_fs_path() . $g['dir'])) : '',
					'game_external_checked'	=> $this->external ? 1 : 0,
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
