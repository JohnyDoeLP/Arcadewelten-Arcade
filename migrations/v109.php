<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\migrations;

/**
 * Category visibility per user group and the permission "Can use the arcade".
 */
class v109 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'aw_arcade_cat_groups');
	}

	public static function depends_on()
	{
		return ['\arcadewelten\arcade\migrations\v108'];
	}

	public function update_schema()
	{
		return [
			'add_tables' => [
				$this->table_prefix . 'aw_arcade_cat_groups' => [
					'COLUMNS' => [
						'cat_id'	=> ['UINT', 0],
						'group_id'	=> ['UINT', 0],
					],
					'PRIMARY_KEY' => ['cat_id', 'group_id'],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'aw_arcade_cat_groups',
			],
		];
	}

	public function update_data()
	{
		$data = [
			['permission.add', ['u_aw_arcade_play']],
		];

		// Same behaviour as before for everybody: granted in the usual user roles ...
		foreach (['ROLE_USER_FULL', 'ROLE_USER_STANDARD', 'ROLE_USER_LIMITED', 'ROLE_USER_NOPM', 'ROLE_USER_NOAVATAR'] as $role)
		{
			$data[] = ['if', [
				['permission.role_exists', [$role]],
				['permission.permission_set', [$role, 'u_aw_arcade_play']],
			]];
		}

		// ... and for guests and registered users (added to their role if they use one).
		foreach (['GUESTS', 'REGISTERED', 'REGISTERED_COPPA'] as $group)
		{
			$data[] = ['permission.permission_set', [$group, 'u_aw_arcade_play', 'group']];
		}

		$data[] = ['config.update', ['aw_arcade_version', '1.0.9']];

		return $data;
	}
}
