<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

$lang = array_merge($lang, [
	'ACP_AW_ARCADE'				=> 'Arcade',
	'ACP_AW_ARCADE_SETTINGS'	=> 'Settings',
	'ACP_AW_ARCADE_GAMES'		=> 'Games',
	'ACP_AW_ARCADE_CATS'		=> 'Categories',
	'ACP_AW_ARCADE_REVIEW'		=> 'Review list',
	'LOG_AW_ARCADE_SEASON_CLOSED'	=> '<strong>Arcade: season closed</strong><br>» %1$s points paid to %2$s members, highscores emptied: %3$s, play counters reset: %4$s',
	'LOG_AW_ARCADE_DEBUG'			=> '<strong>Arcade diagnostics:</strong> %1$s by %2$s<br>» %3$s',
]);
