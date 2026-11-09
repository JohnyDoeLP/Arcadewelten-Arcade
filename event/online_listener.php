<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * "Who is online": shows where members, guests and bots are in the arcade.
 *
 * Game names only appear if the viewer may see the game's category and the
 * category has neither a password nor the switch "hide in Who is online".
 */
class online_listener implements EventSubscriberInterface
{
	protected $games;
	protected $access;
	protected $helper;
	protected $language;
	protected $user;

	/** @var array game_id => game row (or false), per request */
	protected $game_cache = [];

	public function __construct(\arcadewelten\arcade\core\games $games, \arcadewelten\arcade\core\access $access, \phpbb\controller\helper $helper, \phpbb\language\language $language, \phpbb\user $user)
	{
		$this->games = $games;
		$this->access = $access;
		$this->helper = $helper;
		$this->language = $language;
		$this->user = $user;
	}

	public static function getSubscribedEvents()
	{
		return [
			'core.viewonline_overwrite_location'	=> 'location',
		];
	}

	public function location($event)
	{
		$page = (string) $event['row']['session_page'];
		$page = preg_replace('#[?\#].*$#', '', $page);

		if (!preg_match('#(?:^|/)arcadehall(/.*)?$#', $page, $m))
		{
			return;
		}

		$path = isset($m[1]) ? trim($m[1], '/') : '';
		$location = $this->language->lang('AW_ARCADE_ONLINE');
		$url = $this->helper->route('arcadewelten_arcade_index');

		if (preg_match('#^(play|scores)/(\d+)$#', $path, $p))
		{
			$game = $this->visible_game((int) $p[2]);

			if ($game)
			{
				$route = $p[1] === 'play' ? 'arcadewelten_arcade_play' : 'arcadewelten_arcade_scores';
				$location = $this->language->lang($p[1] === 'play' ? 'AW_ARCADE_ONLINE_PLAYING' : 'AW_ARCADE_ONLINE_SCORES', $game['game_name']);
				$url = $this->helper->route($route, ['game_id' => (int) $game['game_id']]);
			}
		}
		else if ($path === 'duels' || strpos($path, 'duel/') === 0)
		{
			$location = $this->language->lang('AW_ARCADE_ONLINE_DUELS');
			$url = $this->helper->route('arcadewelten_arcade_duels');
		}
		else if (preg_match('#^user/\d+$#', $path))
		{
			$location = $this->language->lang('AW_ARCADE_ONLINE_RECORDS');
		}

		$event['location'] = $location;
		$event['location_url'] = $url;
	}

	/**
	 * The game, if its name may be shown to the current viewer; otherwise false.
	 */
	protected function visible_game($game_id)
	{
		if (!array_key_exists($game_id, $this->game_cache))
		{
			$this->game_cache[$game_id] = $this->games->get_game($game_id);
		}

		$game = $this->game_cache[$game_id];

		if (!$game)
		{
			return false;
		}

		$cat_id = (int) $game['cat_id'];

		if (!empty($game['cat_password']) || !empty($game['cat_online_hide']) || !$this->access->can_see_cat($cat_id))
		{
			return false;
		}

		$is_member = $this->user->data['user_id'] != ANONYMOUS && empty($this->user->data['is_bot']);

		if (!$is_member && empty($game['game_guest']))
		{
			return false;
		}

		return $game;
	}
}
