<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\core;

/**
 * "Recently played" list per member (at most KEEP games, newest first).
 * Only the member sees the own list.
 */
class recent
{
	const KEEP = 10;

	protected $db;
	protected $config;
	protected $recent_table;
	protected $games_table;
	protected $cats_table;

	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, $recent_table, $games_table, $cats_table)
	{
		$this->db = $db;
		$this->config = $config;
		$this->recent_table = $recent_table;
		$this->games_table = $games_table;
		$this->cats_table = $cats_table;
	}

	public function enabled()
	{
		return !empty($this->config['aw_arcade_recent']);
	}

	/**
	 * Remember that a member started a game; keeps only the newest KEEP entries.
	 */
	public function add($user_id, $game_id)
	{
		if (!$this->enabled())
		{
			return;
		}

		$user_id = (int) $user_id;
		$game_id = (int) $game_id;

		$this->db->sql_query('UPDATE ' . $this->recent_table . ' SET play_time = ' . time() . '
			WHERE user_id = ' . (int) $user_id . ' AND game_id = ' . (int) $game_id);

		if (!$this->db->sql_affectedrows())
		{
			// Affected rows can be 0 for an unchanged row within the same second.
			$result = $this->db->sql_query('SELECT game_id FROM ' . $this->recent_table . ' WHERE user_id = ' . (int) $user_id . ' AND game_id = ' . (int) $game_id);
			$exists = (bool) $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if (!$exists)
			{
				$this->db->sql_query('INSERT INTO ' . $this->recent_table . ' ' . $this->db->sql_build_array('INSERT', [
					'user_id'	=> $user_id,
					'game_id'	=> $game_id,
					'play_time'	=> time(),
				]));
			}
		}

		// Drop everything beyond the newest KEEP entries.
		$sql = 'SELECT game_id FROM ' . $this->recent_table . '
			WHERE user_id = ' . (int) $user_id . '
			ORDER BY play_time DESC, game_id DESC';
		$result = $this->db->sql_query_limit($sql, 1000, self::KEEP);
		$old = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$old[] = (int) $row['game_id'];
		}
		$this->db->sql_freeresult($result);

		if ($old)
		{
			$this->db->sql_query('DELETE FROM ' . $this->recent_table . ' WHERE user_id = ' . (int) $user_id . ' AND ' . $this->db->sql_in_set('game_id', $old));
		}
	}

	/**
	 * Recently played active games of a member, newest first (with category password for access checks).
	 */
	public function get($user_id, $limit = 6)
	{
		$sql = 'SELECT g.*, c.cat_password, r.play_time
			FROM ' . $this->recent_table . ' r
			JOIN ' . $this->games_table . ' g ON g.game_id = r.game_id
			LEFT JOIN ' . $this->cats_table . ' c ON c.cat_id = g.cat_id
			WHERE r.user_id = ' . (int) $user_id . '
				AND g.game_active = 1
			ORDER BY r.play_time DESC, r.game_id DESC';
		$result = $this->db->sql_query_limit($sql, (int) $limit);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	public function delete_users(array $user_ids)
	{
		if ($user_ids)
		{
			$this->db->sql_query('DELETE FROM ' . $this->recent_table . ' WHERE ' . $this->db->sql_in_set('user_id', array_map('intval', $user_ids)));
		}
	}

	public function delete_game($game_id)
	{
		$this->db->sql_query('DELETE FROM ' . $this->recent_table . ' WHERE game_id = ' . (int) $game_id);
	}
}
