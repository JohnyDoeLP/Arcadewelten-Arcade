<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\migrations;

/**
 * Category images, plausibility checks and review list.
 */
class v105 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'aw_arcade_flagged');
	}

	public static function depends_on()
	{
		return ['\arcadewelten\arcade\migrations\v104'];
	}

	public function update_schema()
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'aw_arcade_cats' => [
					'cat_image'		=> ['VCHAR:255', ''],
				],
				$this->table_prefix . 'aw_arcade_games' => [
					'game_min_time'		=> ['UINT', 0],
					'game_max_score'	=> ['DECIMAL:20', 0],
				],
			],
			'add_tables' => [
				$this->table_prefix . 'aw_arcade_flagged' => [
					'COLUMNS' => [
						'flag_id'		=> ['UINT', null, 'auto_increment'],
						'game_id'		=> ['UINT', 0],
						'user_id'		=> ['UINT', 0],
						'score'			=> ['DECIMAL:20', 0],
						'play_seconds'	=> ['UINT', 0],
						'flag_reason'	=> ['VCHAR:50', ''],
						'flag_time'		=> ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY'	=> 'flag_id',
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'aw_arcade_cats'	=> ['cat_image'],
				$this->table_prefix . 'aw_arcade_games'	=> ['game_min_time', 'game_max_score'],
			],
			'drop_tables' => [
				$this->table_prefix . 'aw_arcade_flagged',
			],
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['aw_arcade_min_time', 5]],
			['config.update', ['aw_arcade_version', '1.0.5']],
			// Full module data instead of 'modes': does not depend on a cached main_info.
			['module.add', ['acp', 'ACP_AW_ARCADE', [
				'module_basename'	=> '\arcadewelten\arcade\acp\main_module',
				'module_langname'	=> 'ACP_AW_ARCADE_REVIEW',
				'module_mode'		=> 'review',
				'module_auth'		=> 'ext_arcadewelten/arcade && acl_a_board',
			]]],
		];
	}
}
