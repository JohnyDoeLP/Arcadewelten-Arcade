<?php
/**
 * Arcadewelten Arcade – score entry point for Activity Mod games.
 *
 * Old Activity Mod games post their score (game_name, score) to newscore.php
 * in the board root. This file only starts phpBB; the arcade extension then
 * handles the request (event core.common) and sends the player to the
 * highscores. No redirect is involved, so the POST data cannot get lost.
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
