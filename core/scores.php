<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\core;

/**
 * Play sessions and highscores.
 */
class scores
{
	/** Maximum age of a play session in seconds. */
	const SESSION_MAX_AGE = 14400;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	const CACHE_KEY = '_aw_arcade_champions';
	const MEDALS_KEY = '_aw_arcade_medals';

	protected $scores_table;
	protected $games_table;
	protected $sessions_table;

	/** @var \phpbb\cache\driver\driver_interface */
	protected $cache;

	protected $cats_table;
	protected $flagged_table;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var duels */
	protected $duels;

	/** @var tourneys|null */
	protected $tourneys;

	/** @var jackpot|null */
	protected $jackpot;

	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\cache\driver\driver_interface $cache, $scores_table, $games_table, $sessions_table, $cats_table, $flagged_table, \phpbb\config\config $config, duels $duels, ?tourneys $tourneys = null, ?jackpot $jackpot = null)
	{
		$this->tourneys = $tourneys;
		$this->jackpot = $jackpot;
		$this->duels = $duels;
		$this->cats_table = $cats_table;
		$this->flagged_table = $flagged_table;
		$this->config = $config;
		$this->db = $db;
		$this->cache = $cache;
		$this->scores_table = $scores_table;
		$this->games_table = $games_table;
		$this->sessions_table = $sessions_table;
	}

	/* ---------------------------------------------------------------
	 * Play sessions
	 * ------------------------------------------------------------- */

	/**
	 * Start a play session when the game page is opened.
	 *
	 * @return string play id (32 hex chars)
	 */
	/* ---------------------------------------------------------------
	 * Plausibility checks and review list
	 * ------------------------------------------------------------- */

	/**
	 * Check a submitted score against the play session.
	 *
	 * @return string '' if plausible, otherwise the reason (too_fast, too_high)
	 */
	public function check_plausible(array $game, array $play, $score)
	{
		$min_time = (int) $game['game_min_time'] > 0 ? (int) $game['game_min_time'] : (int) $this->config['aw_arcade_min_time'];
		$elapsed = time() - (int) $play['start_time'];

		if ($min_time > 0 && $elapsed < $min_time && (float) $score > 0)
		{
			return 'too_fast';
		}

		if ((float) $game['game_max_score'] > 0 && (int) $game['game_scoretype'] !== games::SCORE_LOW && (float) $score > (float) $game['game_max_score'])
		{
			return 'too_high';
		}

		return '';
	}

	public function flag(array $game, $user_id, $score, array $play, $reason)
	{
		$this->db->sql_query('INSERT INTO ' . $this->flagged_table . ' ' . $this->db->sql_build_array('INSERT', [
			'game_id'		=> (int) $game['game_id'],
			'user_id'		=> (int) $user_id,
			'score'			=> round((float) $score, 2),
			'play_seconds'	=> max(0, time() - (int) $play['start_time']),
			'flag_reason'	=> (string) $reason,
			'flag_time'		=> time(),
		]));
	}

	public function get_flagged()
	{
		$sql = 'SELECT f.*, g.game_name, g.game_scoretype, g.game_id, u.username, u.user_colour
			FROM ' . $this->flagged_table . ' f
			LEFT JOIN ' . $this->games_table . ' g ON g.game_id = f.game_id
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = f.user_id
			ORDER BY f.flag_time DESC';
		$result = $this->db->sql_query_limit($sql, 500);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	public function get_flag($flag_id)
	{
		$sql = 'SELECT * FROM ' . $this->flagged_table . ' WHERE flag_id = ' . (int) $flag_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row ?: false;
	}

	public function delete_flag($flag_id)
	{
		$this->db->sql_query('DELETE FROM ' . $this->flagged_table . ' WHERE flag_id = ' . (int) $flag_id);
	}

	public function count_flagged()
	{
		$result = $this->db->sql_query('SELECT COUNT(flag_id) AS total FROM ' . $this->flagged_table);
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		return $total;
	}

	/**
	 * Delete expired play sessions.
	 */
	public function cleanup_sessions()
	{
		$this->db->sql_query('DELETE FROM ' . $this->sessions_table . ' WHERE start_time < ' . (time() - self::SESSION_MAX_AGE));
	}

	public function start_play($user_id, $game_id)
	{
		// Housekeeping: drop expired sessions and older sessions of this user for this game.
		$this->cleanup_sessions();
		$this->db->sql_query('DELETE FROM ' . $this->sessions_table . ' WHERE user_id = ' . (int) $user_id . ' AND game_id = ' . (int) $game_id);

		$play_id = bin2hex(random_bytes(16));

		$this->db->sql_query('INSERT INTO ' . $this->sessions_table . ' ' . $this->db->sql_build_array('INSERT', [
			'play_id'		=> $play_id,
			'user_id'		=> (int) $user_id,
			'game_id'		=> (int) $game_id,
			'start_time'	=> time(),
			'randchar1'		=> 0,
			'randchar2'		=> 0,
			'pending_score'	=> 0,
			'pending_set'	=> 0,
		]));

		return $play_id;
	}

	/**
	 * Latest valid play session of a user, optionally for one game.
	 */
	public function get_play($user_id, $game_id = 0, $play_id = '')
	{
		$sql = 'SELECT * FROM ' . $this->sessions_table . '
			WHERE user_id = ' . (int) $user_id . '
				AND start_time >= ' . (time() - self::SESSION_MAX_AGE) .
			($game_id ? ' AND game_id = ' . (int) $game_id : '') .
			($play_id !== '' ? " AND play_id = '" . $this->db->sql_escape($play_id) . "'" : '') . '
			ORDER BY start_time DESC';
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row ?: false;
	}

	public function update_play($play_id, array $data)
	{
		$this->db->sql_query('UPDATE ' . $this->sessions_table . ' SET ' . $this->db->sql_build_array('UPDATE', $data) . " WHERE play_id = '" . $this->db->sql_escape($play_id) . "'");
	}

	public function end_play($play_id)
	{
		$this->db->sql_query('DELETE FROM ' . $this->sessions_table . " WHERE play_id = '" . $this->db->sql_escape($play_id) . "'");
	}

	/* ---------------------------------------------------------------
	 * Scores
	 * ------------------------------------------------------------- */

	/**
	 * Store a score; only the personal best per game is kept.
	 *
	 * @param int|null $time when the score was reached (approved flagged scores: time of the play)
	 * @return array ['improved' => bool, 'best' => float, 'duels_won' => int, 'tourneys' => int, 'jackpot' => float]
	 */
	public function save_score(array $game, $user_id, $score, $time = null)
	{
		$tourneys = $this->tourneys ? $this->tourneys->on_score($game, $user_id, $score, $time) : 0;
		$jackpot = $this->jackpot ? $this->jackpot->on_score($game, $user_id, $score) : 0;

		$score = round((float) $score, 2);
		$low_wins = (int) $game['game_scoretype'] === games::SCORE_LOW;

		$sql = 'SELECT score, score_plays FROM ' . $this->scores_table . '
			WHERE game_id = ' . (int) $game['game_id'] . '
				AND user_id = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row)
		{
			$this->db->sql_query('INSERT INTO ' . $this->scores_table . ' ' . $this->db->sql_build_array('INSERT', [
				'game_id'		=> (int) $game['game_id'],
				'user_id'		=> (int) $user_id,
				'score'			=> $score,
				'score_time'	=> time(),
				'score_plays'	=> 1,
			]));

			$this->cache->destroy(self::CACHE_KEY);
			$this->cache->destroy(self::MEDALS_KEY);

			return ['improved' => true, 'best' => $score, 'duels_won' => $this->duels->on_score($game, $user_id, $score), 'tourneys' => $tourneys, 'jackpot' => $jackpot];
		}

		$old = (float) $row['score'];
		$improved = $low_wins ? $score < $old : $score > $old;

		$data = ['score_plays' => (int) $row['score_plays'] + 1];

		if ($improved)
		{
			$data['score'] = $score;
			$data['score_time'] = time();
			$this->cache->destroy(self::CACHE_KEY);
			$this->cache->destroy(self::MEDALS_KEY);
		}

		$this->db->sql_query('UPDATE ' . $this->scores_table . ' SET ' . $this->db->sql_build_array('UPDATE', $data) . '
			WHERE game_id = ' . (int) $game['game_id'] . ' AND user_id = ' . (int) $user_id);

		// Every genuine result counts for open duels, not only a new personal best.
		return ['improved' => $improved, 'best' => $improved ? $score : $old, 'duels_won' => $this->duels->on_score($game, $user_id, $score), 'tourneys' => $tourneys, 'jackpot' => $jackpot];
	}

	/**
	 * Highscore list of one game.
	 */
	public function get_scores(array $game, $start = 0, $limit = 25)
	{
		$order = ((int) $game['game_scoretype'] === games::SCORE_LOW) ? 'ASC' : 'DESC';

		$sql = 'SELECT s.*, u.username, u.user_colour
			FROM ' . $this->scores_table . ' s
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = s.user_id
			WHERE s.game_id = ' . (int) $game['game_id'] . '
			ORDER BY s.score ' . $order . ', s.score_time ASC';
		$result = $this->db->sql_query_limit($sql, (int) $limit, (int) $start);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	public function count_scores($game_id)
	{
		$sql = 'SELECT COUNT(user_id) AS total FROM ' . $this->scores_table . ' WHERE game_id = ' . (int) $game_id;
		$result = $this->db->sql_query($sql);
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		return $total;
	}

	/**
	 * 1-based rank of a user in a game, 0 if no score.
	 */
	public function get_rank(array $game, $user_id)
	{
		$sql = 'SELECT score FROM ' . $this->scores_table . '
			WHERE game_id = ' . (int) $game['game_id'] . ' AND user_id = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row)
		{
			return 0;
		}

		$sql = 'SELECT COUNT(user_id) AS better FROM ' . $this->scores_table . '
			WHERE game_id = ' . (int) $game['game_id'] . '
				AND score ' . (((int) $game['game_scoretype'] === games::SCORE_LOW) ? '<' : '>') . ' ' . (float) $row['score'];
		$result = $this->db->sql_query($sql);
		$better = (int) $this->db->sql_fetchfield('better');
		$this->db->sql_freeresult($result);

		return $better + 1;
	}

	/**
	 * Champions (best score holder) for a list of games.
	 *
	 * @param array $games rows with game_id and game_scoretype
	 * @return array game_id => score row with username
	 */
	public function get_champions(array $games)
	{
		if (empty($games))
		{
			return [];
		}

		$types = [];
		foreach ($games as $g)
		{
			$types[(int) $g['game_id']] = (int) $g['game_scoretype'];
		}

		$sql = 'SELECT s.game_id, s.user_id, s.score, s.score_time, u.username, u.user_colour
			FROM ' . $this->scores_table . ' s
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = s.user_id
			WHERE ' . $this->db->sql_in_set('s.game_id', array_keys($types));
		$result = $this->db->sql_query($sql);

		$champions = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$id = (int) $row['game_id'];
			$low = $types[$id] === games::SCORE_LOW;

			if (!isset($champions[$id]))
			{
				$champions[$id] = $row;
				continue;
			}

			$cur = (float) $champions[$id]['score'];
			$new = (float) $row['score'];

			if (($low ? $new < $cur : $new > $cur) || ($new == $cur && $row['score_time'] < $champions[$id]['score_time']))
			{
				$champions[$id] = $row;
			}
		}
		$this->db->sql_freeresult($result);

		return $champions;
	}

	/**
	 * Remove all arcade data of deleted users (GDPR).
	 */
	public function delete_users(array $user_ids)
	{
		if (empty($user_ids))
		{
			return;
		}

		$this->db->sql_query('DELETE FROM ' . $this->scores_table . ' WHERE ' . $this->db->sql_in_set('user_id', array_map('intval', $user_ids)));
		$this->db->sql_query('DELETE FROM ' . $this->sessions_table . ' WHERE ' . $this->db->sql_in_set('user_id', array_map('intval', $user_ids)));
		$this->db->sql_query('DELETE FROM ' . $this->flagged_table . ' WHERE ' . $this->db->sql_in_set('user_id', array_map('intval', $user_ids)));
		$this->cache->destroy(self::CACHE_KEY);
		$this->cache->destroy(self::MEDALS_KEY);
	}

	public function delete_game_scores($game_id)
	{
		$this->db->sql_query('DELETE FROM ' . $this->scores_table . ' WHERE game_id = ' . (int) $game_id);
		$this->db->sql_query('DELETE FROM ' . $this->sessions_table . ' WHERE game_id = ' . (int) $game_id);
		$this->db->sql_query('DELETE FROM ' . $this->flagged_table . ' WHERE game_id = ' . (int) $game_id);
		$this->cache->destroy(self::CACHE_KEY);
		$this->cache->destroy(self::MEDALS_KEY);
	}

	public function get_user($user_id)
	{
		$sql = 'SELECT user_id, username, user_colour FROM ' . USERS_TABLE . ' WHERE user_id = ' . (int) $user_id . ' AND user_type <> ' . USER_IGNORE;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row ?: false;
	}

	/**
	 * Empty all highscores (new season).
	 */
	public function reset_all()
	{
		$this->db->sql_query('DELETE FROM ' . $this->scores_table);
		$this->db->sql_query('DELETE FROM ' . $this->sessions_table);
		$this->db->sql_query('DELETE FROM ' . $this->flagged_table);
		$this->cache->destroy(self::CACHE_KEY);
		$this->cache->destroy(self::MEDALS_KEY);
	}

	public function purge_cache()
	{
		$this->cache->destroy(self::CACHE_KEY);
		$this->cache->destroy(self::MEDALS_KEY);
	}

	/* ---------------------------------------------------------------
	 * Top lists and records
	 * ------------------------------------------------------------- */

	/**
	 * Best n entries for each of the given games.
	 *
	 * @return array game_id => list of rows (rank order)
	 */
	public function get_top(array $games, $n = 3)
	{
		if (empty($games))
		{
			return [];
		}

		$types = [];
		foreach ($games as $g)
		{
			$types[(int) $g['game_id']] = (int) $g['game_scoretype'];
		}

		$sql = 'SELECT s.game_id, s.user_id, s.score, s.score_time, u.username, u.user_colour
			FROM ' . $this->scores_table . ' s
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = s.user_id
			WHERE ' . $this->db->sql_in_set('s.game_id', array_keys($types));
		$result = $this->db->sql_query($sql);

		$by_game = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$by_game[(int) $row['game_id']][] = $row;
		}
		$this->db->sql_freeresult($result);

		foreach ($by_game as $id => &$rows)
		{
			$low = $types[$id] === games::SCORE_LOW;
			usort($rows, function ($a, $b) use ($low) {
				$cmp = $low ? ((float) $a['score'] <=> (float) $b['score']) : ((float) $b['score'] <=> (float) $a['score']);
				return $cmp ?: ((int) $a['score_time'] <=> (int) $b['score_time']);
			});
			$rows = array_slice($rows, 0, $n);
		}
		unset($rows);

		return $by_game;
	}

	/**
	 * Current record holder of every active game (cached).
	 *
	 * @return array game_id => row with user_id, username, user_colour, score, score_time, game_name
	 */
	public function all_champions()
	{
		$champions = $this->cache->get(self::CACHE_KEY);

		if ($champions !== false)
		{
			return $champions;
		}

		$sql = 'SELECT s.game_id, s.user_id, s.score, s.score_time, u.username, u.user_colour, g.game_name, g.game_scoretype, g.cat_id,
				CASE WHEN c.cat_password IS NULL OR c.cat_password = \'\' THEN 0 ELSE 1 END AS cat_protected
			FROM ' . $this->scores_table . ' s
			INNER JOIN ' . $this->games_table . ' g ON g.game_id = s.game_id AND g.game_active = 1
			LEFT JOIN ' . $this->cats_table . ' c ON c.cat_id = g.cat_id
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = s.user_id';
		$result = $this->db->sql_query($sql);

		$champions = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$id = (int) $row['game_id'];
			$low = (int) $row['game_scoretype'] === games::SCORE_LOW;

			if (!isset($champions[$id]))
			{
				$champions[$id] = $row;
				continue;
			}

			$cur = (float) $champions[$id]['score'];
			$new = (float) $row['score'];

			if (($low ? $new < $cur : $new > $cur) || ($new == $cur && $row['score_time'] < $champions[$id]['score_time']))
			{
				$champions[$id] = $row;
			}
		}
		$this->db->sql_freeresult($result);

		$this->cache->put(self::CACHE_KEY, $champions, 3600);

		return $champions;
	}

	/**
	 * Number of records per user.
	 *
	 * @return array user_id => count
	 */
	public function record_counts()
	{
		$counts = [];

		foreach ($this->all_champions() as $row)
		{
			$uid = (int) $row['user_id'];
			$counts[$uid] = isset($counts[$uid]) ? $counts[$uid] + 1 : 1;
		}

		arsort($counts);

		return $counts;
	}

	/**
	 * First, second and third places per member over all active games.
	 *
	 * @return array user_id => [1 => n, 2 => n, 3 => n]
	 */
	public function medal_counts()
	{
		$medals = $this->cache->get(self::MEDALS_KEY);

		if ($medals !== false)
		{
			return $medals;
		}

		$sql = 'SELECT s.game_id, s.user_id, s.score, s.score_time, g.game_scoretype
			FROM ' . $this->scores_table . ' s
			INNER JOIN ' . $this->games_table . ' g ON g.game_id = s.game_id AND g.game_active = 1';
		$result = $this->db->sql_query($sql);
		$by_game = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$by_game[(int) $row['game_id']][] = $row;
		}
		$this->db->sql_freeresult($result);

		$medals = [];
		foreach ($by_game as $rows)
		{
			$low = (int) $rows[0]['game_scoretype'] === games::SCORE_LOW;

			usort($rows, function ($a, $b) use ($low) {
				$sa = (float) $a['score'];
				$sb = (float) $b['score'];

				if ($sa != $sb)
				{
					return ($low ? $sa > $sb : $sa < $sb) ? 1 : -1;
				}

				return (int) $a['score_time'] <=> (int) $b['score_time'];
			});

			foreach (array_slice($rows, 0, 3) as $i => $row)
			{
				$uid = (int) $row['user_id'];
				if (!isset($medals[$uid]))
				{
					$medals[$uid] = [1 => 0, 2 => 0, 3 => 0];
				}
				$medals[$uid][$i + 1]++;
			}
		}

		$this->cache->put(self::MEDALS_KEY, $medals, 3600);

		return $medals;
	}

	/**
	 * Users with the most records.
	 */
	public function hall_of_fame($n = 3)
	{
		$champions = $this->all_champions();
		$counts = $this->record_counts();
		$users = [];

		foreach ($champions as $row)
		{
			$users[(int) $row['user_id']] = $row;
		}

		$top = array_slice($counts, 0, $n, true);
		$avatars = [];

		if ($top)
		{
			$sql = 'SELECT user_id, user_avatar, user_avatar_type, user_avatar_width, user_avatar_height
				FROM ' . USERS_TABLE . '
				WHERE ' . $this->db->sql_in_set('user_id', array_keys($top));
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$avatars[(int) $row['user_id']] = $row;
			}
			$this->db->sql_freeresult($result);
		}

		$list = [];
		foreach ($top as $uid => $count)
		{
			$list[] = [
				'user_id'		=> $uid,
				'username'		=> $users[$uid]['username'],
				'user_colour'	=> $users[$uid]['user_colour'],
				'records'		=> $count,
				'avatar_row'	=> isset($avatars[$uid]) ? $avatars[$uid] : [],
			];
		}

		return $list;
	}

	/**
	 * Champion per category: member with the most records in it.
	 *
	 * @return array cat_id => ['user_id', 'username', 'user_colour', 'records']
	 */
	public function category_champions()
	{
		$per_cat = [];
		$names = [];

		foreach ($this->all_champions() as $row)
		{
			$cid = (int) $row['cat_id'];
			$uid = (int) $row['user_id'];
			$per_cat[$cid][$uid] = isset($per_cat[$cid][$uid]) ? $per_cat[$cid][$uid] + 1 : 1;
			$names[$uid] = $row;
		}

		$result = [];
		foreach ($per_cat as $cid => $counts)
		{
			arsort($counts);
			$uid = (int) key($counts);
			$result[$cid] = [
				'user_id'		=> $uid,
				'username'		=> $names[$uid]['username'],
				'user_colour'	=> $names[$uid]['user_colour'],
				'records'		=> reset($counts),
			];
		}

		return $result;
	}

	/**
	 * Most recent records.
	 */
	public function latest_records($n = 5, array $hidden_cats = [])
	{
		$champions = array_values(array_filter($this->all_champions(), function ($row) use ($hidden_cats) {
			return empty($row['cat_protected']) && !in_array((int) $row['cat_id'], $hidden_cats, true);
		}));

		usort($champions, function ($a, $b) {
			return (int) $b['score_time'] <=> (int) $a['score_time'];
		});

		return array_slice($champions, 0, $n);
	}

	/**
	 * Records held by one user, newest first.
	 */
	public function user_records($user_id)
	{
		$records = array_filter($this->all_champions(), function ($row) use ($user_id) {
			return (int) $row['user_id'] === (int) $user_id;
		});

		usort($records, function ($a, $b) {
			return (int) $b['score_time'] <=> (int) $a['score_time'];
		});

		return $records;
	}
}
