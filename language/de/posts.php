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

// Texts of the forum posts written by the ArcadeBot (board default language).
$lang = array_merge($lang, [
	'AW_ARCADE_POST_GAME_TITLE'		=> '[Spiel installiert] – %s',
	'AW_ARCADE_POST_GAMES_TITLE'	=> '[Spiele installiert] – %d neue Spiele',
	'AW_ARCADE_POST_GAMES_INTRO'	=> 'In der Spielhalle sind %d neue Spiele dazugekommen:',
	'AW_ARCADE_POST_GAME'			=> 'Spiel',
	'AW_ARCADE_POST_CATEGORY'		=> 'Kategorie',
	'AW_ARCADE_POST_DESC'			=> 'Beschreibung',
	'AW_ARCADE_POST_INFO'			=> 'Infos',
	'AW_ARCADE_POST_SIZE'			=> '%s',
	'AW_ARCADE_POST_DIMENSIONS'		=> '%1$d × %2$d px',
	'AW_ARCADE_POST_TOUCH'			=> 'auch auf dem Handy spielbar',
	'AW_ARCADE_POST_NO_SCORES'		=> 'ohne Bestenliste',
	'AW_ARCADE_POST_PLAY'			=> 'Jetzt spielen',
	'AW_ARCADE_POST_SCORES'			=> 'Zur Bestenliste',
	'AW_ARCADE_POST_DISCUSS'		=> 'Tipps, Tricks und Fragen zum Spiel gerne hier im Thema.',
	'AW_ARCADE_POST_DISCUSS_MANY'	=> 'Tipps, Tricks und Fragen zu den Spielen gerne hier im Thema.',
	'AW_ARCADE_POST_SEASON_TITLE'	=> '[Top 10 der Spielhalle] – %s',
	'AW_ARCADE_POST_SEASON_INTRO'	=> 'Die Saison vom %1$s bis %2$s ist beendet. Die besten Spieler nach Anzahl der Rekorde:',
	'AW_ARCADE_POST_RECORDS'		=> [
		1 => '%d Rekord',
		2 => '%d Rekorde',
	],
	'AW_ARCADE_POST_PRIZE'			=> 'Prämie: %1$s %2$s',
	'AW_ARCADE_POST_SEASON_PRIZES'	=> 'Insgesamt wurden %1$s %2$s an %3$d Spieler ausgezahlt.',
	'AW_ARCADE_POST_SEASON_EMPTY'	=> 'In dieser Saison wurden keine Rekorde aufgestellt.',
	'AW_ARCADE_POST_SEASON_NEXT'	=> 'Die neue Saison läuft bereits – viel Glück!',
	'AW_ARCADE_POST_TO_ARCADE'		=> 'Zur Spielhalle',
	'AW_ARCADE_POST_TOURNEY_TITLE'			=> '[Turnier] – %s',
	'AW_ARCADE_POST_TOURNEY_INTRO'			=> 'Ein neues Turnier hat begonnen! Es läuft vom %1$s bis %2$s Uhr.',
	'AW_ARCADE_POST_TOURNEY_GAMES'			=> 'Diese Spiele zählen:',
	'AW_ARCADE_POST_TOURNEY_MORE'			=> [
		1 => '%d weiteres Spiel (nur für berechtigte Mitglieder sichtbar)',
		2 => '%d weitere Spiele (nur für berechtigte Mitglieder sichtbar)',
	],
	'AW_ARCADE_POST_TOURNEY_RULES'			=> 'Pro Spiel zählt dein bestes Ergebnis über 0 im Turnierzeitraum. Die Plätze bringen Turnierpunkte (10, 8, 6, 5, 4, 3, 2, 1), die Summe entscheidet.',
	'AW_ARCADE_POST_TOURNEY_PRIZES'			=> 'Prämien:',
	'AW_ARCADE_POST_TOURNEY_PRIZE_LINE'		=> 'Platz %1$d: %2$s %3$s',
	'AW_ARCADE_POST_TOURNEY_TO'				=> 'Zum Turnier',
	'AW_ARCADE_POST_TOURNEY_GOOD_LUCK'		=> 'viel Glück!',
	'AW_ARCADE_POST_TOURNEY_RESULT_TITLE'	=> '[Turnierergebnis] – %s',
	'AW_ARCADE_POST_TOURNEY_RESULT_INTRO'	=> 'Das Turnier „%s“ ist beendet. Die Gesamtwertung:',
	'AW_ARCADE_POST_TOURNEY_POINTS'			=> [
		1 => '%d Turnierpunkt',
		2 => '%d Turnierpunkte',
	],
	'AW_ARCADE_POST_TOURNEY_EMPTY'			=> 'An diesem Turnier hat niemand teilgenommen.',
	'AW_ARCADE_POST_TOURNEY_FULL'			=> 'Zur vollständigen Wertung',
	'AW_ARCADE_POST_TOURNEY_CHANGE'			=> 'Änderung am Turnier:',
	'AW_ARCADE_POST_TOURNEY_CHANGE_END'		=> 'Das Turnier endet jetzt am %1$s Uhr (bisher %2$s Uhr).',
	'AW_ARCADE_POST_TOURNEY_CHANGE_ADDED'	=> 'Neu dabei: %s',
	'AW_ARCADE_POST_TOURNEY_CHANGE_REMOVED'	=> 'Nicht mehr dabei: %s (bisherige Ergebnisse darin zählen nicht mehr)',
	'AW_ARCADE_POST_TOURNEY_CHANGE_PRIZES'	=> 'Neue Prämien: %s',
	'AW_ARCADE_POST_TOURNEY_CHANGE_NO_PRIZES'	=> 'Es gibt keine Prämien mehr.',
	'AW_ARCADE_POST_TOURNEY_CLOSED'			=> 'Das Turnier ist beendet – vielen Dank fürs Mitspielen!',
	'AW_ARCADE_POST_TOURNEY_CLOSED_LINK'	=> 'Zum Turnierergebnis',
]);
