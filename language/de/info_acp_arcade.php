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
	'ACP_AW_ARCADE_TOURNEYS'		=> 'Turniere',
	'LOG_AW_ARCADE_TOURNEY_ADDED'	=> '<strong>Spielhalle: Turnier angelegt</strong><br>» %s',
	'LOG_AW_ARCADE_TOURNEY_EDITED'	=> '<strong>Spielhalle: Turnier bearbeitet</strong><br>» %s',
	'LOG_AW_ARCADE_TOURNEY_DELETED'	=> '<strong>Spielhalle: Turnier gelöscht</strong><br>» %s',
	'LOG_AW_ARCADE_TOURNEY_FINISHED'	=> '<strong>Spielhalle: Turnier beendet</strong><br>» %1$s – %2$s Teilnehmer, %3$s Punkte ausgezahlt',
	'LOG_AW_ARCADE_JACKPOT_WON'		=> '<strong>Spielhalle: Jackpot gewonnen</strong><br>» %1$s – %2$s, %3$s Punkte',
	'LOG_AW_ARCADE_DEBUG'			=> '<strong>Spielhalle-Diagnose:</strong> %1$s von %2$s<br>» %3$s',
]);
