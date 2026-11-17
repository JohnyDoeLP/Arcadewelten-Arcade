<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\migrations;

/**
 * Notification when somebody beats a player's record.
 */
class v122 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['aw_arcade_record_notify']);
	}

	public static function depends_on()
	{
		return ['\arcadewelten\arcade\migrations\v121'];
	}

	public function update_data()
	{
		return [
			['config.add', ['aw_arcade_record_notify', 1]],
			['config.update', ['aw_arcade_version', '1.2.2']],
		];
	}
}
