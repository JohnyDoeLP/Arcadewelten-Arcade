<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\migrations;

/**
 * Automatic arcade section in the board's privacy policy (on by default).
 */
class v106 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['aw_arcade_privacy']);
	}

	public static function depends_on()
	{
		return ['\arcadewelten\arcade\migrations\v105'];
	}

	public function update_data()
	{
		return [
			['config.add', ['aw_arcade_privacy', 1]],
			['config.update', ['aw_arcade_version', '1.0.6']],
		];
	}
}
