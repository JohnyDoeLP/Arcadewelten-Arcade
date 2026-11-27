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

	'AW_ARCADE_HELP_TITLE'	=> 'Hilfe zur Spielhalle',

	// Erste Schritte
	'AW_ARCADE_HELP_BLOCK_START'				=> 'Erste Schritte',
	'AW_ARCADE_HELP_START_WHAT_QUESTION'		=> 'Was ist die Spielhalle?',
	'AW_ARCADE_HELP_START_WHAT_ANSWER'			=> 'Ein Bereich des Forums, in dem du Spiele direkt im Browser spielen kannst. Deine Ergebnisse landen in Bestenlisten, und je nach Einstellung dieses Forums gibt es Duelle, Turniere und Punkte dazu.',
	'AW_ARCADE_HELP_START_WHERE_QUESTION'		=> 'Wo finde ich die Spielhalle?',
	'AW_ARCADE_HELP_START_WHERE_ANSWER'			=> 'Über den Eintrag „Spielhalle“ im Menü des Forums. Dort siehst du die Kategorien und darunter die Spiele. Mit der Suche oben findest du ein Spiel auch direkt über seinen Namen.',
	'AW_ARCADE_HELP_START_GUEST_QUESTION'		=> 'Kann ich ohne Anmeldung spielen?',
	'AW_ARCADE_HELP_START_GUEST_ANSWER'			=> 'Das entscheidet dieses Forum, und es kann sich von Spiel zu Spiel unterscheiden. Wo Gäste spielen dürfen, werden allerdings keine Ergebnisse gespeichert – dafür braucht es ein Konto.',
	'AW_ARCADE_HELP_START_MOBILE_QUESTION'		=> 'Läuft das auf dem Handy?',
	'AW_ARCADE_HELP_START_MOBILE_ANSWER'		=> 'HTML5-Spiele meistens ja. Ältere Flash-Spiele laufen zwar, brauchen aber oft Maus und Tastatur. Spiele, die sich mit dem Finger bedienen lassen, sind in der Übersicht entsprechend gekennzeichnet.',
	'AW_ARCADE_HELP_START_FULLSCREEN_QUESTION'	=> 'Geht das auch im Vollbild?',
	'AW_ARCADE_HELP_START_FULLSCREEN_ANSWER'	=> 'Ja, der Knopf „Vollbild“ steht unter dem Spielfenster. Mit der Taste Esc kommst du wieder heraus.',
	'AW_ARCADE_HELP_START_LIGHTS_QUESTION'		=> 'Was macht „Licht aus“?',
	'AW_ARCADE_HELP_START_LIGHTS_ANSWER'		=> 'Der Knopf dunkelt alles um das Spielfenster herum ab, damit nichts ablenkt. Noch einmal klicken macht es rückgängig.',

	// Punkte und Bestenlisten
	'AW_ARCADE_HELP_BLOCK_SCORES'				=> 'Ergebnisse und Bestenlisten',
	'AW_ARCADE_HELP_SCORES_HOW_QUESTION'		=> 'Wie kommt mein Ergebnis in die Bestenliste?',
	'AW_ARCADE_HELP_SCORES_HOW_ANSWER'			=> 'Von allein. Das Spiel meldet dein Ergebnis am Ende selbst an die Spielhalle; manche Spiele haben dafür einen Knopf wie „Submit“ oder „Score senden“. Danach erscheint es in der Bestenliste des Spiels.',
	'AW_ARCADE_HELP_SCORES_NOT_COUNTED_QUESTION'=> 'Warum wurde mein Ergebnis nicht gewertet?',
	'AW_ARCADE_HELP_SCORES_NOT_COUNTED_ANSWER'	=> 'Dafür gibt es mehrere mögliche Gründe. Am häufigsten: Du warst nicht angemeldet, das Ergebnis war besser als deine bisherige Bestleistung nicht, oder es wurde zur Prüfung zurückgehalten – dazu stehen die nächsten beiden Fragen hier. Manche alten Spiele melden auch gar nichts, wenn du null Punkte hast.',
	'AW_ARCADE_HELP_SCORES_TOO_FAST_QUESTION'	=> 'Mein Ergebnis kam „zu schnell“ – was heißt das?',
	'AW_ARCADE_HELP_SCORES_TOO_FAST_ANSWER'		=> 'Für manche Spiele ist eine Mindestspielzeit hinterlegt. Trifft ein Ergebnis vorher ein, wandert es in eine Prüfliste, statt sofort zu zählen. Ein Moderator sieht es sich an und gibt es frei oder verwirft es. Das ist kein Vorwurf – ein sehr kurzes Spiel kann auch einfach schnell vorbei sein.',
	'AW_ARCADE_HELP_SCORES_TOO_HIGH_QUESTION'	=> 'Mein Ergebnis war ungewöhnlich hoch und fehlt jetzt.',
	'AW_ARCADE_HELP_SCORES_TOO_HIGH_ANSWER'		=> 'Für Spiele kann eine Höchstpunktzahl festgelegt sein. Alles darüber landet ebenfalls zur Prüfung in der Prüfliste. Wenn du weißt, dass dein Ergebnis echt war, schreib es ruhig in den Bereich des Spiels oder melde es über „Melden“ auf der Spielseite.',
	'AW_ARCADE_HELP_SCORES_CHECKED_QUESTION'	=> 'Wird geprüft, ob jemand schummelt?',
	'AW_ARCADE_HELP_SCORES_CHECKED_ANSWER'		=> 'Ein Stück weit. Die Spielhalle rechnet nach, ob das übermittelte Ergebnis zu dem passt, was das Spiel verschlüsselt mitgeschickt hat, und achtet auf Mindestspielzeit und Höchstpunktzahl. Einen vollständigen Schutz gibt es bei Spielen, die im Browser laufen, allerdings nicht – deshalb die Prüfliste.',
	'AW_ARCADE_HELP_SCORES_BLACK_QUESTION'		=> 'Das Bild bleibt schwarz, wenn ich Punkte speichere.',
	'AW_ARCADE_HELP_SCORES_BLACK_ANSWER'		=> 'Das kommt bei älteren Flash-Spielen vor: Das Spiel verabschiedet sich beim Absenden vom Bildschirm oder lädt einen Abschlussbildschirm, den es nicht findet. Lade die Seite neu und schau in die Bestenliste – meist ist dein Ergebnis trotzdem da.',
	'AW_ARCADE_HELP_SCORES_ZERO_QUESTION'		=> 'Ein Spiel speichert immer null Punkte.',
	'AW_ARCADE_HELP_SCORES_ZERO_ANSWER'			=> 'Dann fehlt dem Spiel vermutlich eine Datei, aus der es erfährt, wo sein Punktestand steht. Das liegt am Spiel und nicht an dir – melde es über „Melden“ auf der Spielseite, dann kann sich jemand mit Zugriff auf die Dateien darum kümmern.',
	'AW_ARCADE_HELP_SCORES_BEST_ONLY_QUESTION'	=> 'Warum steht in der Bestenliste nur ein Ergebnis von mir?',
	'AW_ARCADE_HELP_SCORES_BEST_ONLY_ANSWER'	=> 'In der Bestenliste steht pro Mitglied nur das beste Ergebnis. Bei den meisten Spielen ist das die höchste Punktzahl, bei einigen – etwa Zeitspielen – die niedrigste. Spielst du besser als zuvor, wird dein alter Eintrag ersetzt.',

	// Kategorien
	'AW_ARCADE_HELP_BLOCK_CATEGORIES'			=> 'Kategorien',
	'AW_ARCADE_HELP_CATEGORIES_MISSING_QUESTION'=> 'Warum sehe ich eine Kategorie nicht?',
	'AW_ARCADE_HELP_CATEGORIES_MISSING_ANSWER'	=> 'Kategorien können auf bestimmte Benutzergruppen beschränkt sein – dann sieht sie nur, wer dazugehört. Außerdem gibt es Kategorien mit einem Zeitraum, etwa für Weihnachten oder Ostern; die erscheinen nur in ihrer Jahreszeit.',
	'AW_ARCADE_HELP_CATEGORIES_PASSWORD_QUESTION'=> 'Eine Kategorie fragt nach einem Passwort.',
	'AW_ARCADE_HELP_CATEGORIES_PASSWORD_ANSWER'	=> 'Dann hat jemand sie geschützt. Das Passwort erfährst du dort, wo es vergeben wurde – meist in einem Beitrag im Forum. Einmal eingegeben, bleibt die Kategorie für diesen Besuch offen.',
	'AW_ARCADE_HELP_CATEGORIES_SEASON_CAT_QUESTION'=> 'Wohin sind die Weihnachtsspiele verschwunden?',
	'AW_ARCADE_HELP_CATEGORIES_SEASON_CAT_ANSWER'=> 'Kategorien mit einem Zeitraum werden außerhalb davon ausgeblendet. Die Spiele und alle Bestenlisten bleiben erhalten und kommen im nächsten Jahr wieder.',

	// Saison
	'AW_ARCADE_HELP_BLOCK_SEASON'				=> 'Saison',
	'AW_ARCADE_HELP_SEASON_WHAT_SEASON_QUESTION'=> 'Was ist eine Saison?',
	'AW_ARCADE_HELP_SEASON_WHAT_SEASON_ANSWER'	=> 'Ein Zeitraum, nach dem die Bestenlisten zurückgesetzt werden, damit nicht jahrelang dieselben Rekorde oben stehen. Wer am Ende die meisten Rekorde hält, gewinnt die Saison.',
	'AW_ARCADE_HELP_SEASON_WHEN_QUESTION'		=> 'Wann endet die Saison?',
	'AW_ARCADE_HELP_SEASON_WHEN_ANSWER'			=> 'Das legt dieses Forum fest. In der Spielhalle wird angezeigt, wann die laufende Saison begonnen hat; zum Ende erscheint ein Beitrag im Forum mit den ersten zehn Plätzen.',
	'AW_ARCADE_HELP_SEASON_RECORDS_GONE_QUESTION'=> 'Meine Rekorde sind alle weg!',
	'AW_ARCADE_HELP_SEASON_RECORDS_GONE_ANSWER'	=> 'Dann ist vermutlich gerade eine Saison zu Ende gegangen. Das ist so gewollt und betrifft alle gleichermaßen. Wie oft ein Spiel gespielt wurde und deine Auszeichnungen bleiben erhalten.',
	'AW_ARCADE_HELP_SEASON_CERTIFICATE_QUESTION'=> 'Was hat es mit den Urkunden auf sich?',
	'AW_ARCADE_HELP_SEASON_CERTIFICATE_ANSWER'	=> 'Die ersten drei Plätze am Ende einer Saison – und ebenso eines Turniers – bekommen eine Urkunde als Bild. Sie hängt unter dem Ergebnisbeitrag im Forum, und auf deiner Statistikseite in der Spielhalle kannst du deine Urkunden ansehen und herunterladen.',

	// Duelle
	'AW_ARCADE_HELP_BLOCK_DUELS'				=> 'Duelle',
	'AW_ARCADE_HELP_DUELS_WHAT_DUEL_QUESTION'	=> 'Was ist ein Duell?',
	'AW_ARCADE_HELP_DUELS_WHAT_DUEL_ANSWER'		=> 'Eine Herausforderung an ein anderes Mitglied in einem bestimmten Spiel. Du legst dein Ergebnis vor, und dein Gegner hat bis zum Ablauf Zeit, es zu schlagen.',
	'AW_ARCADE_HELP_DUELS_CHALLENGE_QUESTION'	=> 'Wie fordere ich jemanden heraus?',
	'AW_ARCADE_HELP_DUELS_CHALLENGE_ANSWER'		=> 'Auf der Seite eines Spiels, bei dem du bereits ein Ergebnis hast, findest du den Bereich „Zum Duell herausfordern“. Dort wählst du den Gegner und die Dauer.',
	'AW_ARCADE_HELP_DUELS_DECIDE_QUESTION'		=> 'Wie wird ein Duell entschieden?',
	'AW_ARCADE_HELP_DUELS_DECIDE_ANSWER'		=> 'Dein Gegner muss dein vorgelegtes Ergebnis in diesem Spiel überbieten. Gelingt das, gewinnt er; schafft er es bis zum Ablauf nicht, gewinnst du.',
	'AW_ARCADE_HELP_DUELS_EXPIRE_QUESTION'		=> 'Was passiert, wenn niemand spielt?',
	'AW_ARCADE_HELP_DUELS_EXPIRE_ANSWER'		=> 'Läuft die Frist ab, ohne dass der Herausgeforderte angetreten ist, geht das Duell an denjenigen, der es gestellt hat. Deine offenen Duelle findest du über „Meine Duelle“.',

	// Turniere
	'AW_ARCADE_HELP_BLOCK_TOURNEYS'				=> 'Turniere',
	'AW_ARCADE_HELP_TOURNEYS_WHAT_TOURNEY_QUESTION'=> 'Was ist ein Turnier?',
	'AW_ARCADE_HELP_TOURNEYS_WHAT_TOURNEY_ANSWER'=> 'Mehrere Spiele über einen festen Zeitraum mit einer gemeinsamen Wertung. Für dein Abschneiden in jedem einzelnen Spiel bekommst du Turnierpunkte; wer am Ende die meisten hat, gewinnt.',
	'AW_ARCADE_HELP_TOURNEYS_JOIN_QUESTION'		=> 'Wie mache ich mit?',
	'AW_ARCADE_HELP_TOURNEYS_JOIN_ANSWER'		=> 'Indem du spielst. Eine Anmeldung gibt es nicht – sobald du eines der Turnierspiele im Turnierzeitraum spielst, erscheinst du in der Wertung.',
	'AW_ARCADE_HELP_TOURNEYS_POINTS_TOURNEY_QUESTION'=> 'Wie werden Turnierpunkte vergeben?',
	'AW_ARCADE_HELP_TOURNEYS_POINTS_TOURNEY_ANSWER'=> 'Je besser dein Platz in einem der Turnierspiele, desto mehr Punkte. Auf der Turnierseite siehst du die Einzelwertungen und die Gesamttabelle nebeneinander.',
	'AW_ARCADE_HELP_TOURNEYS_MISSED_QUESTION'	=> 'Ich habe den Anfang verpasst – lohnt es noch?',
	'AW_ARCADE_HELP_TOURNEYS_MISSED_ANSWER'		=> 'Meistens ja. Es zählt dein bestes Ergebnis je Spiel, nicht wann du gespielt hast. Solange das Turnier läuft, kannst du also aufholen.',

	// Punkte
	'AW_ARCADE_HELP_BLOCK_POINTS'				=> 'Punkte und Jackpot',
	'AW_ARCADE_HELP_POINTS_EARN_QUESTION'		=> 'Wie verdiene ich Punkte in der Spielhalle?',
	'AW_ARCADE_HELP_POINTS_EARN_ANSWER'			=> 'Fürs Spielen selbst, und je nach Einstellung zusätzlich für Rekorde, Saisonplätze und Turniere. Was es bei einem Spiel gibt, steht vor dem Start auf der Spielseite.',
	'AW_ARCADE_HELP_POINTS_COST_QUESTION'		=> 'Warum kostet ein Spiel Punkte?',
	'AW_ARCADE_HELP_POINTS_COST_ANSWER'			=> 'Manche Spiele haben einen Einsatz. Vor dem Start siehst du, was eine Runde kostet, was du dafür zurückbekommst und wie dein Guthaben steht – abgebucht wird erst, wenn du zustimmst.',
	'AW_ARCADE_HELP_POINTS_JACKPOT_QUESTION'	=> 'Was ist der Jackpot?',
	'AW_ARCADE_HELP_POINTS_JACKPOT_ANSWER'		=> 'Ein Topf, in den ein Teil der Spieleinsätze fließt. Wer einen neuen Allzeit-Rekord in einem Spiel aufstellt, bekommt ihn ausgeschüttet.',
	'AW_ARCADE_HELP_POINTS_NOT_ENOUGH_QUESTION'	=> 'Mir fehlen Punkte für ein Spiel.',
	'AW_ARCADE_HELP_POINTS_NOT_ENOUGH_ANSWER'	=> 'Dann spiel zunächst eines der Spiele ohne Einsatz – bei vielen bekommst du für jede Runde Punkte gutgeschrieben. Punkte gibt es außerdem für Beiträge im Forum, je nachdem, wie dieses Forum es eingestellt hat.',

	// Sonstiges
	'AW_ARCADE_HELP_BLOCK_EXTRAS'				=> 'Rund ums Spielen',
	'AW_ARCADE_HELP_EXTRAS_FAVOURITES_QUESTION'	=> 'Wie merke ich mir ein Spiel?',
	'AW_ARCADE_HELP_EXTRAS_FAVOURITES_ANSWER'	=> 'Mit dem Knopf „Zu Favoriten“ auf der Spielseite. Deine Favoriten erreichst du danach direkt aus der Spielhallen-Übersicht.',
	'AW_ARCADE_HELP_EXTRAS_RATING_QUESTION'		=> 'Kann ich Spiele bewerten?',
	'AW_ARCADE_HELP_EXTRAS_RATING_ANSWER'		=> 'Ja, mit den Sternen auf der Spielseite. Jedes Mitglied hat eine Stimme je Spiel und kann sie jederzeit ändern.',
	'AW_ARCADE_HELP_EXTRAS_MEDALS_QUESTION'		=> 'Was bedeuten die Medaillen bei meinem Namen?',
	'AW_ARCADE_HELP_EXTRAS_MEDALS_ANSWER'		=> 'Sie zeigen, wie oft du in einer Bestenliste auf dem ersten, zweiten oder dritten Platz stehst – Gold, Silber und Bronze der Reihe nach. Sie erscheinen neben deinen Beiträgen und in deinem Profil.',
	'AW_ARCADE_HELP_EXTRAS_NEW_GAMES_QUESTION'	=> 'Woran erkenne ich neue Spiele?',
	'AW_ARCADE_HELP_EXTRAS_NEW_GAMES_ANSWER'	=> 'Sie tragen eine Weile das Schild „Neu“, und die Kategorie-Kacheln zeigen, wie viele neue Spiele darin stecken.',

	// Spiele herunterladen
	'AW_ARCADE_HELP_BLOCK_DOWNLOAD'				=> 'Spiele herunterladen',
	'AW_ARCADE_HELP_DOWNLOAD_HOW_DOWNLOAD_QUESTION'=> 'Kann ich Spiele herunterladen?',
	'AW_ARCADE_HELP_DOWNLOAD_HOW_DOWNLOAD_ANSWER'=> 'Bei manchen Spielen ja – dann steht unter dem Spielfenster ein Knopf „Herunterladen“. Du bekommst das Spiel als ZIP, so wie es eine Spielhalle wieder einlesen kann. Welche Spiele das sind, entscheidet dieses Forum.',
	'AW_ARCADE_HELP_DOWNLOAD_OWN_GAME_QUESTION'	=> 'Ich habe selbst ein Spiel gebaut – kann ich es beisteuern?',
	'AW_ARCADE_HELP_DOWNLOAD_OWN_GAME_ANSWER'	=> 'Gern, frag einfach im Forum nach. Wie ein Spiel gepackt sein muss, damit die Spielhalle es annimmt, steht in der Anleitung des Projekts unter dem Namen PACKAGING.',

	// Datenschutz
	'AW_ARCADE_HELP_BLOCK_PRIVACY'				=> 'Was gespeichert wird',
	'AW_ARCADE_HELP_PRIVACY_STORED_QUESTION'	=> 'Was speichert die Spielhalle über mich?',
	'AW_ARCADE_HELP_PRIVACY_STORED_ANSWER'		=> 'Deine Ergebnisse mit Zeitpunkt, wie oft du ein Spiel gespielt hast, deine Favoriten und Bewertungen sowie laufende Duelle und Turnierwertungen. Während du spielst, wird kurzzeitig eine Spielsitzung gespeichert, damit ein Ergebnis zugeordnet werden kann; sie wird danach wieder entfernt.',
	'AW_ARCADE_HELP_PRIVACY_DELETE_QUESTION'	=> 'Was passiert damit, wenn ich mein Konto lösche?',
	'AW_ARCADE_HELP_PRIVACY_DELETE_ANSWER'		=> 'Deine Ergebnisse, Favoriten, Bewertungen, Duelle und Urkunden werden mit gelöscht. Die ausführliche Fassung steht in den Datenschutzhinweisen dieses Forums.',
	'AW_ARCADE_HELP_PRIVACY_EXTERNAL_QUESTION'	=> 'Warum warnt mich die Spielhalle vor einem Spiel?',
	'AW_ARCADE_HELP_PRIVACY_EXTERNAL_ANSWER'	=> 'Weil dieses Spiel beim Start etwas von einem fremden Server holen will – dabei geht deine IP-Adresse dorthin. Die Spielhalle nennt dir die Adressen vor dem Start, und du entscheidest, ob du das Spiel trotzdem öffnest. Die Spielhalle selbst schaltet keine Werbung.',
]);
