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

	'AW_ARCADE_HELP_TITLE'	=> 'Arcade help',

	// Getting started
	'AW_ARCADE_HELP_BLOCK_START'				=> 'Getting started',
	'AW_ARCADE_HELP_START_WHAT_QUESTION'		=> 'What is the arcade?',
	'AW_ARCADE_HELP_START_WHAT_ANSWER'			=> 'A part of the board where you play games straight in your browser. Your results go into highscore lists, and depending on what this board has switched on there are duels, tournaments and points alongside.',
	'AW_ARCADE_HELP_START_WHERE_QUESTION'		=> 'Where do I find the arcade?',
	'AW_ARCADE_HELP_START_WHERE_ANSWER'			=> 'Through the “Arcade” entry in the board menu. You will see the categories there and the games below them. The search at the top finds a game by its name.',
	'AW_ARCADE_HELP_START_GUEST_QUESTION'		=> 'Can I play without an account?',
	'AW_ARCADE_HELP_START_GUEST_ANSWER'			=> 'That is for this board to decide, and it can differ from game to game. Where guests may play, no results are kept – that needs an account.',
	'AW_ARCADE_HELP_START_MOBILE_QUESTION'		=> 'Does this work on a phone?',
	'AW_ARCADE_HELP_START_MOBILE_ANSWER'		=> 'HTML5 games usually do. Older Flash games run, but often want a mouse and a keyboard. Games that can be played with a finger are marked as such in the list.',
	'AW_ARCADE_HELP_START_FULLSCREEN_QUESTION'	=> 'Can I play in fullscreen?',
	'AW_ARCADE_HELP_START_FULLSCREEN_ANSWER'	=> 'Yes, the “Fullscreen” button sits under the game. Esc brings you back out.',
	'AW_ARCADE_HELP_START_LIGHTS_QUESTION'		=> 'What does “Lights off” do?',
	'AW_ARCADE_HELP_START_LIGHTS_ANSWER'		=> 'It darkens everything around the game so nothing distracts you. Click it again to undo.',

	// Scores
	'AW_ARCADE_HELP_BLOCK_SCORES'				=> 'Results and highscores',
	'AW_ARCADE_HELP_SCORES_HOW_QUESTION'		=> 'How does my result get into the highscore list?',
	'AW_ARCADE_HELP_SCORES_HOW_ANSWER'			=> 'By itself. The game reports your result to the arcade when it ends; some games have a button for it, labelled “Submit” or something similar. After that it appears in the game’s highscore list.',
	'AW_ARCADE_HELP_SCORES_NOT_COUNTED_QUESTION'=> 'Why was my result not counted?',
	'AW_ARCADE_HELP_SCORES_NOT_COUNTED_ANSWER'	=> 'There are a few possible reasons. The usual ones: you were not logged in, the result was not better than your own best, or it was held back for checking – the next two questions cover that. Some old games also report nothing at all when you scored zero.',
	'AW_ARCADE_HELP_SCORES_TOO_FAST_QUESTION'	=> 'My result arrived “too quickly” – what does that mean?',
	'AW_ARCADE_HELP_SCORES_TOO_FAST_ANSWER'		=> 'Some games have a shortest playing time set for them. A result that arrives sooner goes into a list for checking instead of counting straight away. A moderator looks at it and lets it through or drops it. It is not an accusation – a very short game can simply be over quickly.',
	'AW_ARCADE_HELP_SCORES_TOO_HIGH_QUESTION'	=> 'My result was unusually high and is now missing.',
	'AW_ARCADE_HELP_SCORES_TOO_HIGH_ANSWER'		=> 'A game can have a highest score set for it. Anything above that goes into the same list for checking. If you know your result was real, say so in the board, or use “Report” on the game page.',
	'AW_ARCADE_HELP_SCORES_CHECKED_QUESTION'	=> 'Is there any check against cheating?',
	'AW_ARCADE_HELP_SCORES_CHECKED_ANSWER'		=> 'Up to a point. The arcade works out whether the result sent matches what the game sent along encoded, and watches the shortest playing time and the highest score. Full protection is not possible for games that run in a browser – which is what the checking list is for.',
	'AW_ARCADE_HELP_SCORES_BLACK_QUESTION'		=> 'The picture goes black when the score is saved.',
	'AW_ARCADE_HELP_SCORES_BLACK_ANSWER'		=> 'This happens with older Flash games: the game leaves the screen as it sends, or loads a closing screen it cannot find. Reload the page and look at the highscore list – your result is usually there all the same.',
	'AW_ARCADE_HELP_SCORES_ZERO_QUESTION'		=> 'One game always saves zero.',
	'AW_ARCADE_HELP_SCORES_ZERO_ANSWER'			=> 'Then the game is probably missing a file that tells it where its score is kept. That is the game’s doing, not yours – use “Report” on the game page so somebody with access to the files can look at it.',
	'AW_ARCADE_HELP_SCORES_BEST_ONLY_QUESTION'	=> 'Why is there only one result of mine in the list?',
	'AW_ARCADE_HELP_SCORES_BEST_ONLY_ANSWER'	=> 'The list holds one result per member, your best. For most games that is the highest score, for some – a race against the clock, say – the lowest. Play better than before and your old entry is replaced.',

	// Categories
	'AW_ARCADE_HELP_BLOCK_CATEGORIES'			=> 'Categories',
	'AW_ARCADE_HELP_CATEGORIES_MISSING_QUESTION'=> 'Why can I not see a category?',
	'AW_ARCADE_HELP_CATEGORIES_MISSING_ANSWER'	=> 'A category can be limited to certain groups, and is then seen only by those in them. There are also categories with a period – Christmas or Easter, for instance – which show up only in their season.',
	'AW_ARCADE_HELP_CATEGORIES_PASSWORD_QUESTION'=> 'A category is asking for a password.',
	'AW_ARCADE_HELP_CATEGORIES_PASSWORD_ANSWER'	=> 'Then somebody has locked it. You get the password where it was given out, usually in a topic on the board. Once entered, the category stays open for this visit.',
	'AW_ARCADE_HELP_CATEGORIES_SEASON_CAT_QUESTION'=> 'Where have the Christmas games gone?',
	'AW_ARCADE_HELP_CATEGORIES_SEASON_CAT_ANSWER'=> 'A category with a period is hidden outside it. The games and every highscore stay as they are and come back next year.',

	// Season
	'AW_ARCADE_HELP_BLOCK_SEASON'				=> 'The season',
	'AW_ARCADE_HELP_SEASON_WHAT_SEASON_QUESTION'=> 'What is a season?',
	'AW_ARCADE_HELP_SEASON_WHAT_SEASON_ANSWER'	=> 'A period after which the highscores are cleared, so the same records do not sit at the top for years. Whoever holds the most records at the end wins the season.',
	'AW_ARCADE_HELP_SEASON_WHEN_QUESTION'		=> 'When does the season end?',
	'AW_ARCADE_HELP_SEASON_WHEN_ANSWER'			=> 'This board sets that. The arcade shows when the running season began, and at the end a topic appears on the board with the first ten places.',
	'AW_ARCADE_HELP_SEASON_RECORDS_GONE_QUESTION'=> 'All my records are gone!',
	'AW_ARCADE_HELP_SEASON_RECORDS_GONE_ANSWER'	=> 'Then a season has just ended. That is meant to happen and it hits everyone alike. How often a game was played, and the medals you hold, stay as they are.',
	'AW_ARCADE_HELP_SEASON_CERTIFICATE_QUESTION'=> 'What are the certificates about?',
	'AW_ARCADE_HELP_SEASON_CERTIFICATE_ANSWER'	=> 'The first three places at the end of a season – and of a tournament – get a certificate as a picture. It hangs under the result topic on the board, and your own statistics page in the arcade lets you look at yours and download them.',

	// Duels
	'AW_ARCADE_HELP_BLOCK_DUELS'				=> 'Duels',
	'AW_ARCADE_HELP_DUELS_WHAT_DUEL_QUESTION'	=> 'What is a duel?',
	'AW_ARCADE_HELP_DUELS_WHAT_DUEL_ANSWER'		=> 'A challenge to another member in one particular game. You put your result up, and your opponent has until the deadline to beat it.',
	'AW_ARCADE_HELP_DUELS_CHALLENGE_QUESTION'	=> 'How do I challenge somebody?',
	'AW_ARCADE_HELP_DUELS_CHALLENGE_ANSWER'		=> 'On the page of a game you already have a result in, you will find “Challenge to a duel”. Pick your opponent and how long it should run.',
	'AW_ARCADE_HELP_DUELS_DECIDE_QUESTION'		=> 'How is a duel decided?',
	'AW_ARCADE_HELP_DUELS_DECIDE_ANSWER'		=> 'Your opponent has to beat the result you put up in that game. If they manage it, they win; if the deadline passes without it, you do.',
	'AW_ARCADE_HELP_DUELS_EXPIRE_QUESTION'		=> 'What if nobody plays?',
	'AW_ARCADE_HELP_DUELS_EXPIRE_ANSWER'		=> 'If the time runs out and the challenged member never played, the duel goes to whoever offered it. Your open duels are under “My duels”.',

	// Tournaments
	'AW_ARCADE_HELP_BLOCK_TOURNEYS'				=> 'Tournaments',
	'AW_ARCADE_HELP_TOURNEYS_WHAT_TOURNEY_QUESTION'=> 'What is a tournament?',
	'AW_ARCADE_HELP_TOURNEYS_WHAT_TOURNEY_ANSWER'=> 'Several games over a set period with one ranking across them. How you do in each game earns tournament points; whoever has the most at the end wins.',
	'AW_ARCADE_HELP_TOURNEYS_JOIN_QUESTION'		=> 'How do I take part?',
	'AW_ARCADE_HELP_TOURNEYS_JOIN_ANSWER'		=> 'By playing. There is nothing to sign up for – play one of the tournament games while the tournament runs and you appear in the ranking.',
	'AW_ARCADE_HELP_TOURNEYS_POINTS_TOURNEY_QUESTION'=> 'How are tournament points given out?',
	'AW_ARCADE_HELP_TOURNEYS_POINTS_TOURNEY_ANSWER'=> 'The better your place in one of the tournament games, the more points. The tournament page shows the single games and the overall table side by side.',
	'AW_ARCADE_HELP_TOURNEYS_MISSED_QUESTION'	=> 'I missed the start – is it still worth it?',
	'AW_ARCADE_HELP_TOURNEYS_MISSED_ANSWER'		=> 'Usually yes. What counts is your best result in each game, not when you played it. As long as the tournament runs, you can still catch up.',

	// Points
	'AW_ARCADE_HELP_BLOCK_POINTS'				=> 'Points and the jackpot',
	'AW_ARCADE_HELP_POINTS_EARN_QUESTION'		=> 'How do I earn points in the arcade?',
	'AW_ARCADE_HELP_POINTS_EARN_ANSWER'			=> 'For playing, and depending on the settings for records, season places and tournaments as well. What a game gives is written on its page before you start.',
	'AW_ARCADE_HELP_POINTS_COST_QUESTION'		=> 'Why does a game cost points?',
	'AW_ARCADE_HELP_POINTS_COST_ANSWER'			=> 'Some games have a stake. Before you start you see what a round costs, what you get back for it and where your balance stands – nothing is taken until you agree.',
	'AW_ARCADE_HELP_POINTS_JACKPOT_QUESTION'	=> 'What is the jackpot?',
	'AW_ARCADE_HELP_POINTS_JACKPOT_ANSWER'		=> 'A pot that part of the stakes flows into. Whoever sets a new all-time record in a game has it paid out to them.',
	'AW_ARCADE_HELP_POINTS_NOT_ENOUGH_QUESTION'	=> 'I do not have enough points for a game.',
	'AW_ARCADE_HELP_POINTS_NOT_ENOUGH_ANSWER'	=> 'Play one of the games without a stake first – many of them credit you points for every round. There are usually points for posting on the board too, depending on how this board has it set up.',

	// Around the games
	'AW_ARCADE_HELP_BLOCK_EXTRAS'				=> 'Around the games',
	'AW_ARCADE_HELP_EXTRAS_FAVOURITES_QUESTION'	=> 'How do I keep a game to hand?',
	'AW_ARCADE_HELP_EXTRAS_FAVOURITES_ANSWER'	=> 'With “Add to favourites” on the game page. Your favourites are then one click away from the arcade list.',
	'AW_ARCADE_HELP_EXTRAS_RATING_QUESTION'		=> 'Can I rate games?',
	'AW_ARCADE_HELP_EXTRAS_RATING_ANSWER'		=> 'Yes, with the stars on the game page. Every member has one vote per game and can change it at any time.',
	'AW_ARCADE_HELP_EXTRAS_MEDALS_QUESTION'		=> 'What are the medals next to my name?',
	'AW_ARCADE_HELP_EXTRAS_MEDALS_ANSWER'		=> 'They show how often you stand first, second or third in a highscore list – gold, silver and bronze in that order. They appear beside your posts and in your profile.',
	'AW_ARCADE_HELP_EXTRAS_NEW_GAMES_QUESTION'	=> 'How do I spot new games?',
	'AW_ARCADE_HELP_EXTRAS_NEW_GAMES_ANSWER'	=> 'They carry a “New” label for a while, and the category tiles show how many new games are inside.',

	// Downloading
	'AW_ARCADE_HELP_BLOCK_DOWNLOAD'				=> 'Downloading games',
	'AW_ARCADE_HELP_DOWNLOAD_HOW_DOWNLOAD_QUESTION'=> 'Can I download games?',
	'AW_ARCADE_HELP_DOWNLOAD_HOW_DOWNLOAD_ANSWER'=> 'For some games, yes – then a “Download” button sits under the game. You get it as a ZIP, in the shape an arcade can read back in. Which games those are is for this board to decide.',
	'AW_ARCADE_HELP_DOWNLOAD_OWN_GAME_QUESTION'	=> 'I made a game myself – can I contribute it?',
	'AW_ARCADE_HELP_DOWNLOAD_OWN_GAME_ANSWER'	=> 'Gladly, just ask on the board. How a game has to be packed so the arcade takes it is written down in the project under the name PACKAGING.',

	// Privacy
	'AW_ARCADE_HELP_BLOCK_PRIVACY'				=> 'What is kept about you',
	'AW_ARCADE_HELP_PRIVACY_STORED_QUESTION'	=> 'What does the arcade keep about me?',
	'AW_ARCADE_HELP_PRIVACY_STORED_ANSWER'		=> 'Your results with the time they were made, how often you played a game, your favourites and ratings, and running duels and tournament standings. While you play, a playing session is kept for a short while so a result can be matched to you; it is removed afterwards.',
	'AW_ARCADE_HELP_PRIVACY_DELETE_QUESTION'	=> 'What happens to it if I delete my account?',
	'AW_ARCADE_HELP_PRIVACY_DELETE_ANSWER'		=> 'Your results, favourites, ratings, duels and certificates go with it. The full account is in this board’s privacy policy.',
	'AW_ARCADE_HELP_PRIVACY_EXTERNAL_QUESTION'	=> 'Why does the arcade warn me about a game?',
	'AW_ARCADE_HELP_PRIVACY_EXTERNAL_ANSWER'	=> 'Because that game wants to fetch something from another server when it starts, and your IP address goes there with the request. The arcade names the servers before the game starts and leaves the choice to you. The arcade itself carries no advertising.',
]);
