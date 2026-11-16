<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\migrations;

/**
 * Arcade block on the board index.
 */
class v120 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['aw_arcade_index_block']);
	}

	public static function depends_on()
	{
		return ['\arcadewelten\arcade\migrations\v119'];
	}

	public function update_data()
	{
		return [
			['config.add', ['aw_arcade_index_block', 1]],
			['config.add', ['aw_arcade_index_games', 6]],
			['config.add', ['aw_arcade_index_cats', 1]],
			['config.update', ['aw_arcade_version', '1.2.0']],
		];
	}
}
