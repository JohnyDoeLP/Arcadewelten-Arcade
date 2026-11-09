<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\migrations;

/**
 * "Who is online": per category switch to never show game names.
 */
class v114 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'aw_arcade_cats', 'cat_online_hide');
	}

	public static function depends_on()
	{
		return ['\arcadewelten\arcade\migrations\v113'];
	}

	public function update_schema()
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'aw_arcade_cats' => [
					'cat_online_hide'	=> ['BOOL', 0],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'aw_arcade_cats' => ['cat_online_hide'],
			],
		];
	}

	public function update_data()
	{
		return [
			['config.update', ['aw_arcade_version', '1.1.4']],
		];
	}
}
