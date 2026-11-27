<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\migrations;

/**
 * Handing a game out as a ZIP.
 *
 * Off until a board switches it on: most of these games are somebody else's
 * work, and offering them for download is a different matter from having them
 * playable. The permission is granted to members from the start so that the
 * one switch decides, and a board that wants to open it to fewer people can
 * take it away again.
 */
class v127 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['aw_arcade_download']);
	}

	public static function depends_on()
	{
		return ['\arcadewelten\arcade\migrations\v126'];
	}

	public function update_data()
	{
		$data = [
			['config.add', ['aw_arcade_download', 0]],
			['permission.add', ['u_aw_arcade_download']],
		];

		foreach (['ROLE_USER_FULL', 'ROLE_USER_STANDARD', 'ROLE_USER_LIMITED', 'ROLE_USER_NOPM', 'ROLE_USER_NOAVATAR'] as $role)
		{
			$data[] = ['if', [
				['permission.role_exists', [$role]],
				['permission.permission_set', [$role, 'u_aw_arcade_download']],
			]];
		}

		// Members, not guests: a guest who may play has no reason to carry the
		// files away, and a board that wants that can still grant it.
		foreach (['REGISTERED', 'REGISTERED_COPPA'] as $group)
		{
			$data[] = ['permission.permission_set', [$group, 'u_aw_arcade_download', 'group']];
		}

		$data[] = ['config.update', ['aw_arcade_version', '1.2.7']];

		return $data;
	}

	public function revert_data()
	{
		return [
			['permission.remove', ['u_aw_arcade_download']],
		];
	}
}
