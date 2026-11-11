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
	'AW_ARCADE_PRIVACY_TITLE'		=> 'Arcade',
	'AW_ARCADE_PRIVACY_MEMBERS'		=> 'Our arcade lets you play browser games. When you play while logged in as a member, we store your best score per game, the time of that result and the number of your attempts. Other logged-in members can see this data together with your username: in the highscores, on your records page, in your profile, next to your posts and on the board index.',
	'AW_ARCADE_PRIVACY_SESSION'		=> 'While you play, we briefly create a play session (member ID, game, start time) so that only genuine results are stored. It is deleted once the score has been submitted; unfinished sessions are removed automatically after about four to five hours.',
	'AW_ARCADE_PRIVACY_GUESTS'		=> 'As a guest you can play selected games. We do not store any arcade data about you while you do, in particular no results.',
	'AW_ARCADE_PRIVACY_RECENT'		=> 'So that you can quickly continue playing, we remember the games you played most recently (at most %d, each with the time). Only you can see this list.',
	'AW_ARCADE_PRIVACY_FORUM'		=> 'At the end of every season we publish a ranking (Top 10) in the forum with usernames, number of records and prizes received. It is visible to everybody who may read that forum.',
	'AW_ARCADE_PRIVACY_FAVORITES'	=> 'If you mark games as favourites, we store this choice with the time so that they are shown at the top of the arcade. Only you see your favourites.',
	'AW_ARCADE_PRIVACY_REPORTS'		=> 'If you report a problem with a game, we store the game, the problem, your optional text, your member ID and the time until an administrator closes the report.',
	'AW_ARCADE_PRIVACY_DUELS'		=> 'If you challenge somebody to a duel or are challenged, we store both member IDs, the game, the scores, duration and outcome. Only the two participants see the duels. You are informed about challenges and results by board notification, by e-mail only if you switch that on in your user control panel.',
	'AW_ARCADE_PRIVACY_TOURNEYS'		=> 'If you play a tournament game during a tournament, we store your member ID, your best result, the time and the number of attempts for that tournament. The tournament ranking with usernames is visible to everybody who may use the arcade.',
	'AW_ARCADE_PRIVACY_TOURNEYS_POST'	=> 'If you play a tournament game during a tournament, we store your member ID, your best result, the time and the number of attempts for that tournament. The tournament ranking with usernames is visible to everybody who may use the arcade; when the tournament ends, the top ten also appear in a forum post.',
	'AW_ARCADE_PRIVACY_RATINGS'		=> 'You can rate games you have played with 1 to 5 stars. We store your rating with your member ID and the time so that you can change it later. Others only see the average and the number of ratings, not your individual rating.',
	'AW_ARCADE_PRIVACY_REVIEW'		=> 'If a result fails the automatic plausibility check (e.g. an unusually short play time), it is stored with game, score and play time for review by an administrator and then either accepted or deleted.',
	'AW_ARCADE_PRIVACY_COOKIE'		=> 'Some categories are password protected. When you enter the password correctly, we set a technically necessary cookie (<code>%s</code>) that unlocks the category for you. It contains no personal data in plain text and is deleted when you close your browser.',
	'AW_ARCADE_PRIVACY_POINTS'		=> 'Playing can cost %s, and results can earn them. These transactions are stored in your points account on the board.',
	'AW_ARCADE_PRIVACY_DEBUG'		=> 'A diagnostic mode is currently switched on for troubleshooting: score submissions of the games are additionally stored in the administration log with username, IP address and the data sent by the game. The mode is switched off again after troubleshooting.',
	'AW_ARCADE_PRIVACY_LOCAL'		=> 'The games and the Flash emulator they need (Ruffle) are loaded exclusively from our own server. The arcade itself passes no data on to third parties and uses no advertising or tracking services. Content shown on all pages of our board (e.g. advertising banners) is covered by the other sections of this privacy policy.',
	'AW_ARCADE_PRIVACY_BROWSER'		=> 'In your browser’s storage (local storage) we only keep whether you switched on cinema mode (“lights off”). Some Flash games also store their own save data (e.g. reached levels) in your browser through the Ruffle emulator. This data stays on your device, is not sent to us and can be deleted at any time in your browser settings.',
	'AW_ARCADE_PRIVACY_LEGAL'		=> 'The legal basis is Art. 6(1)(b) GDPR (use of the arcade as part of our board). If your user account is deleted, your arcade data is deleted as well.',
]);
