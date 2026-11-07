<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\migrations;

/**
 * Touch/mobile flag per game and "recently played" list.
 */
class v108 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'aw_arcade_recent');
	}

	public static function depends_on()
	{
		return ['\arcadewelten\arcade\migrations\v107'];
	}

	public function update_schema()
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'aw_arcade_games' => [
					'game_touch'	=> ['BOOL', 0],
				],
			],
			'add_tables' => [
				$this->table_prefix . 'aw_arcade_recent' => [
					'COLUMNS' => [
						'user_id'		=> ['UINT', 0],
						'game_id'		=> ['UINT', 0],
						'play_time'		=> ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY' => ['user_id', 'game_id'],
					'KEYS' => [
						'game'	=> ['INDEX', 'game_id'],
					],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'aw_arcade_games'	=> ['game_touch'],
			],
			'drop_tables' => [
				$this->table_prefix . 'aw_arcade_recent',
			],
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['aw_arcade_recent', 1]],
			['config.update', ['aw_arcade_version', '1.0.8']],
		];
	}
}
