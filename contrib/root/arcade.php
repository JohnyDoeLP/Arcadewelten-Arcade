<?php
/**
 * Arcadewelten Arcade – forwarder for old arcade links.
 *
 * Old posts still link to arcade.php of the previous arcade (e.g. the JV
 * arcade's player statistics arcade.php?mode=stats&u=2). This file only starts
 * phpBB; the arcade extension then redirects (event core.common): player
 * statistics go to the member's records page, everything else to the arcade.
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Only reached if the arcade extension is disabled.
redirect(append_sid($phpbb_root_path . 'index.' . $phpEx));
