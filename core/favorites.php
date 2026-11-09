<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\core;

/**
 * Favourite games of a member (heart), shown at the top of the arcade.
 */
class favorites
{
	const MAX = 24;

	protected $db;
	protected $config;
	protected $favorites_table;
	protected $games_table;
	protected $cats_table;

	/** @var array user_id => [game_id => true] */
	protected $cache = [];

	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, $favorites_table, $games_table, $cats_table)
	{
		$this->db = $db;
		$this->config = $config;
		$this->favorites_table = $favorites_table;
		$this->games_table = $games_table;
		$this->cats_table = $cats_table;
	}

	public function enabled()
	{
		return !empty($this->config['aw_arcade_favorites']);
	}

	/**
	 * Game ids of a member's favourites.
	 *
	 * @return array game_id => true
	 */
	public function ids($user_id)
	{
		$user_id = (int) $user_id;

		if (!isset($this->cache[$user_id]))
		{
			$this->cache[$user_id] = [];
			$result = $this->db->sql_query('SELECT game_id FROM ' . $this->favorites_table . ' WHERE user_id = ' . (int) $user_id);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->cache[$user_id][(int) $row['game_id']] = true;
			}
			$this->db->sql_freeresult($result);
		}

		return $this->cache[$user_id];
	}

	public function is_favorite($user_id, $game_id)
	{
		return isset($this->ids($user_id)[(int) $game_id]);
	}

	/**
	 * Add or remove a favourite.
	 *
	 * @return bool|null new state, null when the limit is reached
	 */
	public function toggle($user_id, $game_id)
	{
		$user_id = (int) $user_id;
		$game_id = (int) $game_id;

		if ($this->is_favorite($user_id, $game_id))
		{
			$this->db->sql_query('DELETE FROM ' . $this->favorites_table . ' WHERE user_id = ' . (int) $user_id . ' AND game_id = ' . (int) $game_id);
			unset($this->cache[$user_id]);

			return false;
		}

		if (count($this->ids($user_id)) >= self::MAX)
		{
			return null;
		}

		$this->db->sql_query('INSERT INTO ' . $this->favorites_table . ' ' . $this->db->sql_build_array('INSERT', [
			'user_id'	=> $user_id,
			'game_id'	=> $game_id,
			'fav_time'	=> time(),
		]));
		unset($this->cache[$user_id]);

		return true;
	}

	/**
	 * Active favourite games of a member, by name.
	 */
	public function get($user_id)
	{
		$sql = 'SELECT g.*, c.cat_password
			FROM ' . $this->favorites_table . ' f
			JOIN ' . $this->games_table . ' g ON g.game_id = f.game_id
			LEFT JOIN ' . $this->cats_table . ' c ON c.cat_id = g.cat_id
			WHERE f.user_id = ' . (int) $user_id . '
				AND g.game_active = 1
			ORDER BY g.game_name ASC';
		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	public function delete_users(array $user_ids)
	{
		if ($user_ids)
		{
			$this->db->sql_query('DELETE FROM ' . $this->favorites_table . ' WHERE ' . $this->db->sql_in_set('user_id', array_map('intval', $user_ids)));
		}
	}

	public function delete_game($game_id)
	{
		$this->db->sql_query('DELETE FROM ' . $this->favorites_table . ' WHERE game_id = ' . (int) $game_id);
	}
}
