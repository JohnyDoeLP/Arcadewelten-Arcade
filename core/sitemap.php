<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\core;

/**
 * sitemap.xml of the arcade for search engines.
 *
 * It always describes what a guest sees, whoever asks for it: the arcade
 * start page, the categories and the games guests may play. Categories
 * limited to user groups guests are not in, categories with a password and
 * games that are not free for guests stay out, and so do the highscores,
 * which need a login anyway.
 */
class sitemap
{
	/** Limit of the sitemap format. */
	const MAX_URLS = 50000;

	protected $config;
	protected $db;
	protected $tourneys;
	protected $games_table;
	protected $cats_table;
	protected $cat_groups_table;

	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, tourneys $tourneys, $games_table, $cats_table, $cat_groups_table)
	{
		$this->config = $config;
		$this->db = $db;
		$this->tourneys = $tourneys;
		$this->games_table = $games_table;
		$this->cats_table = $cats_table;
		$this->cat_groups_table = $cat_groups_table;
	}

	public function enabled()
	{
		return !empty($this->config['aw_arcade_sitemap']);
	}

	/**
	 * Address of the sitemap, for the ACP and robots.txt.
	 */
	public function url()
	{
		return $this->link('/sitemap.xml');
	}

	/**
	 * May guests use the arcade at all? Asked for the guest account, not for
	 * whoever is reading the sitemap.
	 */
	public function guests_may_play()
	{
		$sql = 'SELECT * FROM ' . USERS_TABLE . ' WHERE user_id = ' . (int) ANONYMOUS;
		$result = $this->db->sql_query($sql);
		$guest = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$guest)
		{
			return false;
		}

		$auth = new \phpbb\auth\auth();
		$auth->acl($guest);

		return (bool) $auth->acl_get('u_aw_arcade_play');
	}

	/**
	 * The complete sitemap as XML, or '' when there is nothing to show.
	 */
	public function xml()
	{
		if (!$this->enabled() || !$this->guests_may_play())
		{
			return '';
		}

		$cats = $this->guest_cats();
		$games = $this->guest_games(array_keys($cats));

		if (!$games)
		{
			return '';
		}

		$this->fill_cat_dates($cats, $games);

		$newest = 0;
		foreach ($games as $game)
		{
			$newest = max($newest, (int) $game['game_added']);
		}

		$urls = [[$this->link(''), $newest]];

		if ($this->tourneys->enabled())
		{
			$urls[] = [$this->link('/tourneys'), 0];
		}

		foreach ($cats as $cat_id => $cat)
		{
			if (!empty($cat['newest']))
			{
				$urls[] = [$this->link('/cat/' . (int) $cat_id), $cat['newest']];
			}
		}

		foreach ($games as $game)
		{
			$urls[] = [$this->link('/play/' . (int) $game['game_id']), (int) $game['game_added']];
		}

		$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
			. '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

		foreach (array_slice($urls, 0, self::MAX_URLS) as $url)
		{
			$xml .= "\t" . '<url><loc>' . utf8_htmlspecialchars($url[0]) . '</loc>';

			if ($url[1] > 0)
			{
				$xml .= '<lastmod>' . gmdate('Y-m-d', (int) $url[1]) . '</lastmod>';
			}

			$xml .= '</url>' . "\n";
		}

		return $xml . '</urlset>' . "\n";
	}

	/**
	 * Address of an arcade page, built from the board address in the settings.
	 *
	 * Not from the router: a sitemap must always name the board's own address
	 * without a session id, and it is also built in the ACP, where the router
	 * does not know the address of the page. The paths match config/routing.yml.
	 */
	protected function link($path)
	{
		$app = empty($this->config['enable_mod_rewrite']) ? 'app.php/' : '';

		return generate_board_url() . '/' . $app . 'arcadehall' . $path;
	}

	/**
	 * Categories guests see: no password, and not limited to groups the
	 * guest account is not in.
	 *
	 * @return array cat_id => ['newest' => timestamp]
	 */
	protected function guest_cats()
	{
		$guest_groups = [];
		$sql = 'SELECT group_id FROM ' . USER_GROUP_TABLE . '
			WHERE user_id = ' . (int) ANONYMOUS . '
				AND user_pending = 0';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$guest_groups[] = (int) $row['group_id'];
		}
		$this->db->sql_freeresult($result);

		$limited = [];
		$result = $this->db->sql_query('SELECT cat_id, group_id FROM ' . $this->cat_groups_table);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$limited[(int) $row['cat_id']][] = (int) $row['group_id'];
		}
		$this->db->sql_freeresult($result);

		$cats = [];
		$result = $this->db->sql_query('SELECT cat_id, cat_password FROM ' . $this->cats_table);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$cat_id = (int) $row['cat_id'];

			if ($row['cat_password'] !== '' && $row['cat_password'] !== null)
			{
				continue;
			}

			if (isset($limited[$cat_id]) && !array_intersect($limited[$cat_id], $guest_groups))
			{
				continue;
			}

			$cats[$cat_id] = ['newest' => 0];
		}
		$this->db->sql_freeresult($result);

		return $cats;
	}

	/**
	 * Games guests may play, in the given categories. Fills in the newest
	 * game per category on the way.
	 */
	protected function guest_games(array $cat_ids)
	{
		if (!$cat_ids)
		{
			return [];
		}

		$sql = 'SELECT game_id, cat_id, game_added
			FROM ' . $this->games_table . '
			WHERE game_active = 1
				AND game_guest = 1
				AND ' . $this->db->sql_in_set('cat_id', array_map('intval', $cat_ids)) . '
			ORDER BY game_id ASC';
		$result = $this->db->sql_query_limit($sql, self::MAX_URLS);
		$games = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $games;
	}

	/**
	 * Newest game per category, for the <lastmod> of the category pages.
	 */
	protected function fill_cat_dates(array &$cats, array $games)
	{
		foreach ($games as $game)
		{
			$cat_id = (int) $game['cat_id'];

			if (isset($cats[$cat_id]))
			{
				$cats[$cat_id]['newest'] = max($cats[$cat_id]['newest'], (int) $game['game_added']);
			}
		}
	}
}
