<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\migrations;

/**
 * How often a game has been handed out.
 *
 * A plain count per game, and nothing else: it answers which games anybody
 * wants, without keeping a record of who took what. Who downloaded which
 * game is not stored anywhere by this arcade.
 */
class v130 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'aw_arcade_games', 'game_downloads');
	}

	public static function depends_on()
	{
		return ['\arcadewelten\arcade\migrations\v129'];
	}

	public function update_schema()
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'aw_arcade_games' => [
					'game_downloads' => ['UINT', 0],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'aw_arcade_games' => ['game_downloads'],
			],
		];
	}

	public function update_data()
	{
		return [
			['config.update', ['aw_arcade_version', '1.3.0']],
		];
	}
}
