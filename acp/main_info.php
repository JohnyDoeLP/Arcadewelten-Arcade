<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\acp;

class main_info
{
	public function module()
	{
		return [
			'filename'	=> '\arcadewelten\arcade\acp\main_module',
			'title'		=> 'ACP_AW_ARCADE',
			'modes'		=> [
				'settings'	=> ['title' => 'ACP_AW_ARCADE_SETTINGS', 'auth' => 'ext_arcadewelten/arcade && acl_a_board', 'cat' => ['ACP_AW_ARCADE']],
				'games'		=> ['title' => 'ACP_AW_ARCADE_GAMES', 'auth' => 'ext_arcadewelten/arcade && acl_a_board', 'cat' => ['ACP_AW_ARCADE']],
				'cats'		=> ['title' => 'ACP_AW_ARCADE_CATS', 'auth' => 'ext_arcadewelten/arcade && acl_a_board', 'cat' => ['ACP_AW_ARCADE']],
				'review'	=> ['title' => 'ACP_AW_ARCADE_REVIEW', 'auth' => 'ext_arcadewelten/arcade && acl_a_board', 'cat' => ['ACP_AW_ARCADE']],
			],
		];
	}
}
