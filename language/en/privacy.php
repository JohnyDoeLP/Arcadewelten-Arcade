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
	'AW_ARCADE_PRIVACY_REVIEW'		=> 'If a result fails the automatic plausibility check (e.g. an unusually short play time), it is stored with game, score and play time for review by an administrator and then either accepted or deleted.',
	'AW_ARCADE_PRIVACY_COOKIE'		=> 'Some categories are password protected. When you enter the password correctly, we set a technically necessary cookie (<code>%s</code>) that unlocks the category for you. It contains no personal data in plain text and is deleted when you close your browser.',
	'AW_ARCADE_PRIVACY_POINTS'		=> 'Playing can cost %s, and results can earn them. These transactions are stored in your points account on the board.',
	'AW_ARCADE_PRIVACY_DEBUG'		=> 'A diagnostic mode is currently switched on for troubleshooting: score submissions of the games are additionally stored in the administration log with username, IP address and the data sent by the game. The mode is switched off again after troubleshooting.',
	'AW_ARCADE_PRIVACY_LOCAL'		=> 'The games and the Flash emulator they need (Ruffle) are loaded exclusively from our own server. No data is passed on to third parties, and no advertising or tracking services are used.',
	'AW_ARCADE_PRIVACY_LEGAL'		=> 'The legal basis is Art. 6(1)(b) GDPR (use of the arcade as part of our board). If your user account is deleted, your arcade data is deleted as well.',
]);
