<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\core;

/**
 * Builds the arcade section for the board's privacy policy.
 *
 * The text follows the current settings: guest play, ratings, plausibility
 * check, recently played list, category passwords (cookie), Ultimate Points and diagnostic mode.
 */
class privacy
{
	const ANCHOR = 'aw-arcade-privacy';

	protected $db;
	protected $config;
	protected $language;
	protected $points;
	protected $ratings;
	protected $recent;
	protected $duels;
	protected $forum;
	protected $favorites;
	protected $reports;
	protected $games_table;
	protected $cats_table;

	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, \phpbb\language\language $language, points $points, ratings $ratings, recent $recent, duels $duels, forum $forum, favorites $favorites, reports $reports, $games_table, $cats_table)
	{
		$this->db = $db;
		$this->config = $config;
		$this->language = $language;
		$this->points = $points;
		$this->ratings = $ratings;
		$this->recent = $recent;
		$this->duels = $duels;
		$this->forum = $forum;
		$this->favorites = $favorites;
		$this->reports = $reports;
		$this->games_table = $games_table;
		$this->cats_table = $cats_table;
	}

	/**
	 * Is the automatic section switched on?
	 */
	public function enabled()
	{
		return !empty($this->config['aw_arcade_privacy']);
	}

	/**
	 * Paragraph language keys (with arguments) for the current settings.
	 *
	 * @return array list of [key, arg, ...]
	 */
	public function paragraphs()
	{
		$paragraphs = [
			['AW_ARCADE_PRIVACY_MEMBERS'],
			['AW_ARCADE_PRIVACY_SESSION'],
		];

		if (!empty($this->config['aw_arcade_guest_play']))
		{
			$paragraphs[] = ['AW_ARCADE_PRIVACY_GUESTS'];
		}

		if ($this->recent->enabled())
		{
			$paragraphs[] = ['AW_ARCADE_PRIVACY_RECENT', \arcadewelten\arcade\core\recent::KEEP];
		}

		if ($this->favorites->enabled())
		{
			$paragraphs[] = ['AW_ARCADE_PRIVACY_FAVORITES'];
		}

		if ($this->reports->enabled())
		{
			$paragraphs[] = ['AW_ARCADE_PRIVACY_REPORTS'];
		}

		if ($this->forum->posts_season())
		{
			$paragraphs[] = ['AW_ARCADE_PRIVACY_FORUM'];
		}

		if ($this->duels->enabled())
		{
			$paragraphs[] = ['AW_ARCADE_PRIVACY_DUELS'];
		}

		if (!empty($this->config['aw_arcade_tourneys']))
		{
			$paragraphs[] = [$this->forum->enabled() && !empty($this->config['aw_arcade_post_tourneys']) ? 'AW_ARCADE_PRIVACY_TOURNEYS_POST' : 'AW_ARCADE_PRIVACY_TOURNEYS'];
		}

		if ($this->ratings->enabled())
		{
			$paragraphs[] = ['AW_ARCADE_PRIVACY_RATINGS'];
		}

		if ($this->plausibility_active())
		{
			$paragraphs[] = ['AW_ARCADE_PRIVACY_REVIEW'];
		}

		if ($this->has_passwords())
		{
			$paragraphs[] = ['AW_ARCADE_PRIVACY_COOKIE', $this->config['cookie_name'] . '_awcat_…'];
		}

		if ($this->points->active())
		{
			$paragraphs[] = ['AW_ARCADE_PRIVACY_POINTS', $this->points->name()];
		}

		if (!empty($this->config['aw_arcade_debug']))
		{
			$paragraphs[] = ['AW_ARCADE_PRIVACY_DEBUG'];
		}

		$paragraphs[] = ['AW_ARCADE_PRIVACY_LOCAL'];
		$paragraphs[] = ['AW_ARCADE_PRIVACY_LEGAL'];

		return $paragraphs;
	}

	/**
	 * Section as HTML, in the style of phpBB's own policy text (paragraphs separated by <br><br>).
	 */
	public function html()
	{
		$this->language->add_lang('privacy', 'arcadewelten/arcade');

		$parts = [];
		foreach ($this->paragraphs() as $args)
		{
			$key = array_shift($args);
			$args = array_map(function ($arg) {
				return utf8_htmlspecialchars((string) $arg);
			}, $args);
			$parts[] = $this->language->lang($key, ...$args);
		}

		return '<h3 id="' . self::ANCHOR . '">' . $this->language->lang('AW_ARCADE_PRIVACY_TITLE') . '</h3>'
			. implode('<br><br>', $parts);
	}

	/**
	 * Global minimum play time, or a minimum time / maximum score set for any game.
	 */
	protected function plausibility_active()
	{
		if ((int) $this->config['aw_arcade_min_time'] > 0)
		{
			return true;
		}

		$sql = 'SELECT game_id
			FROM ' . $this->games_table . '
			WHERE game_min_time > 0
				OR game_max_score > 0';
		$result = $this->db->sql_query_limit($sql, 1);
		$found = (bool) $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $found;
	}

	protected function has_passwords()
	{
		$sql = 'SELECT cat_id
			FROM ' . $this->cats_table . "
			WHERE cat_password <> ''";
		$result = $this->db->sql_query_limit($sql, 1);
		$found = (bool) $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $found;
	}
}
