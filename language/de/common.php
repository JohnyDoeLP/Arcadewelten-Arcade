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
	'AW_ARCADE'					=> 'Spielhalle',
	'AW_ARCADE_ALL_GAMES'		=> 'Alle Spiele',
	'AW_ARCADE_SEARCH'			=> 'Spiel suchen …',
	'AW_ARCADE_GAME_COUNT'		=> [
		0 => 'Keine Spiele',
		1 => '%d Spiel',
		2 => '%d Spiele',
	],
	'AW_ARCADE_PLAYS'			=> [
		0 => 'noch nie gespielt',
		1 => '%d-mal gespielt',
		2 => '%d-mal gespielt',
	],
	'AW_ARCADE_PLAYERS'			=> [
		0 => 'noch keine Spieler',
		1 => '%d Spieler',
		2 => '%d Spieler',
	],
	'AW_ARCADE_NO_GAMES'		=> 'Hier gibt es noch keine Spiele.',
	'AW_ARCADE_NO_CHAMPION'		=> 'Noch kein Champion',
	'AW_ARCADE_NO_SCORES'		=> 'Ohne Punktewertung',
	'AW_ARCADE_HIGHSCORES'		=> 'Bestenliste',
	'AW_ARCADE_NO_HIGHSCORES'	=> 'Noch keine Punkte eingetragen. Sei die oder der Erste!',
	'AW_ARCADE_TOP10'			=> 'Top 10',
	'AW_ARCADE_SCORE'			=> 'Punkte',
	'AW_ARCADE_TRIES'			=> 'Versuche',
	'AW_ARCADE_DATE'			=> 'Erreicht am',
	'AW_ARCADE_MY_RANK'			=> 'Dein Platz: %d',
	'AW_ARCADE_LOW_WINS'		=> 'Weniger Punkte sind besser',
	'AW_ARCADE_PLAY_NOW'		=> 'Jetzt spielen',
	'AW_ARCADE_DESCRIPTION'		=> 'Beschreibung',
	'AW_ARCADE_FULLSCREEN'		=> 'Vollbild',
	'AW_ARCADE_NEED_JS'			=> 'Zum Spielen muss JavaScript aktiviert sein.',
	'AW_ARCADE_RUFFLE_MISSING'	=> 'Der Flash-Emulator konnte nicht geladen werden.',
	'AW_ARCADE_GUEST_HINT'		=> 'Du spielst als Gast. Melde dich an, damit deine Punkte gespeichert werden.',
	'AW_ARCADE_LOGIN_TO_PLAY'	=> 'Dieses Spiel ist nur für angemeldete Mitglieder. Bitte melde dich an.',

	'AW_ARCADE_MSG_GUEST'		=> 'Als Gast werden keine Punkte gespeichert. Melde dich an, um in die Bestenlisten zu kommen.',
	'AW_ARCADE_GUEST_TITLE'		=> 'Du bist nicht angemeldet.',
	'AW_ARCADE_GUEST_LIMITED'	=> [
		0 => 'Als Gast kannst du nur die freigegebenen Spiele ausprobieren. Melde dich an oder registriere dich, um alle Spiele zu spielen und in die Bestenlisten zu kommen.',
		1 => 'Als Gast kannst du nur die freigegebenen Spiele ausprobieren. Melde dich an oder registriere dich, um alle %d Spiele zu spielen und in die Bestenlisten zu kommen.',
		2 => 'Als Gast kannst du nur die freigegebenen Spiele ausprobieren. Melde dich an oder registriere dich, um alle %d Spiele zu spielen und in die Bestenlisten zu kommen.',
	],
	'AW_ARCADE_GUEST_BLOCKED'	=> 'Die Spielhalle steht nur angemeldeten Mitgliedern zur Verfügung. Melde dich an oder registriere dich, um zu spielen.',
	'AW_ARCADE_GUEST_RELEASED'	=> 'Für Gäste freigegeben',
	'AW_ARCADE_LOGIN_FOR_CAT'	=> 'Diese Kategorie ist nur für angemeldete Mitglieder sichtbar.',
	'AW_ARCADE_LOGIN_FOR_SCORES'	=> 'Die Bestenlisten sind nur für angemeldete Mitglieder sichtbar.',
	'AW_ARCADE_MSG_NOSESSION'	=> 'Deine Punkte konnten nicht gespeichert werden, weil die Spielsitzung abgelaufen oder ungültig war. Bitte starte das Spiel erneut über die Spielhalle.',
	'AW_ARCADE_MSG_SAVED'		=> 'Gespeichert: %s Punkte. Deinen persönlichen Rekord hast du diesmal nicht geknackt.',
	'AW_ARCADE_MSG_NEW_BEST'	=> 'Neuer persönlicher Rekord: %s Punkte!',

	'AW_ARCADE_PASSWORD_EXPLAIN'	=> 'Diese Kategorie ist mit einem Passwort geschützt.',
	'AW_ARCADE_WRONG_PASSWORD'		=> 'Das Passwort ist falsch.',
	'AW_ARCADE_UNLOCK'				=> 'Freischalten',
	'AW_ARCADE_NO_GAME'				=> 'Dieses Spiel gibt es nicht oder es ist deaktiviert.',
	'AW_ARCADE_NO_CAT'				=> 'Diese Kategorie gibt es nicht.',

	'AW_ARCADE_HALL_OF_FAME'	=> 'Ruhmeshalle – die meisten Rekorde',
	'AW_ARCADE_RECORDS'			=> [
		0 => 'keine Rekorde',
		1 => '%d Rekord',
		2 => '%d Rekorde',
	],
	'AW_ARCADE_RECORDS_LABEL'	=> 'Spielhallen-Rekorde',
	'AW_ARCADE_RECORDS_TITLE'	=> 'Rekorde',
	'AW_ARCADE_RECORDS_OF'		=> 'Rekorde von %s',
	'AW_ARCADE_NO_RECORDS'		=> 'Dieses Mitglied hält zurzeit keinen Rekord.',
	'AW_ARCADE_LATEST_RECORDS'	=> 'Neueste Rekorde in der Spielhalle',
	'AW_ARCADE_GAME'			=> 'Spiel',

	'AW_ARCADE_GOLD'			=> 'Gold',
	'AW_ARCADE_SILVER'			=> 'Silber',
	'AW_ARCADE_BRONZE'			=> 'Bronze',

	'AW_ARCADE_NOT_ENOUGH_POINTS'	=> 'Dieses Spiel kostet %1$s %2$s, du hast aber nur %3$s %2$s.',
	'AW_ARCADE_PAY_EXPLAIN'		=> 'Eine Runde kostet %1$s %2$s.',
	'AW_ARCADE_YOUR_BALANCE'	=> 'Dein Guthaben: %1$s %2$s',
	'AW_ARCADE_PAY_AND_PLAY'	=> 'Für %1$s %2$s spielen',
	'AW_ARCADE_INFO_COST'		=> 'Kosten: %1$s %2$s',
	'AW_ARCADE_INFO_ROUND'		=> 'pro Runde mit Punkten: +%1$s %2$s',
	'AW_ARCADE_INFO_SEASON'		=> 'Saisonprämie Platz 1/2/3: %1$s / %2$s / %3$s %4$s',
	'AW_ARCADE_MSG_EARNED'		=> 'Dafür bekommst du %1$s %2$s.',

	'AW_ARCADE_CREDIT'			=> 'Spielhalle: %s',

	'AW_ARCADE_CATEGORIES'		=> 'Kategorien',

	'AW_ARCADE_MSG_FLAGGED'		=> 'Dein Ergebnis ist ungewöhnlich und wird von einem Administrator geprüft, bevor es in der Bestenliste erscheint.',

	'AW_ARCADE_DEC_POINT'		=> ',',
	'AW_ARCADE_THOUSANDS_SEP'	=> '.',
]);
