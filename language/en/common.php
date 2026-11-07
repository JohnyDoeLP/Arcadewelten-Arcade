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
	'AW_ARCADE'					=> 'Arcade',
	'AW_ARCADE_ALL_GAMES'		=> 'All games',
	'AW_ARCADE_SEARCH'			=> 'Search game …',
	'AW_ARCADE_GAME_COUNT'		=> [
		0 => 'No games',
		1 => '%d game',
		2 => '%d games',
	],
	'AW_ARCADE_PLAYS'			=> [
		0 => 'never played',
		1 => 'played %d time',
		2 => 'played %d times',
	],
	'AW_ARCADE_PLAYERS'			=> [
		0 => 'no players yet',
		1 => '%d player',
		2 => '%d players',
	],
	'AW_ARCADE_NO_GAMES'		=> 'There are no games here yet.',
	'AW_ARCADE_NO_CHAMPION'		=> 'No champion yet',
	'AW_ARCADE_NO_SCORES'		=> 'No score tracking',
	'AW_ARCADE_HIGHSCORES'		=> 'Highscores',
	'AW_ARCADE_NO_HIGHSCORES'	=> 'No scores yet. Be the first!',
	'AW_ARCADE_TOP10'			=> 'Top 10',
	'AW_ARCADE_SCORE'			=> 'Score',
	'AW_ARCADE_TRIES'			=> 'Tries',
	'AW_ARCADE_DATE'			=> 'Achieved',
	'AW_ARCADE_MY_RANK'			=> 'Your rank: %d',
	'AW_ARCADE_LOW_WINS'		=> 'Lower scores are better',
	'AW_ARCADE_PLAY_NOW'		=> 'Play now',
	'AW_ARCADE_DESCRIPTION'		=> 'Description',
	'AW_ARCADE_FULLSCREEN'		=> 'Fullscreen',
	'AW_ARCADE_NEED_JS'			=> 'JavaScript is required to play.',
	'AW_ARCADE_RUFFLE_MISSING'	=> 'The Flash emulator could not be loaded.',
	'AW_ARCADE_GUEST_HINT'		=> 'You are playing as a guest. Log in to have your scores saved.',
	'AW_ARCADE_LOGIN_TO_PLAY'	=> 'This game is for registered members only. Please log in.',

	'AW_ARCADE_MSG_GUEST'		=> 'Scores of guests are not saved. Log in to get into the highscores.',
	'AW_ARCADE_GUEST_TITLE'		=> 'You are not logged in.',
	'AW_ARCADE_GUEST_LIMITED'	=> [
		0 => 'As a guest you can only try the released games. Log in or register to play all games and get into the highscores.',
		1 => 'As a guest you can only try the released games. Log in or register to play all %d games and get into the highscores.',
		2 => 'As a guest you can only try the released games. Log in or register to play all %d games and get into the highscores.',
	],
	'AW_ARCADE_GUEST_BLOCKED'	=> 'The arcade is for registered members only. Log in or register to play.',
	'AW_ARCADE_GUEST_RELEASED'	=> 'Released for guests',
	'AW_ARCADE_LOGIN_FOR_CAT'	=> 'This category is only visible to registered members.',
	'AW_ARCADE_LOGIN_FOR_SCORES'	=> 'Highscores are only visible to registered members.',
	'AW_ARCADE_MSG_NOSESSION'	=> 'Your score could not be saved because the game session expired or was invalid. Please start the game again from the arcade.',
	'AW_ARCADE_MSG_SAVED'		=> 'Saved: %s points. You did not beat your personal best this time.',
	'AW_ARCADE_MSG_NEW_BEST'	=> 'New personal best: %s points!',

	'AW_ARCADE_PASSWORD_EXPLAIN'	=> 'This category is password protected.',
	'AW_ARCADE_WRONG_PASSWORD'		=> 'The password is wrong.',
	'AW_ARCADE_UNLOCK'				=> 'Unlock',
	'AW_ARCADE_NO_GAME'				=> 'This game does not exist or is disabled.',
	'AW_ARCADE_NO_CAT'				=> 'This category does not exist.',

	'AW_ARCADE_HALL_OF_FAME'	=> 'Hall of fame – most records',
	'AW_ARCADE_RECORDS'			=> [
		0 => 'no records',
		1 => '%d record',
		2 => '%d records',
	],
	'AW_ARCADE_RECORDS_LABEL'	=> 'Arcade records',
	'AW_ARCADE_RECORDS_TITLE'	=> 'Records',
	'AW_ARCADE_RECORDS_OF'		=> 'Records of %s',
	'AW_ARCADE_NO_RECORDS'		=> 'This member currently holds no record.',
	'AW_ARCADE_LATEST_RECORDS'	=> 'Latest arcade records',
	'AW_ARCADE_GAME'			=> 'Game',

	'AW_ARCADE_GOLD'			=> 'Gold',
	'AW_ARCADE_SILVER'			=> 'Silver',
	'AW_ARCADE_BRONZE'			=> 'Bronze',

	'AW_ARCADE_NOT_ENOUGH_POINTS'	=> 'This game costs %1$s %2$s, but you only have %3$s %2$s.',
	'AW_ARCADE_PAY_EXPLAIN'		=> 'One round costs %1$s %2$s.',
	'AW_ARCADE_YOUR_BALANCE'	=> 'Your balance: %1$s %2$s',
	'AW_ARCADE_PAY_AND_PLAY'	=> 'Play for %1$s %2$s',
	'AW_ARCADE_INFO_COST'		=> 'Cost: %1$s %2$s',
	'AW_ARCADE_INFO_ROUND'		=> 'per round with a score: +%1$s %2$s',
	'AW_ARCADE_INFO_SEASON'		=> 'Season prize place 1/2/3: %1$s / %2$s / %3$s %4$s',
	'AW_ARCADE_MSG_EARNED'		=> 'You earned %1$s %2$s for it.',

	'AW_ARCADE_CREDIT'			=> 'Arcade: %s',

	'AW_ARCADE_CATEGORIES'		=> 'Categories',

	'AW_ARCADE_MSG_FLAGGED'		=> 'Your result is unusual and will be reviewed by an administrator before it appears in the highscores.',

	'AW_ARCADE_DEC_POINT'		=> '.',
	'AW_ARCADE_THOUSANDS_SEP'	=> ',',

	'AW_ARCADE_RATING'			=> 'Rating',
	'AW_ARCADE_NOT_RATED'		=> 'Not rated yet',
	'AW_ARCADE_RATING_SUMMARY'	=> [
		1 => '%1$s out of 5 (%2$d rating)',
		2 => '%1$s out of 5 (%2$d ratings)',
	],
	'AW_ARCADE_YOUR_RATING'		=> 'Your rating',
	'AW_ARCADE_RATE_N'			=> [
		1 => '%d star',
		2 => '%d stars',
	],
	'AW_ARCADE_RATE_PLAY_FIRST'	=> 'Play the game to be able to rate it.',
	'AW_ARCADE_RATE_LOGIN'		=> 'Only logged-in members can rate games.',
	'AW_ARCADE_RATE_INVALID'	=> 'Please choose 1 to 5 stars.',
	'AW_ARCADE_MSG_RATED'		=> 'Thanks for your rating!',
	'AW_ARCADE_SORT'			=> 'Sort by',
	'AW_ARCADE_SORT_NAME'		=> 'Name',
	'AW_ARCADE_SORT_RATING'		=> 'Best rated',
	'AW_ARCADE_SORT_PLAYS'		=> 'Most played',
	'AW_ARCADE_SORT_NEW'		=> 'Newest',

	'AW_ARCADE_RECENT'			=> 'Recently played',
	'AW_ARCADE_TOUCH_GAME'		=> 'Playable on mobile too',
	'AW_ARCADE_TOUCH_START'		=> 'Play in fullscreen',
	'AW_ARCADE_FULLSCREEN_EXIT'	=> 'Exit fullscreen',
	'AW_ARCADE_NOT_TOUCH_HINT'	=> 'This game needs a keyboard or mouse and may not be playable on phones and tablets.',
	'AW_ARCADE_LIGHTS_OFF'		=> 'Lights off',
	'AW_ARCADE_LIGHTS_ON'		=> 'Lights on',
]);
