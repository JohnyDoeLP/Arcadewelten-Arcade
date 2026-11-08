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

// Section appended to the board's privacy policy (ucp.php?mode=privacy).
$lang = array_merge($lang, [
	'AW_ARCADE_PRIVACY_TITLE'		=> 'Spielhalle',
	'AW_ARCADE_PRIVACY_MEMBERS'		=> 'In unserer Spielhalle kannst du Browserspiele spielen. Wenn du als Mitglied angemeldet spielst, speichern wir je Spiel deine beste Punktzahl, den Zeitpunkt dieses Ergebnisses und die Anzahl deiner Versuche. Diese Daten sind für andere angemeldete Mitglieder mit deinem Benutzernamen sichtbar: in den Bestenlisten, auf deiner Rekordseite, in deinem Profil, neben deinen Beiträgen und auf der Startseite des Forums.',
	'AW_ARCADE_PRIVACY_SESSION'		=> 'Während eines Spiels legen wir kurzzeitig eine Spielsitzung an (Mitglieds-ID, Spiel, Startzeit), damit nur echte Spielergebnisse gespeichert werden. Sie wird nach der Punktemeldung gelöscht, nicht abgeschlossene Sitzungen nach etwa vier bis fünf Stunden automatisch.',
	'AW_ARCADE_PRIVACY_GUESTS'		=> 'Als Gast kannst du einzelne freigegebene Spiele spielen. Dabei speichern wir keine Spielhallen-Daten über dich, insbesondere keine Ergebnisse.',
	'AW_ARCADE_PRIVACY_RECENT'		=> 'Damit du schnell weiterspielen kannst, merken wir uns deine zuletzt gespielten Spiele (höchstens %d, jeweils mit Zeitpunkt). Diese Liste siehst nur du.',
	'AW_ARCADE_PRIVACY_FORUM'		=> 'Beim Abschluss jeder Saison veröffentlichen wir im Forum eine Bestenliste (Top 10) mit Benutzernamen, Anzahl der Rekorde und erhaltenen Prämien. Sie ist für alle sichtbar, die dieses Forum lesen dürfen.',
	'AW_ARCADE_PRIVACY_DUELS'		=> 'Forderst du jemanden zum Duell heraus oder wirst herausgefordert, speichern wir die beiden Mitglieds-IDs, das Spiel, die Ergebnisse, Laufzeit und Ausgang. Die Duelle sehen nur die beiden Beteiligten. Über Herausforderungen und Ergebnisse wirst du per Forenbenachrichtigung informiert, per E-Mail nur, wenn du das in deinem persönlichen Bereich einschaltest.',
	'AW_ARCADE_PRIVACY_RATINGS'		=> 'Du kannst Spiele, die du gespielt hast, mit 1 bis 5 Sternen bewerten. Wir speichern deine Bewertung mit Mitglieds-ID und Zeitpunkt, damit du sie später ändern kannst. Andere sehen nur den Durchschnitt und die Anzahl der Bewertungen, nicht deine einzelne Bewertung.',
	'AW_ARCADE_PRIVACY_REVIEW'		=> 'Fällt ein Ergebnis bei der automatischen Plausibilitätsprüfung auf (z. B. ungewöhnlich kurze Spielzeit), wird es mit Spiel, Punktzahl und Spieldauer zur Prüfung durch einen Administrator gespeichert und nach der Prüfung übernommen oder gelöscht.',
	'AW_ARCADE_PRIVACY_COOKIE'		=> 'Einige Kategorien sind durch ein Passwort geschützt. Gibst du es richtig ein, setzen wir ein technisch notwendiges Cookie (<code>%s</code>), das die Kategorie für dich freischaltet. Es enthält keine personenbezogenen Daten im Klartext und wird beim Schließen des Browsers gelöscht.',
	'AW_ARCADE_PRIVACY_POINTS'		=> 'Für das Spielen können %s abgezogen und für Ergebnisse gutgeschrieben werden. Diese Buchungen werden in deinem Punktekonto im Forum gespeichert.',
	'AW_ARCADE_PRIVACY_DEBUG'		=> 'Derzeit ist zur Fehlersuche ein Diagnosemodus eingeschaltet: Punktemeldungen der Spiele werden dabei zusätzlich mit Benutzername, IP-Adresse und den vom Spiel gesendeten Daten im Administrationsprotokoll gespeichert. Der Modus wird nach der Fehlersuche wieder ausgeschaltet.',
	'AW_ARCADE_PRIVACY_LOCAL'		=> 'Die Spiele und der dafür nötige Flash-Emulator (Ruffle) werden ausschließlich von unserem eigenen Server geladen. Es werden keine Daten an Dritte übertragen, und es werden keine Werbe- oder Tracking-Dienste eingebunden.',
	'AW_ARCADE_PRIVACY_LEGAL'		=> 'Rechtsgrundlage ist Art. 6 Abs. 1 lit. b DSGVO (Nutzung der Spielhalle als Teil unseres Forenangebots). Wird dein Benutzerkonto gelöscht, werden auch deine Spielhallen-Daten gelöscht.',
]);
