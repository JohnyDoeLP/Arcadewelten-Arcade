<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\core;

/**
 * Games and categories storage.
 */
class games
{
	const TYPE_FLASH = 1;
	const TYPE_HTML5 = 2;

	const SAVE_NONE = 0;
	const SAVE_IBPRO = 1;
	const SAVE_IBPROV3 = 2;
	const SAVE_AMOD = 3;
	const SAVE_HTML5 = 4;

	const SCORE_HIGH = 1;
	const SCORE_LOW = 2;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $games_table;

	/** @var string */
	protected $cats_table;

	/** @var bool Only show games released for guests */
	protected $guest_mode = false;

	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, $root_path, $games_table, $cats_table)
	{
		$this->db = $db;
		$this->config = $config;
		$this->root_path = $root_path;
		$this->games_table = $games_table;
		$this->cats_table = $cats_table;
	}

	/**
	 * Restrict lists and counts to games released for guests.
	 */
	public function set_guest_mode($guest_mode)
	{
		$this->guest_mode = (bool) $guest_mode;
	}

	/**
	 * Games folder relative to the board root, always with trailing slash.
	 */
	public function games_table()
	{
		return $this->games_table;
	}

	public function cats_table()
	{
		return $this->cats_table;
	}

	public function games_path()
	{
		$path = trim(str_replace('\\', '/', (string) $this->config['aw_arcade_games_path']), '/');

		// Only allow plain relative paths inside the board root.
		if ($path === '' || strpos($path, '..') !== false)
		{
			$path = 'arcade/games';
		}

		return $path . '/';
	}

	/**
	 * Absolute filesystem path of the games folder.
	 */
	public function games_fs_path()
	{
		return $this->root_path . $this->games_path();
	}

	public function get_game($game_id, $only_active = true)
	{
		$sql = 'SELECT g.*, c.cat_name, c.cat_password, c.cat_online_hide, c.cat_cost, c.cat_reward_play, c.cat_reward_1, c.cat_reward_2, c.cat_reward_3
			FROM ' . $this->games_table . ' g
			LEFT JOIN ' . $this->cats_table . ' c ON c.cat_id = g.cat_id
			WHERE g.game_id = ' . (int) $game_id .
			($only_active ? ' AND g.game_active = 1' : '');
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row ?: false;
	}

	public function get_game_by_scorevar($scorevar)
	{
		$scorevar = (string) $scorevar;

		if ($scorevar === '')
		{
			return false;
		}

		$sql = 'SELECT *
			FROM ' . $this->games_table . "
			WHERE LOWER(game_scorevar) = '" . $this->db->sql_escape(utf8_strtolower($scorevar)) . "'
				AND game_active = 1";
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row ?: false;
	}

	/**
	 * @return string[] game_dir values already in the database
	 */
	public function installed_dirs()
	{
		$dirs = [];
		$sql = 'SELECT game_dir FROM ' . $this->games_table;
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$dirs[] = $row['game_dir'];
		}
		$this->db->sql_freeresult($result);

		return $dirs;
	}

	/**
	 * Sort orders for the game list (key => ORDER BY).
	 */
	const SORTS = [
		'name'		=> 'g.game_name ASC',
		'rating'	=> 'CASE WHEN g.game_rating_count > 0 THEN g.game_rating_sum * 1.0 / g.game_rating_count ELSE 0 END DESC, g.game_rating_count DESC, g.game_name ASC',
		'plays'		=> 'g.game_plays DESC, g.game_name ASC',
		'new'		=> 'g.game_added DESC, g.game_id DESC',
	];

	public function list_games($cat_id = 0, $start = 0, $limit = 30, $only_active = true, $search = '', array $exclude_cats = [], $sort = 'name')
	{
		$sql = 'SELECT g.*, c.cat_name, c.cat_password, c.cat_cost, c.cat_reward_play, c.cat_reward_1, c.cat_reward_2, c.cat_reward_3
			FROM ' . $this->games_table . ' g
			LEFT JOIN ' . $this->cats_table . ' c ON c.cat_id = g.cat_id
			WHERE ' . $this->where($cat_id, $only_active, $search, $exclude_cats) . '
			ORDER BY ' . (isset(self::SORTS[$sort]) ? self::SORTS[$sort] : self::SORTS['name']);
		$result = $this->db->sql_query_limit($sql, (int) $limit, (int) $start);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	/**
	 * Start of the "new" period (0 = badge switched off).
	 */
	public function new_since()
	{
		$days = (int) $this->config['aw_arcade_new_days'];

		return $days > 0 ? time() - $days * 86400 : 0;
	}

	public function is_new(array $game)
	{
		$since = $this->new_since();

		return $since && empty($game['game_not_new']) && (int) $game['game_added'] >= $since;
	}

	/**
	 * Number of new active games per category.
	 *
	 * @return array cat_id => count
	 */
	public function count_new_by_cat()
	{
		$since = $this->new_since();

		if (!$since)
		{
			return [];
		}

		$sql = 'SELECT cat_id, COUNT(game_id) AS total
			FROM ' . $this->games_table . '
			WHERE game_active = 1
				AND game_not_new = 0
				AND game_added >= ' . (int) $since . '
			GROUP BY cat_id';
		$result = $this->db->sql_query($sql);
		$counts = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$counts[(int) $row['cat_id']] = (int) $row['total'];
		}
		$this->db->sql_freeresult($result);

		return $counts;
	}

	public function count_games($cat_id = 0, $only_active = true, $search = '', array $exclude_cats = [])
	{
		$sql = 'SELECT COUNT(g.game_id) AS total
			FROM ' . $this->games_table . ' g
			WHERE ' . $this->where($cat_id, $only_active, $search, $exclude_cats);
		$result = $this->db->sql_query($sql);
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		return $total;
	}

	/**
	 * A random active game outside the given categories (0 if there is none).
	 */
	public function random_game_id(array $exclude_cats = [])
	{
		$result = $this->db->sql_query('SELECT g.game_id FROM ' . $this->games_table . ' g WHERE ' . $this->where(0, true, '', $exclude_cats));
		$ids = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$ids[] = (int) $row['game_id'];
		}
		$this->db->sql_freeresult($result);

		return $ids ? $ids[array_rand($ids)] : 0;
	}

	protected function where($cat_id, $only_active, $search, array $exclude_cats = [])
	{
		$where = ['1 = 1'];

		if (!empty($exclude_cats))
		{
			$where[] = $this->db->sql_in_set('g.cat_id', array_map('intval', $exclude_cats), true);
		}

		if ($cat_id)
		{
			$where[] = 'g.cat_id = ' . (int) $cat_id;
		}

		if ($only_active)
		{
			$where[] = 'g.game_active = 1';
		}

		if ($this->guest_mode)
		{
			$where[] = 'g.game_guest = 1';
		}

		if ($search !== '')
		{
			$where[] = 'g.game_name ' . $this->db->sql_like_expression($this->db->get_any_char() . $search . $this->db->get_any_char());
		}

		return implode(' AND ', $where);
	}

	public function add_game(array $data)
	{
		$data['game_added'] = time();
		$this->db->sql_query('INSERT INTO ' . $this->games_table . ' ' . $this->db->sql_build_array('INSERT', $data));

		return (int) $this->db->sql_nextid();
	}

	public function update_game($game_id, array $data)
	{
		$this->db->sql_query('UPDATE ' . $this->games_table . ' SET ' . $this->db->sql_build_array('UPDATE', $data) . ' WHERE game_id = ' . (int) $game_id);
	}

	public function delete_game($game_id)
	{
		$this->db->sql_query('DELETE FROM ' . $this->games_table . ' WHERE game_id = ' . (int) $game_id);
	}

	/**
	 * Remove a game's own folder from the server (optional when deleting a game).
	 *
	 * Only a plain folder name directly inside the games folder is touched, never a
	 * folder that another game uses, and symbolic links are removed without following them.
	 *
	 * @return array ['status' => 'deleted'|'partial'|'missing'|'shared'|'invalid', 'left' => [relative paths]]
	 */
	public function delete_game_dir(array $game)
	{
		$dir = (string) $game['game_dir'];

		if (!preg_match('/^[A-Za-z0-9_\-]+$/', $dir))
		{
			return ['status' => 'invalid', 'left' => []];
		}

		$sql = 'SELECT COUNT(game_id) AS other
			FROM ' . $this->games_table . "
			WHERE game_dir = '" . $this->db->sql_escape($dir) . "'
				AND game_id <> " . (int) $game['game_id'];
		$result = $this->db->sql_query($sql);
		$other = (int) $this->db->sql_fetchfield('other');
		$this->db->sql_freeresult($result);

		if ($other > 0)
		{
			return ['status' => 'shared', 'left' => []];
		}

		$base = realpath($this->games_fs_path());
		$path = $this->games_fs_path() . $dir;

		if ($base === false || is_link($path))
		{
			return ['status' => 'invalid', 'left' => []];
		}

		if (!is_dir($path))
		{
			return ['status' => 'missing', 'left' => []];
		}

		$real = realpath($path);

		if ($real === false || strpos($real, $base . DIRECTORY_SEPARATOR) !== 0 || dirname($real) !== $base)
		{
			return ['status' => 'invalid', 'left' => []];
		}

		$left = [];
		$items = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($real, \FilesystemIterator::SKIP_DOTS),
			\RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ($items as $item)
		{
			$file = $item->getPathname();
			$ok = ($item->isDir() && !$item->isLink()) ? @rmdir($file) : @unlink($file);

			if (!$ok)
			{
				$left[] = $dir . '/' . ltrim(str_replace('\\', '/', substr($file, strlen($real))), '/');
			}
		}

		if (!@rmdir($real) && !$left)
		{
			$left[] = $dir . '/';
		}

		return ['status' => $left ? 'partial' : 'deleted', 'left' => $left];
	}

	/**
	 * Set the play counter of all games (or one game) back to zero.
	 */
	public function reset_plays($game_id = 0)
	{
		$this->db->sql_query('UPDATE ' . $this->games_table . ' SET game_plays = 0' . ($game_id ? ' WHERE game_id = ' . (int) $game_id : ''));
	}

	public function count_play($game_id)
	{
		$this->db->sql_query('UPDATE ' . $this->games_table . ' SET game_plays = game_plays + 1 WHERE game_id = ' . (int) $game_id);
	}

	public function get_cats()
	{
		$count_sql = 'SELECT COUNT(g.game_id) FROM ' . $this->games_table . ' g WHERE g.cat_id = c.cat_id AND g.game_active = 1' . ($this->guest_mode ? ' AND g.game_guest = 1' : '');
		$sql = 'SELECT c.*, (' . $count_sql . ') AS game_count
			FROM ' . $this->cats_table . ' c
			ORDER BY c.cat_order ASC, c.cat_name ASC';
		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	/**
	 * Cover image per category: the image of its most played game.
	 *
	 * @return array cat_id => ['game_dir' => ..., 'game_image' => ...]
	 */
	public function cat_cover_images()
	{
		$sql = 'SELECT g.cat_id, g.game_dir, g.game_image
			FROM ' . $this->games_table . " g
			WHERE g.game_active = 1
				AND g.game_image <> ''" . ($this->guest_mode ? ' AND g.game_guest = 1' : '') . '
			ORDER BY g.game_plays DESC, g.game_id ASC';
		$result = $this->db->sql_query($sql);

		$covers = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$cid = (int) $row['cat_id'];

			if (!isset($covers[$cid]))
			{
				$covers[$cid] = $row;
			}
		}
		$this->db->sql_freeresult($result);

		return $covers;
	}

	public function get_cat($cat_id)
	{
		$sql = 'SELECT * FROM ' . $this->cats_table . ' WHERE cat_id = ' . (int) $cat_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row ?: false;
	}

	public function add_cat($name)
	{
		$sql = 'SELECT MAX(cat_order) AS max_order FROM ' . $this->cats_table;
		$result = $this->db->sql_query($sql);
		$order = (int) $this->db->sql_fetchfield('max_order') + 1;
		$this->db->sql_freeresult($result);

		$this->db->sql_query('INSERT INTO ' . $this->cats_table . ' ' . $this->db->sql_build_array('INSERT', [
			'cat_name'	=> (string) $name,
			'cat_order'	=> $order,
		]));

		return (int) $this->db->sql_nextid();
	}

	public function update_cat($cat_id, array $data)
	{
		$this->db->sql_query('UPDATE ' . $this->cats_table . ' SET ' . $this->db->sql_build_array('UPDATE', $data) . ' WHERE cat_id = ' . (int) $cat_id);
	}

	public function move_cat($cat_id, $direction)
	{
		$cats = $this->get_cats();
		$ids = array_map('intval', array_column($cats, 'cat_id'));
		$pos = array_search((int) $cat_id, $ids, true);
		$swap = $pos === false ? false : ($direction === 'up' ? $pos - 1 : $pos + 1);

		if ($pos === false || !isset($ids[$swap]))
		{
			return;
		}

		list($ids[$pos], $ids[$swap]) = [$ids[$swap], $ids[$pos]];

		foreach ($ids as $order => $id)
		{
			$this->update_cat($id, ['cat_order' => $order + 1]);
		}
	}

	public function delete_cat($cat_id)
	{
		$this->db->sql_query('UPDATE ' . $this->games_table . ' SET cat_id = 0 WHERE cat_id = ' . (int) $cat_id);
		$this->db->sql_query('DELETE FROM ' . $this->cats_table . ' WHERE cat_id = ' . (int) $cat_id);
	}

	/**
	 * Find a category by name or create it.
	 */
	public function cat_id_by_name($name)
	{
		$name = trim((string) $name);

		if ($name === '')
		{
			return 0;
		}

		$sql = 'SELECT cat_id FROM ' . $this->cats_table . "
			WHERE LOWER(cat_name) = '" . $this->db->sql_escape(utf8_strtolower($name)) . "'";
		$result = $this->db->sql_query_limit($sql, 1);
		$cat_id = (int) $this->db->sql_fetchfield('cat_id');
		$this->db->sql_freeresult($result);

		return $cat_id ?: $this->add_cat($name);
	}
}
