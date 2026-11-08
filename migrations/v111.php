<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\migrations;

/**
 * "NEW" badge for recently installed games and duels between members.
 */
class v111 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'aw_arcade_duels');
	}

	public static function depends_on()
	{
		return ['\arcadewelten\arcade\migrations\v110'];
	}

	public function update_schema()
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'aw_arcade_games' => [
					// Set by the ACP bulk action "remove new mark".
					'game_not_new'	=> ['BOOL', 0],
				],
			],
			'add_tables' => [
				$this->table_prefix . 'aw_arcade_duels' => [
					'COLUMNS' => [
						'duel_id'			=> ['UINT', null, 'auto_increment'],
						'game_id'			=> ['UINT', 0],
						'challenger_id'		=> ['UINT', 0],
						'opponent_id'		=> ['UINT', 0],
						// Score of the challenger, frozen when the duel starts.
						'challenger_score'	=> ['DECIMAL:20', 0],
						// Best score of the opponent during the duel.
						'opponent_score'	=> ['DECIMAL:20', 0],
						'opponent_played'	=> ['BOOL', 0],
						'duel_status'		=> ['TINT:1', 0],
						'winner_id'			=> ['UINT', 0],
						'start_time'		=> ['TIMESTAMP', 0],
						'deadline'			=> ['TIMESTAMP', 0],
						'end_time'			=> ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY' => 'duel_id',
					'KEYS' => [
						'opponent'		=> ['INDEX', ['opponent_id', 'duel_status']],
						'challenger'	=> ['INDEX', ['challenger_id', 'duel_status']],
						'status'		=> ['INDEX', ['duel_status', 'deadline']],
					],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'aw_arcade_games'	=> ['game_not_new'],
			],
			'drop_tables' => [
				$this->table_prefix . 'aw_arcade_duels',
			],
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['aw_arcade_new_days', 14]],
			['config.add', ['aw_arcade_duels', 1]],
			['config.add', ['aw_arcade_duel_days', 7]],
			['config.add', ['aw_arcade_duel_max', 5]],
			['config.add', ['aw_arcade_duel_cron_last', 0, true]],
			['config.update', ['aw_arcade_version', '1.1.1']],
		];
	}
}
