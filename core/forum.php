<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\core;

/**
 * Forum integration ("ArcadeBot").
 *
 * A bot user chosen in the ACP posts arcade news with phpBB's normal
 * submit_post(), so topic counters, subscriptions, notifications, search and
 * feeds work as for any other post:
 * - "[Game installed] – name": one topic per game (or one collective topic
 *   for big imports); the topic doubles as the game's discussion thread.
 * - "[Top 10 of the arcade]": a new topic at every season close.
 *
 * Posts are stored as finished text in the board's default language, so they
 * stay readable even without the extension.
 */
class forum
{
	protected $db;
	protected $config;
	protected $user;
	protected $auth;
	protected $language_loader;
	protected $ext_manager;
	protected $games;
	protected $points;
	protected $root_path;
	protected $php_ext;

	/** @var \phpbb\language\language|null board default language for post texts */
	protected $post_lang;

	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\user $user, \phpbb\auth\auth $auth, \phpbb\language\language_file_loader $language_loader, games $games, points $points, $root_path, $php_ext)
	{
		$this->db = $db;
		$this->config = $config;
		$this->user = $user;
		$this->auth = $auth;
		$this->language_loader = $language_loader;
		$this->games = $games;
		$this->points = $points;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Bot user row, or false when the integration is not set up.
	 */
	public function bot()
	{
		$bot_id = (int) $this->config['aw_arcade_bot_id'];

		if (!$bot_id)
		{
			return false;
		}

		$result = $this->db->sql_query('SELECT * FROM ' . USERS_TABLE . ' WHERE user_id = ' . (int) $bot_id);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row ?: false;
	}

	public function forum_exists($forum_id)
	{
		if (!$forum_id)
		{
			return false;
		}

		$result = $this->db->sql_query('SELECT forum_id FROM ' . FORUMS_TABLE . ' WHERE forum_id = ' . (int) $forum_id . ' AND forum_type = ' . FORUM_POST);
		$found = (bool) $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $found;
	}

	/**
	 * Is anything posted at all (bot set, default forum set)?
	 */
	public function enabled()
	{
		return (int) $this->config['aw_arcade_bot_id'] > 0 && (int) $this->config['aw_arcade_news_forum'] > 0;
	}

	public function posts_games()
	{
		return $this->enabled() && !empty($this->config['aw_arcade_post_games']);
	}

	public function posts_season()
	{
		return $this->enabled() && !empty($this->config['aw_arcade_post_season']);
	}

	/**
	 * Target forum for a category: its own news forum or the default one.
	 */
	public function forum_for_cat($cat)
	{
		$own = is_array($cat) ? (int) (isset($cat['cat_news_forum']) ? $cat['cat_news_forum'] : 0) : 0;

		return $own ?: (int) $this->config['aw_arcade_news_forum'];
	}

	/* ---------------------------------------------------------------
	 * New games
	 * ------------------------------------------------------------- */

	/**
	 * Announce installed games. Games that already have a topic are skipped.
	 * Per target forum: from aw_arcade_post_batch games on, one collective topic.
	 *
	 * @param int[] $game_ids
	 * @param bool $force also when "post new games" is switched off (ACP bulk action)
	 * @return int number of created topics
	 */
	public function announce_games(array $game_ids, $force = false)
	{
		if (!$game_ids || !($force ? $this->enabled() : $this->posts_games()))
		{
			return 0;
		}

		$sql = 'SELECT g.*, c.cat_name, c.cat_news_forum
			FROM ' . $this->games->games_table() . ' g
			LEFT JOIN ' . $this->games->cats_table() . ' c ON c.cat_id = g.cat_id
			WHERE ' . $this->db->sql_in_set('g.game_id', array_map('intval', $game_ids)) . '
				AND g.game_active = 1
				AND g.game_topic_id = 0
			ORDER BY g.game_name ASC';
		$result = $this->db->sql_query($sql);
		$by_forum = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$by_forum[$this->forum_for_cat($row)][] = $row;
		}
		$this->db->sql_freeresult($result);

		$batch = (int) $this->config['aw_arcade_post_batch'];
		$topics = 0;

		foreach ($by_forum as $forum_id => $games)
		{
			if (!$this->forum_exists($forum_id))
			{
				continue;
			}

			if ($batch > 0 && count($games) >= $batch)
			{
				$topic_id = $this->post($forum_id, $this->lang('AW_ARCADE_POST_GAMES_TITLE', count($games)), $this->collective_text($games));
				$topics += $topic_id ? 1 : 0;

				foreach ($games as $game)
				{
					$topic_id && $this->games->update_game($game['game_id'], ['game_topic_id' => $topic_id]);
				}

				continue;
			}

			foreach ($games as $game)
			{
				$topic_id = $this->post($forum_id, $this->lang('AW_ARCADE_POST_GAME_TITLE', $game['game_name']), $this->game_text($game));

				if ($topic_id)
				{
					$this->games->update_game($game['game_id'], ['game_topic_id' => $topic_id]);
					$topics++;
				}
			}
		}

		return $topics;
	}

	protected function game_text(array $game)
	{
		$lines = [];
		$image = $this->image_url($game);

		if ($image !== '')
		{
			$lines[] = '[img]' . $image . '[/img]';
			$lines[] = '';
		}

		$lines[] = '[b]' . $this->lang('AW_ARCADE_POST_GAME') . ':[/b] ' . $game['game_name'];

		if (!empty($game['cat_name']))
		{
			$lines[] = '[b]' . $this->lang('AW_ARCADE_POST_CATEGORY') . ':[/b] [url=' . $this->url('arcadehall/cat/' . (int) $game['cat_id']) . ']' . $game['cat_name'] . '[/url]';
		}

		if (trim((string) $game['game_desc']) !== '')
		{
			$lines[] = '[b]' . $this->lang('AW_ARCADE_POST_DESC') . ':[/b] ' . trim($game['game_desc']);
		}

		$info = [];
		if ($size = $this->dir_size($game))
		{
			$info[] = $this->lang('AW_ARCADE_POST_SIZE', get_formatted_filesize($size));
		}
		$info[] = $this->lang('AW_ARCADE_POST_DIMENSIONS', (int) $game['game_width'], (int) $game['game_height']);
		if (!empty($game['game_touch']))
		{
			$info[] = $this->lang('AW_ARCADE_POST_TOUCH');
		}
		if ((int) $game['game_save_type'] === games::SAVE_NONE)
		{
			$info[] = $this->lang('AW_ARCADE_POST_NO_SCORES');
		}
		$lines[] = '[b]' . $this->lang('AW_ARCADE_POST_INFO') . ':[/b] ' . implode(' · ', $info);

		$lines[] = '';
		$lines[] = '[size=130][b][url=' . $this->url('arcadehall/play/' . (int) $game['game_id']) . ']▶ ' . $this->lang('AW_ARCADE_POST_PLAY') . '[/url][/b][/size]';
		if ((int) $game['game_save_type'] !== games::SAVE_NONE)
		{
			$lines[] = '[url=' . $this->url('arcadehall/scores/' . (int) $game['game_id']) . ']' . $this->lang('AW_ARCADE_POST_SCORES') . '[/url]';
		}
		$lines[] = '';
		$lines[] = '[i]' . $this->lang('AW_ARCADE_POST_DISCUSS') . '[/i]';

		return implode("\n", $lines);
	}

	protected function collective_text(array $games)
	{
		$lines = [$this->lang('AW_ARCADE_POST_GAMES_INTRO', count($games)), ''];

		foreach ($games as $game)
		{
			$line = '[b][url=' . $this->url('arcadehall/play/' . (int) $game['game_id']) . ']' . $game['game_name'] . '[/url][/b]';

			if (!empty($game['cat_name']))
			{
				$line .= ' (' . $game['cat_name'] . ')';
			}

			if (trim((string) $game['game_desc']) !== '')
			{
				$line .= ' – ' . truncate_string(trim($game['game_desc']), 160, 255, false, '…');
			}

			$lines[] = '[*]' . $line;
		}

		return $lines[0] . "\n\n[list]\n" . implode("\n", array_slice($lines, 2)) . "\n[/list]\n\n[i]" . $this->lang('AW_ARCADE_POST_DISCUSS_MANY') . '[/i]';
	}

	/* ---------------------------------------------------------------
	 * Season results
	 * ------------------------------------------------------------- */

	/**
	 * Post the Top 10 at a season close (called before the scores are reset).
	 *
	 * @param array $record_counts user_id => number of records (sorted)
	 * @param array $users user rows (user_id => username, user_colour)
	 * @param array $prizes user_id => paid amount
	 * @param int $season_start
	 * @return int topic id or 0
	 */
	public function announce_season(array $record_counts, array $users, array $prizes, $season_start)
	{
		if (!$this->posts_season() || !$this->forum_exists((int) $this->config['aw_arcade_news_forum']))
		{
			return 0;
		}

		$lang = $this->post_language();
		$start = $this->user->format_date((int) $season_start, 'd.m.Y');
		$end = $this->user->format_date(time(), 'd.m.Y');

		$lines = [$this->lang('AW_ARCADE_POST_SEASON_INTRO', $start, $end), ''];

		$place = 0;
		$list = [];
		foreach (array_slice($record_counts, 0, 10, true) as $user_id => $count)
		{
			$place++;
			$name = isset($users[$user_id]) ? $users[$user_id]['username'] : '?';
			$colour = isset($users[$user_id]['user_colour']) ? (string) $users[$user_id]['user_colour'] : '';
			$name = $colour !== '' ? '[color=#' . $colour . ']' . $name . '[/color]' : $name;
			$entry = $place . '. [b][url=' . $this->url('arcadehall/user/' . (int) $user_id) . ']' . $name . '[/url][/b] – ' . $lang->lang('AW_ARCADE_POST_RECORDS', (int) $count);

			if (!empty($prizes[$user_id]))
			{
				$entry .= ' – ' . $this->lang('AW_ARCADE_POST_PRIZE', $this->points->format($prizes[$user_id]), $this->points->name());
			}

			$list[] = $entry;
		}

		if (!$list)
		{
			$list[] = $this->lang('AW_ARCADE_POST_SEASON_EMPTY');
		}

		$lines = array_merge($lines, $list);

		if ($prizes && $this->points->active())
		{
			$lines[] = '';
			$lines[] = $this->lang('AW_ARCADE_POST_SEASON_PRIZES', $this->points->format(array_sum($prizes)), $this->points->name(), count($prizes));
		}

		$lines[] = '';
		$lines[] = $this->lang('AW_ARCADE_POST_SEASON_NEXT') . ' [url=' . $this->url('arcadehall') . ']' . $this->lang('AW_ARCADE_POST_TO_ARCADE') . '[/url]';

		return $this->post((int) $this->config['aw_arcade_news_forum'], $this->lang('AW_ARCADE_POST_SEASON_TITLE', $end), implode("\n", $lines));
	}

	/* ---------------------------------------------------------------
	 * Tournaments
	 * ------------------------------------------------------------- */

	public function posts_tourneys()
	{
		return $this->enabled() && !empty($this->config['aw_arcade_post_tourneys']);
	}

	/**
	 * Topic "[Turnier] Name" when a tournament starts.
	 *
	 * @param array $tourney
	 * @param array $games games that may be named in public (no password, no group limit)
	 * @param int $hidden number of further games that are not named
	 * @param array $prizes [1 => amount, 2 => ..., 3 => ...] (only amounts > 0)
	 * @return int topic id or 0
	 */
	public function announce_tourney_start(array $tourney, array $games, $hidden, array $prizes)
	{
		if (!$this->posts_tourneys() || !$this->forum_exists((int) $this->config['aw_arcade_news_forum']))
		{
			return 0;
		}

		$url = $this->url('arcadehall/tourney/' . (int) $tourney['tourney_id']);
		$lines = [
			$this->lang('AW_ARCADE_POST_TOURNEY_INTRO', $this->user->format_date((int) $tourney['tourney_start'], 'd.m.Y H:i'), $this->user->format_date((int) $tourney['tourney_end'], 'd.m.Y H:i')),
		];

		if ((string) $tourney['tourney_desc'] !== '')
		{
			$lines[] = '';
			$lines[] = $tourney['tourney_desc'];
		}

		$lines[] = '';
		$lines[] = '[b]' . $this->lang('AW_ARCADE_POST_TOURNEY_GAMES') . '[/b]';

		$list = [];
		foreach ($games as $game)
		{
			$list[] = '[*][url=' . $this->url('arcadehall/play/' . (int) $game['game_id']) . ']' . $game['game_name'] . '[/url]';
		}

		if ($hidden > 0)
		{
			$list[] = '[*]' . $this->post_language()->lang('AW_ARCADE_POST_TOURNEY_MORE', (int) $hidden);
		}

		$lines[] = '[list]' . implode('', $list) . '[/list]';
		$lines[] = $this->lang('AW_ARCADE_POST_TOURNEY_RULES');

		if ($prizes && $this->points->active())
		{
			$lines[] = '';
			$lines[] = '[b]' . $this->lang('AW_ARCADE_POST_TOURNEY_PRIZES') . '[/b]';
			foreach ($prizes as $place => $amount)
			{
				$lines[] = $this->lang('AW_ARCADE_POST_TOURNEY_PRIZE_LINE', (int) $place, $this->points->format($amount), $this->points->name());
			}
		}

		$lines[] = '';
		$lines[] = '[url=' . $url . ']' . $this->lang('AW_ARCADE_POST_TOURNEY_TO') . '[/url] – ' . $this->lang('AW_ARCADE_POST_TOURNEY_GOOD_LUCK');

		return $this->post((int) $this->config['aw_arcade_news_forum'], $this->lang('AW_ARCADE_POST_TOURNEY_TITLE', $tourney['tourney_name']), implode("\n", $lines));
	}

	/**
	 * Topic "[Turnierergebnis] Name" when a tournament ends.
	 *
	 * @param array $overall overall ranking (rows from tourneys::standings)
	 * @param array $paid user_id => paid amount
	 * @return int topic id or 0
	 */
	public function announce_tourney_result(array $tourney, array $overall, array $paid)
	{
		if (!$this->posts_tourneys() || !$this->forum_exists((int) $this->config['aw_arcade_news_forum']))
		{
			return 0;
		}

		$lang = $this->post_language();
		$lines = [$this->lang('AW_ARCADE_POST_TOURNEY_RESULT_INTRO', $tourney['tourney_name']), ''];

		foreach (array_slice($overall, 0, 10) as $row)
		{
			$name = (string) $row['user_colour'] !== '' ? '[color=#' . $row['user_colour'] . ']' . $row['username'] . '[/color]' : $row['username'];
			$entry = (int) $row['rank'] . '. [b][url=' . $this->url('arcadehall/user/' . (int) $row['user_id']) . ']' . $name . '[/url][/b] – ' . $lang->lang('AW_ARCADE_POST_TOURNEY_POINTS', (int) $row['points']);

			if (!empty($paid[(int) $row['user_id']]))
			{
				$entry .= ' – ' . $this->lang('AW_ARCADE_POST_PRIZE', $this->points->format($paid[(int) $row['user_id']]), $this->points->name());
			}

			$lines[] = $entry;
		}

		if (!$overall)
		{
			$lines[] = $this->lang('AW_ARCADE_POST_TOURNEY_EMPTY');
		}

		$lines[] = '';
		$lines[] = '[url=' . $this->url('arcadehall/tourney/' . (int) $tourney['tourney_id']) . ']' . $this->lang('AW_ARCADE_POST_TOURNEY_FULL') . '[/url]';

		return $this->post((int) $this->config['aw_arcade_news_forum'], $this->lang('AW_ARCADE_POST_TOURNEY_RESULT_TITLE', $tourney['tourney_name']), implode("\n", $lines));
	}

	/* ---------------------------------------------------------------
	 * Discussion link on the play page
	 * ------------------------------------------------------------- */

	/**
	 * URL of the game's topic if it exists and the current user may read it.
	 */
	public function topic_url(array $game)
	{
		$topic_id = isset($game['game_topic_id']) ? (int) $game['game_topic_id'] : 0;

		if (!$topic_id)
		{
			return '';
		}

		$result = $this->db->sql_query('SELECT forum_id, topic_visibility FROM ' . TOPICS_TABLE . ' WHERE topic_id = ' . (int) $topic_id);
		$topic = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$topic || (int) $topic['topic_visibility'] !== ITEM_APPROVED || !$this->auth->acl_get('f_read', (int) $topic['forum_id']))
		{
			return '';
		}

		return append_sid($this->root_path . 'viewtopic.' . $this->php_ext, 't=' . $topic_id);
	}

	/* ---------------------------------------------------------------
	 * Posting
	 * ------------------------------------------------------------- */

	/**
	 * Create a topic as the bot user.
	 *
	 * @return int topic id (0 on failure)
	 */
	protected function post($forum_id, $subject, $text)
	{
		$bot = $this->bot();

		if (!$bot || !$this->forum_exists($forum_id))
		{
			return 0;
		}

		if (!function_exists('submit_post'))
		{
			include $this->root_path . 'includes/functions_posting.' . $this->php_ext;
		}

		$subject = truncate_string($subject, 120);
		$message = utf8_normalize_nfc($text);
		$uid = $bitfield = '';
		$flags = 0;
		generate_text_for_storage($message, $uid, $bitfield, $flags, true, true, true);

		$result = $this->db->sql_query('SELECT forum_name FROM ' . FORUMS_TABLE . ' WHERE forum_id = ' . (int) $forum_id);
		$forum_name = (string) $this->db->sql_fetchfield('forum_name');
		$this->db->sql_freeresult($result);

		$data = [
			'forum_id'				=> (int) $forum_id,
			'topic_id'				=> 0,
			'icon_id'				=> 0,
			'enable_bbcode'			=> true,
			'enable_smilies'		=> true,
			'enable_urls'			=> true,
			'enable_sig'			=> false,
			'message'				=> $message,
			'message_md5'			=> md5($message),
			'bbcode_bitfield'		=> $bitfield,
			'bbcode_uid'			=> $uid,
			'post_edit_locked'		=> 0,
			'topic_title'			=> $subject,
			'notify_set'			=> false,
			'notify'				=> false,
			'post_time'				=> 0,
			'forum_name'			=> $forum_name,
			'enable_indexing'		=> true,
			'force_approved_state'	=> ITEM_APPROVED,
			'force_visibility'		=> ITEM_APPROVED,
			// Some extensions read their own flags from these data without checking
			// (e.g. "Markdown" by alfredoramos): bot posts use BBCode only.
			'enable_markdown'		=> false,
		];
		$poll = [];

		// submit_post() takes the poster from $user: act as the bot for this one call.
		$backup = $this->user->data;
		$this->user->data = array_merge($this->user->data, $bot, ['is_registered' => true, 'is_bot' => false]);

		try
		{
			submit_post('post', $subject, $bot['username'], POST_NORMAL, $poll, $data);
		}
		finally
		{
			$this->user->data = $backup;
		}

		return (int) $data['topic_id'];
	}

	/* ---------------------------------------------------------------
	 * Helpers
	 * ------------------------------------------------------------- */

	/**
	 * Language of the board (not of whoever triggered the post).
	 */
	protected function post_language()
	{
		if ($this->post_lang === null)
		{
			$this->post_lang = new \phpbb\language\language($this->language_loader);
			$this->post_lang->set_user_language($this->config['default_lang']);
			$this->post_lang->set_default_language($this->config['default_lang']);
			$this->post_lang->add_lang('posts', 'arcadewelten/arcade');
		}

		return $this->post_lang;
	}

	protected function lang(...$args)
	{
		return $this->post_language()->lang(...$args);
	}

	/**
	 * Absolute arcade URL (works from the ACP and from cron as well).
	 */
	protected function url($path)
	{
		return generate_board_url() . '/' . (!empty($this->config['enable_mod_rewrite']) ? '' : 'app.' . $this->php_ext . '/') . $path;
	}

	protected function image_url(array $game)
	{
		if ((string) $game['game_image'] === '')
		{
			return '';
		}

		return generate_board_url() . '/' . $this->games->games_path() . rawurlencode($game['game_dir']) . '/' . rawurlencode($game['game_image']);
	}

	protected function dir_size(array $game)
	{
		$dir = $this->games->games_fs_path() . $game['game_dir'];

		if ($game['game_dir'] === '' || !is_dir($dir))
		{
			return 0;
		}

		$size = 0;
		foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)) as $file)
		{
			$size += $file->getSize();
		}

		return $size;
	}
}
