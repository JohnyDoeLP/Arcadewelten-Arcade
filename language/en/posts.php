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
	'AW_ARCADE_POST_GAME_TITLE'		=> '[Game installed] – %s',
	'AW_ARCADE_POST_GAMES_TITLE'	=> '[Games installed] – %d new games',
	'AW_ARCADE_POST_GAMES_INTRO'	=> '%d new games have been added to the arcade:',
	'AW_ARCADE_POST_GAME'			=> 'Game',
	'AW_ARCADE_POST_CATEGORY'		=> 'Category',
	'AW_ARCADE_POST_DESC'			=> 'Description',
	'AW_ARCADE_POST_INFO'			=> 'Info',
	'AW_ARCADE_POST_SIZE'			=> '%s',
	'AW_ARCADE_POST_DIMENSIONS'		=> '%1$d × %2$d px',
	'AW_ARCADE_POST_TOUCH'			=> 'playable on mobile too',
	'AW_ARCADE_POST_NO_SCORES'		=> 'no highscores',
	'AW_ARCADE_POST_PLAY'			=> 'Play now',
	'AW_ARCADE_POST_SCORES'			=> 'Highscores',
	'AW_ARCADE_POST_DISCUSS'		=> 'Tips, tricks and questions about the game are welcome in this topic.',
	'AW_ARCADE_POST_DISCUSS_MANY'	=> 'Tips, tricks and questions about the games are welcome in this topic.',
	'AW_ARCADE_POST_SEASON_TITLE'	=> '[Top 10 of the arcade] – %s',
	'AW_ARCADE_POST_SEASON_INTRO'	=> 'The season from %1$s to %2$s has ended. The best players by number of records:',
	'AW_ARCADE_POST_RECORDS'		=> [
		1 => '%d record',
		2 => '%d records',
	],
	'AW_ARCADE_POST_PRIZE'			=> 'prize: %1$s %2$s',
	'AW_ARCADE_POST_SEASON_PRIZES'	=> 'In total %1$s %2$s were paid out to %3$d players.',
	'AW_ARCADE_POST_SEASON_EMPTY'	=> 'No records were set this season.',
	'AW_ARCADE_POST_SEASON_NEXT'	=> 'The new season is already running – good luck!',
	'AW_ARCADE_POST_TO_ARCADE'		=> 'To the arcade',
	'AW_ARCADE_POST_TOURNEY_TITLE'			=> '[Tournament] – %s',
	'AW_ARCADE_POST_TOURNEY_INTRO'			=> 'A new tournament has started! It runs from %1$s to %2$s.',
	'AW_ARCADE_POST_TOURNEY_GAMES'			=> 'These games count:',
	'AW_ARCADE_POST_TOURNEY_MORE'			=> [
		1 => '%d more game (only visible to members with access)',
		2 => '%d more games (only visible to members with access)',
	],
	'AW_ARCADE_POST_TOURNEY_RULES'			=> 'Per game your best result above 0 within the tournament period counts. The places earn tournament points (10, 8, 6, 5, 4, 3, 2, 1); the total decides.',
	'AW_ARCADE_POST_TOURNEY_PRIZES'			=> 'Prizes:',
	'AW_ARCADE_POST_TOURNEY_PRIZE_LINE'		=> 'Place %1$d: %2$s %3$s',
	'AW_ARCADE_POST_TOURNEY_TO'				=> 'To the tournament',
	'AW_ARCADE_POST_TOURNEY_GOOD_LUCK'		=> 'good luck!',
	'AW_ARCADE_POST_TOURNEY_RESULT_TITLE'	=> '[Tournament result] – %s',
	'AW_ARCADE_POST_TOURNEY_RESULT_INTRO'	=> 'The tournament “%s” has ended. The overall ranking:',
	'AW_ARCADE_POST_TOURNEY_POINTS'			=> [
		1 => '%d tournament point',
		2 => '%d tournament points',
	],
	'AW_ARCADE_POST_TOURNEY_EMPTY'			=> 'Nobody took part in this tournament.',
	'AW_ARCADE_POST_TOURNEY_FULL'			=> 'Full ranking',
]);
