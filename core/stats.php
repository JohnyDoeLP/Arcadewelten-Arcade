<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\core;

/**
 * Numbers of one member for the statistics page.
 *
 * Everything is worked out from what the arcade stores anyway; no play time
 * or anything else is recorded for this. Counts (rounds, records, medals)
 * cover every active game, like the hall of fame does. Lists that name games
 * only hold games the reader may see.
 */
class stats
{
	const CACHE_KEY = '_aw_arcade_stats_';
	const CACHE_TIME = 600;
	const TOP_GAMES = 3;
	const BEST_PLACES = 5;

	protected $db;
	protected $cache;
	protected $scores;
	protected $tourneys;
	protected $scores_table;
	protected $games_table;
	protected $duels_table;
	protected $ratings_table;
	protected $tourney_scores_table;
	protected $favorites_table;

	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\cache\driver\driver_interface $cache, scores $scores, tourneys $tourneys, $scores_table, $games_table, $duels_table, $ratings_table, $tourney_scores_table, $favorites_table)
	{
		$this->db = $db;
		$this->cache = $cache;
		$this->scores = $scores;
		$this->tourneys = $tourneys;
		$this->scores_table = $scores_table;
		$this->games_table = $games_table;
		$this->duels_table = $duels_table;
		$this->ratings_table = $ratings_table;
		$this->tourney_scores_table = $tourney_scores_table;
		$this->favorites_table = $favorites_table;
	}

	/**
	 * All numbers of a member, cached for a few minutes.
	 */
	public function for_user($user_id)
	{
		$user_id = (int) $user_id;
		$key = self::CACHE_KEY . $user_id;
		$data = $this->cache->get($key);

		if ($data !== false)
		{
			return $data;
		}

		$data = $this->summary($user_id) + [
			'medals'	=> $this->medals($user_id),
			'records'	=> $this->records($user_id),
			'ratings'	=> $this->ratings($user_id),
			'favorites'	=> $this->favorites($user_id),
			'top_games'	=> $this->top_games($user_id),
			'best'		=> $this->best_places($user_id),
			'duels'		=> $this->duels($user_id),
			'tourneys'	=> $this->tourney_places($user_id),
		];

		$this->cache->put($key, $data, self::CACHE_TIME);

		return $data;
	}

	public function forget($user_id = 0)
	{
		if ($user_id)
		{
			$this->cache->destroy(self::CACHE_KEY . (int) $user_id);
		}
	}

	/**
	 * Rounds, games and the first and last round.
	 */
	protected function summary($user_id)
	{
		$sql = 'SELECT COUNT(*) AS games_played, SUM(s.score_plays) AS rounds,
				MIN(s.score_time) AS first_time, MAX(s.score_time) AS last_time
			FROM ' . $this->scores_table . ' s
			INNER JOIN ' . $this->games_table . ' g ON g.game_id = s.game_id AND g.game_active = 1
			WHERE s.user_id = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$result = $this->db->sql_query('SELECT COUNT(*) AS total FROM ' . $this->games_table . ' WHERE game_active = 1');
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		return [
			'games_played'	=> (int) $row['games_played'],
			'games_total'	=> $total,
			'rounds'		=> (int) $row['rounds'],
			'first_time'	=> (int) $row['first_time'],
			'last_time'		=> (int) $row['last_time'],
		];
	}

	protected function medals($user_id)
	{
		$all = $this->scores->medal_counts();

		return isset($all[(int) $user_id]) ? $all[(int) $user_id] : [1 => 0, 2 => 0, 3 => 0];
	}

	protected function records($user_id)
	{
		$counts = $this->scores->record_counts();

		return isset($counts[(int) $user_id]) ? (int) $counts[(int) $user_id] : 0;
	}

	protected function ratings($user_id)
	{
		$result = $this->db->sql_query('SELECT COUNT(*) AS n FROM ' . $this->ratings_table . ' WHERE user_id = ' . (int) $user_id);
		$n = (int) $this->db->sql_fetchfield('n');
		$this->db->sql_freeresult($result);

		return $n;
	}

	/**
	 * The games the member played most often. A few more than needed are
	 * fetched, because the reader may not see all of them.
	 */
	protected function favorites($user_id)
	{
		$sql = 'SELECT COUNT(*) AS n
			FROM ' . $this->favorites_table . ' f
			INNER JOIN ' . $this->games_table . ' g ON g.game_id = f.game_id AND g.game_active = 1
			WHERE f.user_id = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$n = (int) $this->db->sql_fetchfield('n');
		$this->db->sql_freeresult($result);

		return $n;
	}

	protected function top_games($user_id)
	{
		$sql = 'SELECT s.game_id, s.score_plays, g.game_name, g.cat_id
			FROM ' . $this->scores_table . ' s
			INNER JOIN ' . $this->games_table . ' g ON g.game_id = s.game_id AND g.game_active = 1
			WHERE s.user_id = ' . (int) $user_id . '
			ORDER BY s.score_plays DESC, g.game_name ASC';
		$result = $this->db->sql_query_limit($sql, self::TOP_GAMES * 6);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	/**
	 * Games where the member is on the podium, best place first.
	 */
	protected function best_places($user_id)
	{
		$user_id = (int) $user_id;
		$mine = [];

		foreach ($this->scores->podium() as $game_id => $top)
		{
			$place = array_search($user_id, $top, true);

			if ($place !== false)
			{
				$mine[(int) $game_id] = $place + 1;
			}
		}

		if (!$mine)
		{
			return [];
		}

		$sql = 'SELECT s.game_id, s.score, g.game_name, g.cat_id
			FROM ' . $this->scores_table . ' s
			INNER JOIN ' . $this->games_table . ' g ON g.game_id = s.game_id AND g.game_active = 1
			WHERE s.user_id = ' . (int) $user_id . '
				AND ' . $this->db->sql_in_set('s.game_id', array_map('intval', array_keys($mine)));
		$result = $this->db->sql_query($sql);
		$rows = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$row['place'] = $mine[(int) $row['game_id']];
			$rows[] = $row;
		}
		$this->db->sql_freeresult($result);

		usort($rows, function ($a, $b) {
			return ($a['place'] <=> $b['place']) ?: strcasecmp($a['game_name'], $b['game_name']);
		});

		return $rows;
	}

	protected function duels($user_id)
	{
		$user_id = (int) $user_id;
		$out = ['won' => 0, 'lost' => 0, 'open' => 0];

		$sql = 'SELECT duel_status, winner_id
			FROM ' . $this->duels_table . '
			WHERE challenger_id = ' . (int) $user_id . ' OR opponent_id = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			if ((int) $row['duel_status'] === duels::OPEN)
			{
				$out['open']++;
			}
			else if ((int) $row['duel_status'] === duels::FINISHED)
			{
				if ((int) $row['winner_id'] === $user_id)
				{
					$out['won']++;
				}
				else if ((int) $row['winner_id'])
				{
					$out['lost']++;
				}
			}
		}
		$this->db->sql_freeresult($result);

		return $out;
	}

	/**
	 * Tournaments the member took part in, with wins and podium places.
	 * Only finished tournaments count for the places.
	 */
	protected function tourney_places($user_id)
	{
		$user_id = (int) $user_id;
		$out = ['joined' => 0, 'won' => 0, 'podium' => 0];

		$sql = 'SELECT DISTINCT tourney_id FROM ' . $this->tourney_scores_table . ' WHERE user_id = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$ids = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$ids[] = (int) $row['tourney_id'];
		}
		$this->db->sql_freeresult($result);

		$out['joined'] = count($ids);

		foreach ($ids as $id)
		{
			$tourney = $this->tourneys->get($id);

			if (!$tourney || !(int) $tourney['tourney_finished'])
			{
				continue;
			}

			$standings = $this->tourneys->standings($tourney, $this->tourneys->get_games($id));

			foreach ($standings['overall'] as $row)
			{
				if ((int) $row['user_id'] !== $user_id)
				{
					continue;
				}

				$place = (int) $row['place'];

				if ($place === 1)
				{
					$out['won']++;
				}

				if ($place >= 1 && $place <= 3)
				{
					$out['podium']++;
				}

				break;
			}
		}

		return $out;
	}
}
