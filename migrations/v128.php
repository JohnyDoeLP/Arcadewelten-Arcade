<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\migrations;

/**
 * Which games may be handed out.
 *
 * The switch in the settings turns the button on at all; this says for which
 * games it appears. A board usually has a few games of its own it is happy to
 * pass on, and a great many that came from elsewhere. New games start at no,
 * so switching the button on gives nothing away by itself.
 */
class v128 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'aw_arcade_games', 'game_download');
	}

	public static function depends_on()
	{
		return ['\arcadewelten\arcade\migrations\v127'];
	}

	public function update_schema()
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'aw_arcade_games' => [
					'game_download' => ['BOOL', 0],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'aw_arcade_games' => ['game_download'],
			],
		];
	}

	public function update_data()
	{
		return [
			['config.update', ['aw_arcade_version', '1.2.8']],
		];
	}
}
