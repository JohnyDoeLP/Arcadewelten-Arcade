<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\migrations;

/**
 * Where a game is served from.
 *
 * Normally that is its own folder under arcade/games/, and the column stays
 * empty. Other arcades pack an HTML5 game the other way round: the game
 * itself, with its index.html, goes under arcade/gamedata/<name>/, and the
 * game folder holds only the installation file and the images. Such a game
 * keeps its entry here and is loaded from the folder named in this column.
 */
class v126 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'aw_arcade_games', 'game_base');
	}

	public static function depends_on()
	{
		return ['\arcadewelten\arcade\migrations\v125'];
	}

	public function update_schema()
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'aw_arcade_games' => [
					// Board-relative web path of the folder, without a trailing
					// slash. Empty means the game's own folder.
					'game_base' => ['VCHAR:255', ''],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'aw_arcade_games' => ['game_base'],
			],
		];
	}

	public function update_data()
	{
		return [
			['config.update', ['aw_arcade_version', '1.2.6']],
		];
	}
}
