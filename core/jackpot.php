<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\core;

/**
 * Jackpot (needs Ultimate Points): a share of every play cost goes into a pot
 * per game. Whoever beats the all-time record of that game wins the whole pot.
 *
 * The all-time record is kept on the game itself, so it survives season resets.
 */
class jackpot
{
	protected $db;
	protected $config;
	protected $points;
	protected $log;
	protected $user;
	protected $games_table;

	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, points $points, \phpbb\log\log_interface $log, \phpbb\user $user, $games_table)
	{
		$this->db = $db;
		$this->config = $config;
		$this->points = $points;
		$this->log = $log;
		$this->user = $user;
		$this->games_table = $games_table;
	}

	/**
	 * Switched on and Ultimate Points active.
	 */
	public function active()
	{
		return $this->points->active() && !empty($this->config['aw_arcade_jackpot_enable']);
	}

	/**
	 * Share of the play cost that goes into the pot of this game (0-100 %).
	 */
	public function share(array $game)
	{
		return min(100, $this->points->value($game, 'jackpot'));
	}

	/**
	 * Does this game have a jackpot at all? (needs a cost and a share)
	 */
	public function applies(array $game)
	{
		return $this->active()
			&& (int) $game['game_save_type'] !== games::SAVE_NONE
			&& $this->points->value($game, 'cost') > 0
			&& $this->share($game) > 0;
	}

	public function pot(array $game)
	{
		return isset($game['game_jackpot_pot']) ? max(0, (float) $game['game_jackpot_pot']) : 0.0;
	}

	/**
	 * A member paid for a game: put the share into its pot.
	 *
	 * @return float amount added
	 */
	public function on_play(array $game, $cost)
	{
		if (!$this->applies($game) || $cost <= 0)
		{
			return 0.0;
		}

		$amount = round((float) $cost * $this->share($game) / 100, 2);

		if ($amount > 0)
		{
			$this->db->sql_query('UPDATE ' . $this->games_table . '
				SET game_jackpot_pot = game_jackpot_pot + ' . (float) $amount . '
				WHERE game_id = ' . (int) $game['game_id']);
		}

		return $amount;
	}

	/**
	 * A genuine result was saved: keep the all-time record up to date and pay
	 * the pot if the record was beaten.
	 *
	 * @return float amount won (0 if none)
	 */
	public function on_score(array $game, $user_id, $score)
	{
		$score = round((float) $score, 2);

		// Read the stored record fresh: the game row of the caller may be older.
		$result = $this->db->sql_query('SELECT game_record, game_record_set, game_jackpot_pot
			FROM ' . $this->games_table . ' WHERE game_id = ' . (int) $game['game_id']);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$row || $score <= 0)
		{
			return 0.0;
		}

		$low = (int) $game['game_scoretype'] === games::SCORE_LOW;
		$had_record = !empty($row['game_record_set']);
		$old = (float) $row['game_record'];

		if ($had_record && !($low ? $score < $old : $score > $old))
		{
			return 0.0;
		}

		// Only the request that really moves the record on may pay.
		$this->db->sql_query('UPDATE ' . $this->games_table . '
			SET game_record = ' . (float) $score . ', game_record_set = 1
			WHERE game_id = ' . (int) $game['game_id'] . '
				AND game_record_set = ' . ($had_record ? 1 : 0) . '
				AND game_record = ' . (float) $old);

		if (!$this->db->sql_affectedrows() || !$had_record)
		{
			// The very first result sets the record; there was nothing to beat yet.
			return 0.0;
		}

		$pot = round((float) $row['game_jackpot_pot'], 2);

		if ($pot <= 0 || !$this->active())
		{
			return 0.0;
		}

		$this->db->sql_query('UPDATE ' . $this->games_table . '
			SET game_jackpot_pot = 0
			WHERE game_id = ' . (int) $game['game_id'] . '
				AND game_jackpot_pot = ' . (float) $row['game_jackpot_pot']);

		if (!$this->db->sql_affectedrows())
		{
			return 0.0;
		}

		$this->points->add((int) $user_id, $pot);

		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_AW_ARCADE_JACKPOT_WON', false, [
			$game['game_name'],
			$this->username($user_id),
			$this->points->format($pot),
		]);

		return $pot;
	}

	/**
	 * Highscores of a game were reset in the ACP: start a fresh record (the pot stays).
	 */
	public function reset_record($game_id)
	{
		$this->db->sql_query('UPDATE ' . $this->games_table . '
			SET game_record = 0, game_record_set = 0
			WHERE game_id = ' . (int) $game_id);
	}

	protected function username($user_id)
	{
		$result = $this->db->sql_query('SELECT username FROM ' . USERS_TABLE . ' WHERE user_id = ' . (int) $user_id);
		$name = (string) $this->db->sql_fetchfield('username');
		$this->db->sql_freeresult($result);

		return $name;
	}
}
