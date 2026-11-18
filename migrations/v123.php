<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\migrations;

/**
 * Categories that are only visible in a certain period (Christmas, Easter, …).
 */
class v123 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'aw_arcade_cats', 'cat_show_mode');
	}

	public static function depends_on()
	{
		return ['\arcadewelten\arcade\migrations\v122'];
	}

	public function update_schema()
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'aw_arcade_cats' => [
					// 0 = always, 1 = once (timestamps), 2 = every year (MMDD)
					'cat_show_mode'	=> ['TINT:2', 0],
					'cat_show_from'	=> ['UINT:11', 0],
					'cat_show_to'	=> ['UINT:11', 0],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'aw_arcade_cats' => ['cat_show_mode', 'cat_show_from', 'cat_show_to'],
			],
		];
	}

	public function update_data()
	{
		return [
			['config.update', ['aw_arcade_version', '1.2.3']],
		];
	}
}
