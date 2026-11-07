<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\core;

/**
 * Game ratings: 1–5 stars, one rating per member and game (changeable).
 *
 * Only members who played the game may rate it: they have a highscore, an
 * entry in the recently played list, an open play session (e.g. games
 * without scores) or rated it before.
 * Sum and count are mirrored in the games table for lists and sorting.
 */
class ratings
{
	const MAX = 5;

	protected $db;
	protected $config;
	protected $ratings_table;
	protected $games_table;
	protected $scores_table;
	protected $sessions_table;
	protected $recent_table;

	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, $ratings_table, $games_table, $scores_table, $sessions_table, $recent_table)
	{
		$this->db = $db;
		$this->config = $config;
		$this->ratings_table = $ratings_table;
		$this->games_table = $games_table;
		$this->scores_table = $scores_table;
		$this->sessions_table = $sessions_table;
		$this->recent_table = $recent_table;
	}

	public function enabled()
	{
		return !empty($this->config['aw_arcade_ratings']);
	}

	/**
	 * Average of a game row (0 when unrated).
	 */
	public static function average(array $game)
	{
		$count = isset($game['game_rating_count']) ? (int) $game['game_rating_count'] : 0;

		return $count ? (int) $game['game_rating_sum'] / $count : 0.0;
	}

	/**
	 * Own rating of a member (0 = not rated yet).
	 */
	public function get_user_rating($game_id, $user_id)
	{
		$sql = 'SELECT rating FROM ' . $this->ratings_table . '
			WHERE game_id = ' . (int) $game_id . '
				AND user_id = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$rating = (int) $this->db->sql_fetchfield('rating');
		$this->db->sql_freeresult($result);

		return $rating;
	}

	/**
	 * Has the member played this game (or rated it before)?
	 */
	public function can_rate($game_id, $user_id)
	{
		$where = ' WHERE game_id = ' . (int) $game_id . ' AND user_id = ' . (int) $user_id;

		foreach ([$this->ratings_table, $this->scores_table, $this->recent_table, $this->sessions_table] as $table)
		{
			$result = $this->db->sql_query_limit('SELECT user_id FROM ' . $table . $where, 1);
			$found = (bool) $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if ($found)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Store or change a rating and refresh the game totals.
	 *
	 * @return array ['sum' => int, 'count' => int]
	 */
	public function rate($game_id, $user_id, $rating)
	{
		$rating = max(1, min(self::MAX, (int) $rating));
		$data = ['rating' => $rating, 'rating_time' => time()];

		$this->db->sql_transaction('begin');

		$sql = 'UPDATE ' . $this->ratings_table . ' SET ' . $this->db->sql_build_array('UPDATE', $data) . '
			WHERE game_id = ' . (int) $game_id . '
				AND user_id = ' . (int) $user_id;
		$this->db->sql_query($sql);

		if (!$this->db->sql_affectedrows() && !$this->get_user_rating($game_id, $user_id))
		{
			$this->db->sql_query('INSERT INTO ' . $this->ratings_table . ' ' . $this->db->sql_build_array('INSERT', $data + [
				'game_id'	=> (int) $game_id,
				'user_id'	=> (int) $user_id,
			]));
		}

		$totals = $this->recount($game_id);

		$this->db->sql_transaction('commit');

		return $totals;
	}

	/**
	 * Recalculate sum and count of one game from the ratings table.
	 */
	public function recount($game_id)
	{
		$sql = 'SELECT COUNT(rating) AS total, SUM(rating) AS rating_sum
			FROM ' . $this->ratings_table . '
			WHERE game_id = ' . (int) $game_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$totals = ['sum' => (int) $row['rating_sum'], 'count' => (int) $row['total']];

		$this->db->sql_query('UPDATE ' . $this->games_table . '
			SET game_rating_sum = ' . $totals['sum'] . ', game_rating_count = ' . $totals['count'] . '
			WHERE game_id = ' . (int) $game_id);

		return $totals;
	}

	/**
	 * Remove all ratings of one game (ACP reset or game deleted).
	 */
	public function delete_game($game_id)
	{
		$this->db->sql_query('DELETE FROM ' . $this->ratings_table . ' WHERE game_id = ' . (int) $game_id);
		$this->db->sql_query('UPDATE ' . $this->games_table . ' SET game_rating_sum = 0, game_rating_count = 0 WHERE game_id = ' . (int) $game_id);
	}

	/**
	 * Remove the ratings of deleted members and fix the totals of their games.
	 */
	public function delete_users(array $user_ids)
	{
		$user_ids = array_map('intval', $user_ids);

		if (empty($user_ids))
		{
			return;
		}

		$sql = 'SELECT DISTINCT game_id FROM ' . $this->ratings_table . ' WHERE ' . $this->db->sql_in_set('user_id', $user_ids);
		$result = $this->db->sql_query($sql);
		$game_ids = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$game_ids[] = (int) $row['game_id'];
		}
		$this->db->sql_freeresult($result);

		$this->db->sql_query('DELETE FROM ' . $this->ratings_table . ' WHERE ' . $this->db->sql_in_set('user_id', $user_ids));

		foreach ($game_ids as $game_id)
		{
			$this->recount($game_id);
		}
	}
}
