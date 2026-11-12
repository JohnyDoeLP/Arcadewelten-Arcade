<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\migrations;

/**
 * Jackpot: share of the play costs goes into a pot per game; a new all-time record wins it.
 */
class v118 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'aw_arcade_games', 'game_jackpot_pot');
	}

	public static function depends_on()
	{
		return ['\arcadewelten\arcade\migrations\v117'];
	}

	public function update_schema()
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'aw_arcade_games' => [
					// Share of the play cost in percent (-1 = from category or global setting).
					'game_jackpot'		=> ['DECIMAL:20', -1],
					'game_jackpot_pot'	=> ['DECIMAL:20', 0],
					// All-time record of the game, independent of season resets.
					'game_record'		=> ['DECIMAL:20', 0],
					'game_record_set'	=> ['BOOL', 0],
				],
				$this->table_prefix . 'aw_arcade_cats' => [
					'cat_jackpot'		=> ['DECIMAL:20', -1],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'aw_arcade_games'	=> ['game_jackpot', 'game_jackpot_pot', 'game_record', 'game_record_set'],
				$this->table_prefix . 'aw_arcade_cats'	=> ['cat_jackpot'],
			],
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['aw_arcade_jackpot_enable', 0]],
			['config.add', ['aw_arcade_jackpot', 50]],
			['config.update', ['aw_arcade_version', '1.1.8']],
			['custom', [[$this, 'init_records']]],
		];
	}

	/**
	 * The current best score of every game becomes its all-time record.
	 */
	public function init_records()
	{
		$games = $this->table_prefix . 'aw_arcade_games';
		$scores = $this->table_prefix . 'aw_arcade_scores';

		$result = $this->db->sql_query('SELECT g.game_id, g.game_scoretype, MAX(s.score) AS hi, MIN(s.score) AS lo
			FROM ' . $games . ' g
			JOIN ' . $scores . ' s ON s.game_id = g.game_id
			GROUP BY g.game_id, g.game_scoretype');

		while ($row = $this->db->sql_fetchrow($result))
		{
			// 2 = lowest score wins (games::SCORE_LOW)
			$record = (int) $row['game_scoretype'] === 2 ? (float) $row['lo'] : (float) $row['hi'];

			$this->db->sql_query('UPDATE ' . $games . '
				SET game_record = ' . (float) $record . ', game_record_set = 1
				WHERE game_id = ' . (int) $row['game_id']);
		}
		$this->db->sql_freeresult($result);
	}
}
