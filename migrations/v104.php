<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\migrations;

/**
 * Credit line on arcade pages (can be switched off in the ACP).
 */
class v104 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['aw_arcade_credit']);
	}

	public static function depends_on()
	{
		return ['\arcadewelten\arcade\migrations\v103'];
	}

	public function update_data()
	{
		return [
			['config.add', ['aw_arcade_credit', 1]],
			['config.add', ['aw_arcade_last_cleanup', 0, true]],
			['config.update', ['aw_arcade_version', '1.0.4']],
		];
	}
}
