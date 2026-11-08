<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\core;

/**
 * Duels: member A challenges member B in a game.
 *
 * A's personal best is frozen when the duel starts. B has until the deadline
 * to beat it with a genuine result (same play session and plausibility check
 * as the highscores). B wins as soon as the score is beaten; otherwise A wins
 * at the deadline. B may decline. Both get phpBB notifications.
 */
class duels
{
	const OPEN = 0;
	const FINISHED = 1;
	const DECLINED = 2;

	const TYPE_CHALLENGE = 'arcadewelten.arcade.notification.type.duel_challenge';
	const TYPE_RESULT = 'arcadewelten.arcade.notification.type.duel_result';

	protected $db;
	protected $config;
	protected $notifications;
	protected $duels_table;
	protected $games_table;
	protected $scores_table;

	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\notification\manager $notifications, $duels_table, $games_table, $scores_table)
	{
		$this->db = $db;
		$this->config = $config;
		$this->notifications = $notifications;
		$this->duels_table = $duels_table;
		$this->games_table = $games_table;
		$this->scores_table = $scores_table;
	}

	public function enabled()
	{
		return !empty($this->config['aw_arcade_duels']);
	}

	public function max_days()
	{
		return max(1, min(30, (int) $this->config['aw_arcade_duel_days']));
	}

	public function max_open()
	{
		return max(1, (int) $this->config['aw_arcade_duel_max']);
	}

	/**
	 * Does the score beat the other one, according to the game's score type?
	 */
	public static function beats(array $game, $score, $other)
	{
		return ((int) $game['game_scoretype'] === games::SCORE_LOW) ? (float) $score < (float) $other : (float) $score > (float) $other;
	}

	/* ---------------------------------------------------------------
	 * Reading
	 * ------------------------------------------------------------- */

	protected function select()
	{
		return 'SELECT d.*, g.game_name, g.game_scoretype, g.cat_id, g.game_image, g.game_dir,
				uc.username AS challenger_name, uc.user_colour AS challenger_colour,
				uo.username AS opponent_name, uo.user_colour AS opponent_colour
			FROM ' . $this->duels_table . ' d
			JOIN ' . $this->games_table . ' g ON g.game_id = d.game_id
			LEFT JOIN ' . USERS_TABLE . ' uc ON uc.user_id = d.challenger_id
			LEFT JOIN ' . USERS_TABLE . ' uo ON uo.user_id = d.opponent_id';
	}

	public function get($duel_id)
	{
		$result = $this->db->sql_query($this->select() . ' WHERE d.duel_id = ' . (int) $duel_id);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row ?: false;
	}

	/**
	 * Open duels of a member: incoming (to beat) and outgoing (waiting), plus recently finished ones.
	 */
	public function for_user($user_id, $finished_limit = 20)
	{
		$user_id = (int) $user_id;
		$open = $this->db->sql_query($this->select() . '
			WHERE d.duel_status = ' . self::OPEN . '
				AND (d.opponent_id = ' . (int) $user_id . ' OR d.challenger_id = ' . (int) $user_id . ')
			ORDER BY d.deadline ASC');
		$rows = $this->db->sql_fetchrowset($open);
		$this->db->sql_freeresult($open);

		$lists = ['incoming' => [], 'outgoing' => [], 'finished' => []];
		foreach ($rows as $row)
		{
			$lists[(int) $row['opponent_id'] === $user_id ? 'incoming' : 'outgoing'][] = $row;
		}

		$result = $this->db->sql_query_limit($this->select() . '
			WHERE d.duel_status <> ' . self::OPEN . '
				AND (d.opponent_id = ' . (int) $user_id . ' OR d.challenger_id = ' . (int) $user_id . ')
			ORDER BY d.end_time DESC', (int) $finished_limit);
		$lists['finished'] = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $lists;
	}

	/**
	 * Open duels a member has to answer (for the counter in the arcade).
	 */
	public function count_incoming($user_id)
	{
		$result = $this->db->sql_query('SELECT COUNT(duel_id) AS total FROM ' . $this->duels_table . '
			WHERE opponent_id = ' . (int) $user_id . ' AND duel_status = ' . self::OPEN . ' AND deadline >= ' . time());
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		return $total;
	}

	/**
	 * Open duels of a member as opponent in one game (banner on the play page).
	 */
	public function incoming_for_game($user_id, $game_id)
	{
		$result = $this->db->sql_query($this->select() . '
			WHERE d.opponent_id = ' . (int) $user_id . '
				AND d.game_id = ' . (int) $game_id . '
				AND d.duel_status = ' . self::OPEN . '
				AND d.deadline >= ' . time() . '
			ORDER BY d.deadline ASC');
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	public function count_open_by($challenger_id)
	{
		$result = $this->db->sql_query('SELECT COUNT(duel_id) AS total FROM ' . $this->duels_table . '
			WHERE challenger_id = ' . (int) $challenger_id . ' AND duel_status = ' . self::OPEN);
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		return $total;
	}

	public function exists_open($game_id, $challenger_id, $opponent_id)
	{
		$sql = 'SELECT duel_id FROM ' . $this->duels_table . '
			WHERE game_id = ' . (int) $game_id . '
				AND duel_status = ' . self::OPEN . '
				AND ((challenger_id = ' . (int) $challenger_id . ' AND opponent_id = ' . (int) $opponent_id . ')
					OR (challenger_id = ' . (int) $opponent_id . ' AND opponent_id = ' . (int) $challenger_id . '))';
		$result = $this->db->sql_query_limit($sql, 1);
		$found = (bool) $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $found;
	}

	/**
	 * Personal best of a member in a game, or null.
	 */
	public function best_score($game_id, $user_id)
	{
		$result = $this->db->sql_query('SELECT score FROM ' . $this->scores_table . '
			WHERE game_id = ' . (int) $game_id . ' AND user_id = ' . (int) $user_id);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row ? (float) $row['score'] : null;
	}

	/* ---------------------------------------------------------------
	 * Changes
	 * ------------------------------------------------------------- */

	/**
	 * Start a duel (checks are done by the caller) and notify the opponent.
	 *
	 * @return int duel id
	 */
	public function create(array $game, $challenger_id, $opponent_id, $score, $days)
	{
		$now = time();
		$data = [
			'game_id'			=> (int) $game['game_id'],
			'challenger_id'		=> (int) $challenger_id,
			'opponent_id'		=> (int) $opponent_id,
			'challenger_score'	=> round((float) $score, 2),
			'opponent_score'	=> 0,
			'opponent_played'	=> 0,
			'duel_status'		=> self::OPEN,
			'winner_id'			=> 0,
			'start_time'		=> $now,
			'deadline'			=> $now + max(1, min($this->max_days(), (int) $days)) * 86400,
			'end_time'			=> 0,
		];

		$this->db->sql_query('INSERT INTO ' . $this->duels_table . ' ' . $this->db->sql_build_array('INSERT', $data));
		$duel_id = (int) $this->db->sql_nextid();

		$this->notifications->add_notifications(self::TYPE_CHALLENGE, $this->notification_data($duel_id));

		return $duel_id;
	}

	/**
	 * A genuine score was saved: update open duels where the player is the opponent.
	 *
	 * @return int number of duels won with this score
	 */
	public function on_score(array $game, $user_id, $score)
	{
		if (!$this->enabled())
		{
			return 0;
		}

		$sql = 'SELECT * FROM ' . $this->duels_table . '
			WHERE opponent_id = ' . (int) $user_id . '
				AND game_id = ' . (int) $game['game_id'] . '
				AND duel_status = ' . self::OPEN . '
				AND deadline >= ' . time();
		$result = $this->db->sql_query($sql);
		$duels = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		$won = 0;
		$score = round((float) $score, 2);

		foreach ($duels as $duel)
		{
			$data = [];

			if (!$duel['opponent_played'] || self::beats($game, $score, $duel['opponent_score']))
			{
				$data = ['opponent_score' => $score, 'opponent_played' => 1];
			}

			if ($data)
			{
				$this->db->sql_query('UPDATE ' . $this->duels_table . ' SET ' . $this->db->sql_build_array('UPDATE', $data) . '
					WHERE duel_id = ' . (int) $duel['duel_id']);
			}

			if (self::beats($game, $score, $duel['challenger_score']))
			{
				$this->finish($duel['duel_id'], self::FINISHED, (int) $user_id);
				$won++;
			}
		}

		return $won;
	}

	/**
	 * The opponent declines; the challenger is notified.
	 */
	public function decline(array $duel)
	{
		$this->finish($duel['duel_id'], self::DECLINED, 0);
	}

	/**
	 * Close duels past their deadline: the challenger wins. Called by the cron task.
	 *
	 * @return int number of closed duels
	 */
	public function expire()
	{
		$result = $this->db->sql_query('SELECT duel_id, challenger_id FROM ' . $this->duels_table . '
			WHERE duel_status = ' . self::OPEN . ' AND deadline < ' . time());
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		foreach ($rows as $row)
		{
			$this->finish($row['duel_id'], self::FINISHED, (int) $row['challenger_id']);
		}

		return count($rows);
	}

	protected function finish($duel_id, $status, $winner_id)
	{
		// Only an open duel can end (guards against two requests at the same time).
		$this->db->sql_query('UPDATE ' . $this->duels_table . '
			SET duel_status = ' . (int) $status . ', winner_id = ' . (int) $winner_id . ', end_time = ' . time() . '
			WHERE duel_id = ' . (int) $duel_id . ' AND duel_status = ' . self::OPEN);

		if (!$this->db->sql_affectedrows())
		{
			return;
		}

		// The challenge is answered: remove it from the opponent's notifications.
		$this->notifications->delete_notifications(self::TYPE_CHALLENGE, (int) $duel_id);
		$this->notifications->add_notifications(self::TYPE_RESULT, $this->notification_data($duel_id));
	}

	/**
	 * Everything the notification types need, read once when the notification is created.
	 */
	protected function notification_data($duel_id)
	{
		$duel = $this->get($duel_id);

		return [
			'duel_id'			=> (int) $duel['duel_id'],
			'game_id'			=> (int) $duel['game_id'],
			'game_name'			=> $duel['game_name'],
			'game_scoretype'	=> (int) $duel['game_scoretype'],
			'challenger_id'		=> (int) $duel['challenger_id'],
			'opponent_id'		=> (int) $duel['opponent_id'],
			'challenger_score'	=> (float) $duel['challenger_score'],
			'opponent_score'	=> (float) $duel['opponent_score'],
			'opponent_played'	=> (int) $duel['opponent_played'],
			'duel_status'		=> (int) $duel['duel_status'],
			'winner_id'			=> (int) $duel['winner_id'],
			'deadline'			=> (int) $duel['deadline'],
		];
	}

	/* ---------------------------------------------------------------
	 * Cleanup
	 * ------------------------------------------------------------- */

	public function delete_users(array $user_ids)
	{
		$user_ids = array_map('intval', $user_ids);

		if (!$user_ids)
		{
			return;
		}

		$where = $this->db->sql_in_set('challenger_id', $user_ids) . ' OR ' . $this->db->sql_in_set('opponent_id', $user_ids);
		$this->delete_where($where);
	}

	public function delete_game($game_id)
	{
		$this->delete_where('game_id = ' . (int) $game_id);
	}

	protected function delete_where($where)
	{
		$result = $this->db->sql_query('SELECT duel_id FROM ' . $this->duels_table . ' WHERE ' . $where);
		$ids = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$ids[] = (int) $row['duel_id'];
		}
		$this->db->sql_freeresult($result);

		if (!$ids)
		{
			return;
		}

		foreach ([self::TYPE_CHALLENGE, self::TYPE_RESULT] as $type)
		{
			$this->notifications->delete_notifications($type, $ids);
		}

		$this->db->sql_query('DELETE FROM ' . $this->duels_table . ' WHERE ' . $this->db->sql_in_set('duel_id', $ids));
	}
}
