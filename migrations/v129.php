<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\migrations;

/**
 * The help page for the arcade.
 */
class v129 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['aw_arcade_help']);
	}

	public static function depends_on()
	{
		return ['\arcadewelten\arcade\migrations\v128'];
	}

	public function update_data()
	{
		return [
			['config.add', ['aw_arcade_help', 1]],
			['config.update', ['aw_arcade_version', '1.2.9']],
		];
	}
}
