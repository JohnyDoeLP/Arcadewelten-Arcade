<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\migrations;

/**
 * Favourite games and game reports.
 */
class v113 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'aw_arcade_favorites');
	}

	public static function depends_on()
	{
		return ['\arcadewelten\arcade\migrations\v112'];
	}

	public function update_schema()
	{
		return [
			'add_tables' => [
				$this->table_prefix . 'aw_arcade_favorites' => [
					'COLUMNS' => [
						'user_id'	=> ['UINT', 0],
						'game_id'	=> ['UINT', 0],
						'fav_time'	=> ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY' => ['user_id', 'game_id'],
					'KEYS' => [
						'game'	=> ['INDEX', 'game_id'],
					],
				],
				$this->table_prefix . 'aw_arcade_reports' => [
					'COLUMNS' => [
						'report_id'		=> ['UINT', null, 'auto_increment'],
						'game_id'		=> ['UINT', 0],
						'user_id'		=> ['UINT', 0],
						'report_reason'	=> ['VCHAR:20', ''],
						'report_text'	=> ['VCHAR_UNI', ''],
						'report_time'	=> ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY' => 'report_id',
					'KEYS' => [
						'game_user'	=> ['INDEX', ['game_id', 'user_id']],
					],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'aw_arcade_favorites',
				$this->table_prefix . 'aw_arcade_reports',
			],
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['aw_arcade_favorites', 1]],
			['config.add', ['aw_arcade_reports', 1]],
			['config.add', ['aw_arcade_medals', 1]],
			['config.update', ['aw_arcade_version', '1.1.3']],
		];
	}
}
