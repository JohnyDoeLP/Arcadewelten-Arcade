<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\migrations;

/**
 * Search engines may use the arcade again (as before 0.14): they see what guests see.
 */
class v110 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['aw_arcade_version']) && phpbb_version_compare($this->config['aw_arcade_version'], '1.1.0', '>=');
	}

	public static function depends_on()
	{
		return ['\arcadewelten\arcade\migrations\v109'];
	}

	public function update_data()
	{
		return [
			['permission.permission_set', ['BOTS', 'u_aw_arcade_play', 'group']],
			['config.update', ['aw_arcade_version', '1.1.0']],
		];
	}
}
