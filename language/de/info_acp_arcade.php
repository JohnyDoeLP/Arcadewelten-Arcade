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
	'ACP_AW_ARCADE'				=> 'Spielhalle',
	'ACP_AW_ARCADE_SETTINGS'	=> 'Einstellungen',
	'ACP_AW_ARCADE_GAMES'		=> 'Spiele',
	'ACP_AW_ARCADE_CATS'		=> 'Kategorien',
	'ACP_AW_ARCADE_REVIEW'		=> 'Prüfliste',
	'LOG_AW_ARCADE_SEASON_CLOSED'	=> '<strong>Spielhalle: Saison abgeschlossen</strong><br>» %1$s Punkte an %2$s Mitglieder, Bestenlisten geleert: %3$s, Spielezähler zurückgesetzt: %4$s',
	'LOG_AW_ARCADE_DEBUG'			=> '<strong>Spielhalle-Diagnose:</strong> %1$s von %2$s<br>» %3$s',
]);
