<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\migrations;

/**
 * Drop-down menu under "Arcade" in the header, with "random game".
 */
class v117 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['aw_arcade_nav_menu']);
	}

	public static function depends_on()
	{
		return ['\arcadewelten\arcade\migrations\v116'];
	}

	public function update_data()
	{
		return [
			['config.add', ['aw_arcade_nav_menu', 1]],
			['config.add', ['aw_arcade_nav_random', 1]],
			['config.update', ['aw_arcade_version', '1.1.7']],
		];
	}
}
