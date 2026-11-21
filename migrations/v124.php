<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\migrations;

/**
 * Own ACP page for adding games: the upload and the list of games found move
 * off the games page, which only holds the installed games from now on.
 */
class v124 extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\arcadewelten\arcade\migrations\v123'];
	}

	public function update_data()
	{
		return [
			['module.add', ['acp', 'ACP_AW_ARCADE', [
				'module_basename'	=> '\arcadewelten\arcade\acp\main_module',
				'module_langname'	=> 'ACP_AW_ARCADE_ADD',
				'module_mode'		=> 'add',
				'module_auth'		=> 'ext_arcadewelten/arcade && acl_a_board',
			]]],
			['custom', [[$this, 'sort_module']]],
		];
	}

	/**
	 * The new page is appended at the end of the menu, so it is moved up until
	 * it sits behind "Games". A menu that reads in the order of the work.
	 */
	public function sort_module()
	{
		global $phpbb_container;

		$manager = $phpbb_container->get('module.manager');

		$sql = 'SELECT module_id, module_langname, left_id
			FROM ' . MODULES_TABLE . "
			WHERE module_class = 'acp'
				AND module_basename = '" . $this->db->sql_escape('\\arcadewelten\\arcade\\acp\\main_module') . "'
			ORDER BY left_id ASC";
		$result = $this->db->sql_query($sql);
		$order = [];
		$add = 0;

		while ($row = $this->db->sql_fetchrow($result))
		{
			$order[] = $row['module_langname'];

			if ($row['module_langname'] === 'ACP_AW_ARCADE_ADD')
			{
				$add = (int) $row['module_id'];
			}
		}
		$this->db->sql_freeresult($result);

		if (!$add)
		{
			return;
		}

		// How many places up: everything that sits between "Games" and the end.
		$steps = count($order) - 1 - (int) array_search('ACP_AW_ARCADE_GAMES', $order, true) - 1;

		if ($steps > 0)
		{
			$manager->move_module_by($manager->get_module_row($add, 'acp'), 'acp', 'move_up', $steps);
		}
	}

	public function revert_data()
	{
		return [
			['module.remove', ['acp', 'ACP_AW_ARCADE', 'ACP_AW_ARCADE_ADD']],
		];
	}
}
