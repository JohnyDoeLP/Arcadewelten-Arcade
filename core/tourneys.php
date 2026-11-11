<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\core;

/**
 * Tournaments: the admin picks several games and a period. Every genuine result
 * in that period counts; per game the places give tournament points, and the
 * sum of these points decides the overall ranking.
 */
class tourneys
{
	/** Tournament points for places 1, 2, 3, ... (from place 9 on: 0) */
	const PLACE_POINTS = [10, 8, 6, 5, 4, 3, 2, 1];

	const UPCOMING = 'upcoming';
	const RUNNING = 'running';
	const FINISHED = 'finished';

	protected $db;
	protected $config;
	protected $tourneys_table;
	protected $tourney_games_table;
	protected $tourney_scores_table;
	protected $games_table;
	protected $cats_table;

	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, $tourneys_table, $tourney_games_table, $tourney_scores_table, $games_table, $cats_table)
	{
		$this->db = $db;
		$this->config = $config;
		$this->tourneys_table = $tourneys_table;
		$this->tourney_games_table = $tourney_games_table;
		$this->tourney_scores_table = $tourney_scores_table;
		$this->games_table = $games_table;
		$this->cats_table = $cats_table;
	}

	public function enabled()
	{
		return !empty($this->config['aw_arcade_tourneys']);
	}

	/* ---------------------------------------------------------------
	 * Reading
	 * ------------------------------------------------------------- */

	public function get($tourney_id)
	{
		$result = $this->db->sql_query('SELECT * FROM ' . $this->tourneys_table . ' WHERE tourney_id = ' . (int) $tourney_id);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row ?: false;
	}

	/**
	 * All tournaments, newest start first.
	 */
	public function get_all($limit = 0)
	{
		$sql = 'SELECT * FROM ' . $this->tourneys_table . ' ORDER BY tourney_start DESC, tourney_id DESC';
		$result = $limit ? $this->db->sql_query_limit($sql, $limit) : $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	/**
	 * Running tournaments (for the banner in the arcade), ending soonest first.
	 */
	public function get_running()
	{
		$now = time();
		$sql = 'SELECT * FROM ' . $this->tourneys_table . '
			WHERE tourney_finished = 0
				AND tourney_start <= ' . (int) $now . '
				AND tourney_end > ' . (int) $now . '
			ORDER BY tourney_end ASC';
		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	public static function status(array $tourney)
	{
		if (!empty($tourney['tourney_finished']) || (int) $tourney['tourney_end'] <= time())
		{
			return self::FINISHED;
		}

		return (int) $tourney['tourney_start'] > time() ? self::UPCOMING : self::RUNNING;
	}

	/**
	 * Games of one tournament (with category data), in name order.
	 *
	 * @return array game_id => game row
	 */
	public function get_games($tourney_id)
	{
		$sql = 'SELECT g.*, c.cat_name, c.cat_password
			FROM ' . $this->tourney_games_table . ' tg
			JOIN ' . $this->games_table . ' g ON g.game_id = tg.game_id
			LEFT JOIN ' . $this->cats_table . ' c ON c.cat_id = g.cat_id
			WHERE tg.tourney_id = ' . (int) $tourney_id . '
			ORDER BY g.game_name ASC';
		$result = $this->db->sql_query($sql);
		$games = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$games[(int) $row['game_id']] = $row;
		}
		$this->db->sql_freeresult($result);

		return $games;
	}

	public function get_game_ids($tourney_id)
	{
		$result = $this->db->sql_query('SELECT game_id FROM ' . $this->tourney_games_table . ' WHERE tourney_id = ' . (int) $tourney_id);
		$ids = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$ids[] = (int) $row['game_id'];
		}
		$this->db->sql_freeresult($result);

		return $ids;
	}

	/**
	 * Running or upcoming tournaments a game belongs to (hint on the play page).
	 */
	public function active_for_game($game_id)
	{
		$sql = 'SELECT t.*
			FROM ' . $this->tourneys_table . ' t
			JOIN ' . $this->tourney_games_table . ' tg ON tg.tourney_id = t.tourney_id
			WHERE tg.game_id = ' . (int) $game_id . '
				AND t.tourney_finished = 0
				AND t.tourney_end > ' . time() . '
			ORDER BY t.tourney_start ASC';
		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	/**
	 * Number of players per tournament (results above 0 only).
	 *
	 * @return array tourney_id => count
	 */
	public function count_players()
	{
		$sql = 'SELECT tourney_id, COUNT(DISTINCT user_id) AS players
			FROM ' . $this->tourney_scores_table . '
			WHERE score > 0
			GROUP BY tourney_id';
		$result = $this->db->sql_query($sql);
		$counts = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$counts[(int) $row['tourney_id']] = (int) $row['players'];
		}
		$this->db->sql_freeresult($result);

		return $counts;
	}

	/* ---------------------------------------------------------------
	 * Ranking
	 * ------------------------------------------------------------- */

	/**
	 * Places per game and the overall ranking.
	 *
	 * @param array $tourney
	 * @param array $games game_id => game row (all games of the tournament)
	 * @return array ['games' => game_id => [rows with place/points], 'overall' => [rows]]
	 */
	public function standings(array $tourney, array $games)
	{
		$per_game = [];
		$overall = [];

		if (!$games)
		{
			return ['games' => [], 'overall' => []];
		}

		$sql = 'SELECT s.game_id, s.user_id, s.score, s.score_time, s.score_plays, u.username, u.user_colour
			FROM ' . $this->tourney_scores_table . ' s
			JOIN ' . USERS_TABLE . ' u ON u.user_id = s.user_id
			WHERE s.tourney_id = ' . (int) $tourney['tourney_id'] . '
				AND s.score > 0
				AND ' . $this->db->sql_in_set('s.game_id', array_keys($games));
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$per_game[(int) $row['game_id']][] = $row;
		}
		$this->db->sql_freeresult($result);

		foreach ($per_game as $game_id => &$rows)
		{
			$low = (int) $games[$game_id]['game_scoretype'] === games::SCORE_LOW;

			usort($rows, function ($a, $b) use ($low) {
				$cmp = $low ? ((float) $a['score'] <=> (float) $b['score']) : ((float) $b['score'] <=> (float) $a['score']);

				// Same score: whoever reached it first is ahead.
				return $cmp ?: ((int) $a['score_time'] <=> (int) $b['score_time']);
			});

			$place = 0;
			$last = null;

			foreach ($rows as $i => &$row)
			{
				// Equal scores share the place (and its points).
				if ($last === null || (float) $row['score'] !== $last)
				{
					$place = $i + 1;
				}
				$last = (float) $row['score'];

				$row['place'] = $place;
				$row['points'] = isset(self::PLACE_POINTS[$place - 1]) ? self::PLACE_POINTS[$place - 1] : 0;

				$uid = (int) $row['user_id'];
				if (!isset($overall[$uid]))
				{
					$overall[$uid] = [
						'user_id'		=> $uid,
						'username'		=> $row['username'],
						'user_colour'	=> $row['user_colour'],
						'points'		=> 0,
						'games'			=> 0,
						'places'		=> [0, 0, 0],
					];
				}

				$overall[$uid]['points'] += $row['points'];
				$overall[$uid]['games']++;

				if ($place <= 3)
				{
					$overall[$uid]['places'][$place - 1]++;
				}
			}
			unset($row);
		}
		unset($rows);

		$overall = array_values($overall);

		// Points, then first/second/third places, then number of games played, then name.
		usort($overall, function ($a, $b) {
			return ($b['points'] <=> $a['points'])
				?: ($b['places'][0] <=> $a['places'][0])
				?: ($b['places'][1] <=> $a['places'][1])
				?: ($b['places'][2] <=> $a['places'][2])
				?: ($b['games'] <=> $a['games'])
				?: strcasecmp($a['username'], $b['username']);
		});

		foreach ($overall as $i => &$row)
		{
			$row['rank'] = $i + 1;
		}
		unset($row);

		return ['games' => $per_game, 'overall' => $overall];
	}

	/* ---------------------------------------------------------------
	 * Results
	 * ------------------------------------------------------------- */

	/**
	 * A genuine result: counts for every running tournament with this game.
	 *
	 * @param int|null $time when the score was reached (approved flagged scores keep their play time)
	 * @return int number of tournaments the score counted for
	 */
	public function on_score(array $game, $user_id, $score, $time = null)
	{
		if (!$this->enabled())
		{
			return 0;
		}

		$time = $time === null ? time() : (int) $time;
		$score = round((float) $score, 2);

		// 0 (or less) means "nothing reached": no place, no tournament points.
		if ($score <= 0)
		{
			return 0;
		}
		$low = (int) $game['game_scoretype'] === games::SCORE_LOW;

		$sql = 'SELECT t.tourney_id
			FROM ' . $this->tourneys_table . ' t
			JOIN ' . $this->tourney_games_table . ' tg ON tg.tourney_id = t.tourney_id
			WHERE tg.game_id = ' . (int) $game['game_id'] . '
				AND t.tourney_finished = 0
				AND t.tourney_start <= ' . (int) $time . '
				AND t.tourney_end > ' . (int) $time;
		$result = $this->db->sql_query($sql);
		$ids = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$ids[] = (int) $row['tourney_id'];
		}
		$this->db->sql_freeresult($result);

		foreach ($ids as $tourney_id)
		{
			$where = 'tourney_id = ' . $tourney_id . ' AND game_id = ' . (int) $game['game_id'] . ' AND user_id = ' . (int) $user_id;

			$result = $this->db->sql_query('SELECT score, score_plays FROM ' . $this->tourney_scores_table . ' WHERE ' . $where);
			$row = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			if (!$row)
			{
				$this->db->sql_query('INSERT INTO ' . $this->tourney_scores_table . ' ' . $this->db->sql_build_array('INSERT', [
					'tourney_id'	=> $tourney_id,
					'game_id'		=> (int) $game['game_id'],
					'user_id'		=> (int) $user_id,
					'score'			=> $score,
					'score_time'	=> $time,
					'score_plays'	=> 1,
				]));
				continue;
			}

			$data = ['score_plays' => (int) $row['score_plays'] + 1];

			if ($low ? $score < (float) $row['score'] : $score > (float) $row['score'])
			{
				$data['score'] = $score;
				$data['score_time'] = $time;
			}

			$this->db->sql_query('UPDATE ' . $this->tourney_scores_table . ' SET ' . $this->db->sql_build_array('UPDATE', $data) . ' WHERE ' . $where);
		}

		return count($ids);
	}

	/* ---------------------------------------------------------------
	 * Due work (cron and page views)
	 * ------------------------------------------------------------- */

	/**
	 * Started but not yet announced.
	 */
	public function get_due_announcements()
	{
		$sql = 'SELECT * FROM ' . $this->tourneys_table . '
			WHERE tourney_announced = 0
				AND tourney_finished = 0
				AND tourney_start <= ' . time() . '
				AND tourney_end > ' . time();
		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	public function get_due_finishes()
	{
		$sql = 'SELECT * FROM ' . $this->tourneys_table . '
			WHERE tourney_finished = 0
				AND tourney_end <= ' . time();
		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	/**
	 * Mark a step as done; true only for the request that actually changed the row,
	 * so two simultaneous requests never pay or post twice.
	 */
	public function claim($tourney_id, $column)
	{
		$sql = $column === 'tourney_finished'
			? 'SET tourney_finished = 1 WHERE tourney_finished = 0'
			: 'SET tourney_announced = 1 WHERE tourney_announced = 0';

		$this->db->sql_query('UPDATE ' . $this->tourneys_table . ' ' . $sql . ' AND tourney_id = ' . (int) $tourney_id);

		return (bool) $this->db->sql_affectedrows();
	}

	public function set_topic($tourney_id, $topic_id)
	{
		$this->db->sql_query('UPDATE ' . $this->tourneys_table . ' SET tourney_topic_id = ' . (int) $topic_id . ' WHERE tourney_id = ' . (int) $tourney_id);
	}

	/* ---------------------------------------------------------------
	 * Writing (ACP)
	 * ------------------------------------------------------------- */

	/**
	 * @return int tourney id
	 */
	public function save($tourney_id, array $data, array $game_ids)
	{
		if ($tourney_id)
		{
			$this->db->sql_query('UPDATE ' . $this->tourneys_table . ' SET ' . $this->db->sql_build_array('UPDATE', $data) . '
				WHERE tourney_id = ' . (int) $tourney_id);
		}
		else
		{
			$this->db->sql_query('INSERT INTO ' . $this->tourneys_table . ' ' . $this->db->sql_build_array('INSERT', $data));
			$tourney_id = (int) $this->db->sql_nextid();
		}

		$this->db->sql_query('DELETE FROM ' . $this->tourney_games_table . ' WHERE tourney_id = ' . (int) $tourney_id);

		$rows = [];
		foreach (array_unique(array_map('intval', $game_ids)) as $game_id)
		{
			if ($game_id > 0)
			{
				$rows[] = ['tourney_id' => (int) $tourney_id, 'game_id' => $game_id];
			}
		}

		if ($rows)
		{
			$this->db->sql_multi_insert($this->tourney_games_table, $rows);
		}

		// Results of games that were removed from the tournament are dropped.
		$where = 'tourney_id = ' . (int) $tourney_id;
		if ($rows)
		{
			$where .= ' AND ' . $this->db->sql_in_set('game_id', array_column($rows, 'game_id'), true);
		}
		$this->db->sql_query('DELETE FROM ' . $this->tourney_scores_table . ' WHERE ' . $where);

		return (int) $tourney_id;
	}

	public function delete($tourney_id)
	{
		foreach ([$this->tourney_scores_table, $this->tourney_games_table, $this->tourneys_table] as $table)
		{
			$this->db->sql_query('DELETE FROM ' . $table . ' WHERE tourney_id = ' . (int) $tourney_id);
		}
	}

	/* ---------------------------------------------------------------
	 * Clean-up
	 * ------------------------------------------------------------- */

	public function delete_users(array $user_ids)
	{
		if ($user_ids)
		{
			$this->db->sql_query('DELETE FROM ' . $this->tourney_scores_table . ' WHERE ' . $this->db->sql_in_set('user_id', array_map('intval', $user_ids)));
		}
	}

	public function delete_game($game_id)
	{
		$this->db->sql_query('DELETE FROM ' . $this->tourney_scores_table . ' WHERE game_id = ' . (int) $game_id);
		$this->db->sql_query('DELETE FROM ' . $this->tourney_games_table . ' WHERE game_id = ' . (int) $game_id);
	}
}
