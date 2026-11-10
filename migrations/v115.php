<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\migrations;

/**
 * Tournaments: several games, one period, an overall ranking and prizes.
 */
class v115 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'aw_arcade_tourneys');
	}

	public static function depends_on()
	{
		return ['\arcadewelten\arcade\migrations\v114'];
	}

	public function update_schema()
	{
		return [
			'add_tables' => [
				$this->table_prefix . 'aw_arcade_tourneys' => [
					'COLUMNS' => [
						'tourney_id'		=> ['UINT', null, 'auto_increment'],
						'tourney_name'		=> ['VCHAR_UNI:255', ''],
						'tourney_desc'		=> ['TEXT_UNI', ''],
						'tourney_start'		=> ['TIMESTAMP', 0],
						'tourney_end'		=> ['TIMESTAMP', 0],
						'tourney_prize_1'	=> ['DECIMAL:20', 0],
						'tourney_prize_2'	=> ['DECIMAL:20', 0],
						'tourney_prize_3'	=> ['DECIMAL:20', 0],
						'tourney_announced'	=> ['BOOL', 0],
						'tourney_finished'	=> ['BOOL', 0],
					],
					'PRIMARY_KEY' => 'tourney_id',
					'KEYS' => [
						'period'	=> ['INDEX', ['tourney_finished', 'tourney_end']],
					],
				],
				$this->table_prefix . 'aw_arcade_tourney_games' => [
					'COLUMNS' => [
						'tourney_id'	=> ['UINT', 0],
						'game_id'		=> ['UINT', 0],
					],
					'PRIMARY_KEY' => ['tourney_id', 'game_id'],
					'KEYS' => [
						'game_id'	=> ['INDEX', 'game_id'],
					],
				],
				$this->table_prefix . 'aw_arcade_tourney_scores' => [
					'COLUMNS' => [
						'tourney_id'	=> ['UINT', 0],
						'game_id'		=> ['UINT', 0],
						'user_id'		=> ['ULINT', 0],
						'score'			=> ['DECIMAL:20', 0],
						'score_time'	=> ['TIMESTAMP', 0],
						'score_plays'	=> ['UINT', 0],
					],
					'PRIMARY_KEY' => ['tourney_id', 'game_id', 'user_id'],
					'KEYS' => [
						'user_id'	=> ['INDEX', 'user_id'],
					],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'aw_arcade_tourneys',
				$this->table_prefix . 'aw_arcade_tourney_games',
				$this->table_prefix . 'aw_arcade_tourney_scores',
			],
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['aw_arcade_tourneys', 1]],
			['config.add', ['aw_arcade_post_tourneys', 1]],
			['config.add', ['aw_arcade_tourney_cron_last', 0, true]],
			['config.update', ['aw_arcade_version', '1.1.5']],
			['module.add', ['acp', 'ACP_AW_ARCADE', [
				'module_basename'	=> '\arcadewelten\arcade\acp\main_module',
				'module_langname'	=> 'ACP_AW_ARCADE_TOURNEYS',
				'module_mode'		=> 'tourneys',
				'module_auth'		=> 'ext_arcadewelten/arcade && acl_a_board',
			]]],
		];
	}
}
