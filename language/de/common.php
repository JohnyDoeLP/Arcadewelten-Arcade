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

	'AW_ARCADE_RATING'			=> 'Bewertung',
	'AW_ARCADE_NOT_RATED'		=> 'Noch nicht bewertet',
	'AW_ARCADE_RATING_SUMMARY'	=> [
		1 => '%1$s von 5 (%2$d Bewertung)',
		2 => '%1$s von 5 (%2$d Bewertungen)',
	],
	'AW_ARCADE_YOUR_RATING'		=> 'Deine Bewertung',
	'AW_ARCADE_RATE_N'			=> [
		1 => '%d Stern',
		2 => '%d Sterne',
	],
	'AW_ARCADE_RATE_PLAY_FIRST'	=> 'Spiele das Spiel, um es bewerten zu können.',
	'AW_ARCADE_RATE_LOGIN'		=> 'Nur angemeldete Mitglieder können Spiele bewerten.',
	'AW_ARCADE_RATE_INVALID'	=> 'Bitte wähle 1 bis 5 Sterne.',
	'AW_ARCADE_MSG_RATED'		=> 'Danke für deine Bewertung!',
	'AW_ARCADE_SORT'			=> 'Sortieren',
	'AW_ARCADE_SORT_NAME'		=> 'Name',
	'AW_ARCADE_SORT_RATING'		=> 'Beste Bewertung',
	'AW_ARCADE_SORT_PLAYS'		=> 'Am meisten gespielt',
	'AW_ARCADE_SORT_NEW'		=> 'Neueste',

	'AW_ARCADE_RECENT'			=> 'Zuletzt gespielt',
	'AW_ARCADE_TOUCH_GAME'		=> 'Auch auf dem Handy spielbar',
	'AW_ARCADE_TOUCH_START'		=> 'Im Vollbild spielen',
	'AW_ARCADE_FULLSCREEN_EXIT'	=> 'Vollbild beenden',
	'AW_ARCADE_NOT_TOUCH_HINT'	=> 'Dieses Spiel braucht Tastatur oder Maus und ist auf Handy und Tablet eventuell nicht spielbar.',
	'AW_ARCADE_LIGHTS_OFF'		=> 'Licht aus',
	'AW_ARCADE_LIGHTS_ON'		=> 'Licht an',

	'AW_ARCADE_NO_PERMISSION'	=> 'Du hast keine Berechtigung, die Spielhalle zu benutzen.',
	'AW_ARCADE_CAT_GROUPS_ONLY'	=> 'Nur für bestimmte Benutzergruppen sichtbar',

	'AW_ARCADE_NEW'				=> 'Neu',
	'AW_ARCADE_NEW_COUNT'		=> [
		1 => '%d neu',
		2 => '%d neu',
	],

	'AW_ARCADE_DUELS'			=> 'Duelle',
	'AW_ARCADE_DUELS_EXPLAIN'	=> 'Fordere andere Mitglieder mit deinem Rekord heraus: Sie müssen ihn bis zum Ablauf schlagen – sonst gewinnst du.',
	'AW_ARCADE_DUELS_INCOMING'	=> 'Herausforderungen an dich',
	'AW_ARCADE_DUELS_OUTGOING'	=> 'Deine Herausforderungen',
	'AW_ARCADE_DUELS_FINISHED'	=> 'Beendete Duelle',
	'AW_ARCADE_DUELS_NONE_INCOMING'	=> 'Gerade fordert dich niemand heraus.',
	'AW_ARCADE_DUELS_NONE_OUTGOING'	=> 'Du hast gerade niemanden herausgefordert. Das geht auf der Bestenliste eines Spiels, in dem du einen Rekord hast.',
	'AW_ARCADE_DUELS_NONE_FINISHED'	=> 'Noch keine beendeten Duelle.',
	'AW_ARCADE_DUELS_MINE'		=> 'Meine Duelle',
	'AW_ARCADE_DUEL_FROM'		=> 'Herausforderer',
	'AW_ARCADE_DUEL_AGAINST'	=> 'Gegner',
	'AW_ARCADE_DUEL_TO_BEAT'	=> 'Zu schlagen',
	'AW_ARCADE_DUEL_OPP_SCORE'	=> 'Bestes Ergebnis Gegner',
	'AW_ARCADE_DUEL_DEADLINE'	=> 'Läuft bis',
	'AW_ARCADE_DUEL_ENDED'		=> 'Beendet',
	'AW_ARCADE_DUEL_RESULT'		=> 'Ergebnis',
	'AW_ARCADE_DUEL_WON'		=> 'Gewonnen',
	'AW_ARCADE_DUEL_LOST'		=> 'Verloren',
	'AW_ARCADE_DUEL_DECLINED'	=> 'Abgelehnt',
	'AW_ARCADE_DUEL_DECLINE'	=> 'Ablehnen',
	'AW_ARCADE_DUEL_DECLINE_CONFIRM'	=> 'Diese Herausforderung wirklich ablehnen?',
	'AW_ARCADE_DUEL_CHALLENGE'	=> 'Zum Duell herausfordern',
	'AW_ARCADE_DUEL_FORM_EXPLAIN'	=> 'Dein Rekord von <strong>%s</strong> wird vorgelegt. Dein Gegner muss ihn bis zum Ablauf mit einem echten Spiel schlagen – sonst gewinnst du.',
	'AW_ARCADE_DUEL_OPPONENT'	=> 'Gegner',
	'AW_ARCADE_DUEL_DURATION'	=> 'Dauer',
	'AW_ARCADE_DUEL_DAYS_N'		=> [
		1 => '%d Tag',
		2 => '%d Tage',
	],
	'AW_ARCADE_DUEL_SEND'		=> 'Herausfordern',
	'AW_ARCADE_DUEL_BANNER'		=> 'Duell: %1$s fordert dich heraus – schlage <strong>%2$s</strong> bis %3$s.',
	'AW_ARCADE_BACK_TO_SCORES'	=> 'Zurück zur Bestenliste',
	'AW_ARCADE_LOGIN_FOR_DUELS'	=> 'Melde dich an, um deine Duelle zu sehen.',
	'AW_ARCADE_DUELS_OFF'		=> 'Duelle sind in dieser Spielhalle abgeschaltet.',
	'AW_ARCADE_DUEL_NOT_FOUND'	=> 'Dieses Duell gibt es nicht (mehr).',
	'AW_ARCADE_DUEL_NEED_SCORE'	=> 'Du brauchst ein eigenes Ergebnis in diesem Spiel, um jemanden herauszufordern.',
	'AW_ARCADE_DUEL_NO_USER'	=> 'Diesen Benutzer gibt es nicht.',
	'AW_ARCADE_DUEL_SELF'		=> 'Du kannst dich nicht selbst herausfordern.',
	'AW_ARCADE_DUEL_NOT_ALLOWED'	=> '%s kann dieses Spiel nicht spielen und daher nicht herausgefordert werden.',
	'AW_ARCADE_DUEL_EXISTS'		=> 'Zwischen dir und %s läuft in diesem Spiel schon ein Duell.',
	'AW_ARCADE_DUEL_MAX'		=> [
		1 => 'Du hast schon eine offene Herausforderung. Warte, bis sie entschieden ist.',
		2 => 'Du hast schon %d offene Herausforderungen. Warte, bis eine davon entschieden ist.',
	],
	'AW_ARCADE_MSG_CHALLENGED'	=> 'Du hast %s herausgefordert. Er oder sie wurde benachrichtigt.',
	'AW_ARCADE_MSG_DUEL_WON'	=> [
		1 => 'Damit hast du ein Duell gewonnen!',
		2 => 'Damit hast du %d Duelle gewonnen!',
	],
	'AW_ARCADE_MSG_DUEL_DECLINED'	=> 'Du hast die Herausforderung abgelehnt.',

	'AW_ARCADE_NOTIFICATION_GROUP'				=> 'Spielhallen-Benachrichtigungen',
	'AW_ARCADE_NOTIFICATION_TYPE_DUEL_CHALLENGE'	=> 'Jemand fordert mich in der Spielhalle zum Duell heraus',
	'AW_ARCADE_NOTIFICATION_TYPE_DUEL_RESULT'	=> 'Ein Duell in der Spielhalle ist entschieden',
	'AW_ARCADE_NOTIFICATION_DUEL_CHALLENGE'		=> '<strong>Duell:</strong> %1$s fordert dich in „%2$s“ heraus',
	'AW_ARCADE_NOTIFICATION_DUEL_BEAT'			=> 'Schlage %1$s bis %2$s',
	'AW_ARCADE_NOTIFICATION_DUEL_WON'			=> '<strong>Duell gewonnen:</strong> gegen %1$s in „%2$s“',
	'AW_ARCADE_NOTIFICATION_DUEL_LOST'			=> '<strong>Duell verloren:</strong> gegen %1$s in „%2$s“',
	'AW_ARCADE_NOTIFICATION_DUEL_DECLINED'		=> '<strong>Duell abgelehnt:</strong> %1$s hat deine Herausforderung in „%2$s“ abgelehnt',
	'AW_ARCADE_NOTIFICATION_DUEL_SCORES'		=> 'Vorgelegt: %1$s – Gegner: %2$s',

	'AW_ARCADE_DISCUSS'			=> 'Tipps &amp; Diskussion',

	'AW_ARCADE_FAVORITES'		=> 'Meine Favoriten',
	'AW_ARCADE_FAV_ADD'			=> 'Zu Favoriten',
	'AW_ARCADE_FAV_REMOVE'		=> 'Favorit entfernen',
	'AW_ARCADE_FAV_TOGGLE'		=> '%s als Favorit markieren oder entfernen',
	'AW_ARCADE_FAV_MAX'			=> 'Du kannst höchstens %d Favoriten haben. Entferne zuerst einen anderen.',
	'AW_ARCADE_MEDAL_1'			=> [
		1 => '%d erster Platz',
		2 => '%d erste Plätze',
	],
	'AW_ARCADE_MEDAL_2'			=> [
		1 => '%d zweiter Platz',
		2 => '%d zweite Plätze',
	],
	'AW_ARCADE_MEDAL_3'			=> [
		1 => '%d dritter Platz',
		2 => '%d dritte Plätze',
	],
	'AW_ARCADE_REPORT'			=> 'Spiel melden',
	'AW_ARCADE_REPORT_TITLE'	=> 'Problem mit diesem Spiel melden',
	'AW_ARCADE_REPORT_EXPLAIN'	=> 'Deine Meldung geht direkt an die Administratoren.',
	'AW_ARCADE_REPORT_REASON'	=> 'Problem',
	'AW_ARCADE_REPORT_TEXT'		=> 'Details',
	'AW_ARCADE_REPORT_TEXT_HINT'	=> 'optional, z. B. Browser oder Gerät',
	'AW_ARCADE_REPORT_SEND'		=> 'Absenden',
	'AW_ARCADE_REPORT_THANKS'	=> 'Danke für deine Meldung! Die Administratoren schauen sich das Spiel an.',
	'AW_ARCADE_REPORT_SCORES'	=> 'Punkte werden nicht gespeichert',
	'AW_ARCADE_REPORT_GRAPHICS'	=> 'Grafik- oder Tonfehler',
	'AW_ARCADE_REPORT_MOBILE'	=> 'Geht auf Handy oder Tablet nicht',
	'AW_ARCADE_REPORT_LOADING'	=> 'Spiel lädt nicht oder hängt',
	'AW_ARCADE_REPORT_OTHER'	=> 'Sonstiges',
	'AW_ARCADE_BACK_TO_GAME'	=> 'Zurück zum Spiel',
]);
