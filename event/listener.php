<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	protected $legacy;
	protected $scores;
	protected $ratings;
	protected $recent;
	protected $access;
	protected $duels;
	protected $helper;
	protected $template;
	protected $request;
	protected $user;
	protected $language;
	protected $config;

	/** @var array|null user_id => number of records */
	protected $record_counts;

	public function __construct(\arcadewelten\arcade\core\legacy $legacy, \arcadewelten\arcade\core\scores $scores, \arcadewelten\arcade\core\ratings $ratings, \arcadewelten\arcade\core\recent $recent, \arcadewelten\arcade\core\access $access, \arcadewelten\arcade\core\duels $duels, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\request\request_interface $request, \phpbb\user $user, \phpbb\language\language $language, \phpbb\config\config $config)
	{
		$this->config = $config;
		$this->user = $user;
		$this->language = $language;
		$this->legacy = $legacy;
		$this->scores = $scores;
		$this->ratings = $ratings;
		$this->recent = $recent;
		$this->access = $access;
		$this->duels = $duels;
		$this->helper = $helper;
		$this->template = $template;
		$this->request = $request;
	}

	public static function getSubscribedEvents()
	{
		return [
			'core.common'				=> 'legacy_scores',
			'core.user_setup'			=> 'load_language',
			'core.permissions'			=> 'add_permissions',
			'core.page_header'			=> 'page_header',
			'core.delete_user_after'	=> 'delete_users',
			'core.viewtopic_modify_post_row'	=> 'post_records',
			'core.memberlist_view_profile'		=> 'profile_records',
			'core.index_modify_page_title'		=> 'latest_records',
		];
	}

	/**
	 * Old arcade games send their scores to the board's index.php.
	 */
	public function legacy_scores()
	{
		$script = basename((string) $this->request->server('SCRIPT_NAME', ''));

		if ($script === 'newscore.php')
		{
			// Activity Mod games: root file newscore.php starts phpBB, we take over here.
			$response = $this->legacy->handle(true);
		}
		else if ($script === 'index.php' && $this->legacy->is_legacy_request())
		{
			$response = $this->legacy->handle();
		}
		else if ($script === 'arcade.php')
		{
			// Old links of the JV arcade (arcade.php in the board root, e.g. in old forum posts).
			$response = $this->old_arcade_link();
		}
		else
		{
			return;
		}

		$response->send();

		garbage_collection();
		exit_handler();
	}

	/**
	 * Permanent redirect for old JV arcade links:
	 * arcade.php?mode=stats&u=ID → records page of the member, everything else → arcade.
	 * Game ids of the JV arcade differ from ours, so game links go to the arcade start page.
	 */
	protected function old_arcade_link()
	{
		$mode = $this->request->variable('mode', '');
		$user_id = $this->request->variable('u', 0);

		$url = ($mode === 'stats' && $user_id > 0)
			? $this->helper->route('arcadewelten_arcade_user', ['user_id' => $user_id], false, false, \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL)
			: $this->helper->route('arcadewelten_arcade_index', [], false, false, \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);

		return new \Symfony\Component\HttpFoundation\RedirectResponse($url, 301);
	}

	public function load_language($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name'	=> 'arcadewelten/arcade',
			'lang_set'	=> 'common',
		];
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	 * Permission "Can use the arcade" in the ACP (user permissions, tab "Misc").
	 */
	public function add_permissions($event)
	{
		$permissions = $event['permissions'];
		$permissions['u_aw_arcade_play'] = ['lang' => 'ACL_U_AW_ARCADE_PLAY', 'cat' => 'misc'];
		$event['permissions'] = $permissions;
	}

	public function page_header()
	{
		// Remove expired play sessions at most once per hour (privacy: no data kept longer than needed).
		if (time() - (int) $this->config['aw_arcade_last_cleanup'] > 3600)
		{
			$this->config->set('aw_arcade_last_cleanup', time(), false);
			$this->scores->cleanup_sessions();
		}

		$this->template->assign_vars([
			'U_AW_ARCADE'	=> $this->helper->route('arcadewelten_arcade_index'),
			'S_AW_ARCADE_ALLOWED'	=> $this->access->can_use(),
			'S_AW_CREDIT'	=> !empty($this->config['aw_arcade_credit']),
		]);
	}

	public function delete_users($event)
	{
		$this->scores->delete_users($event['user_ids']);
		$this->ratings->delete_users($event['user_ids']);
		$this->recent->delete_users($event['user_ids']);
		$this->duels->delete_users($event['user_ids']);
	}

	/* ---------------------------------------------------------------
	 * Records in the forum (members only)
	 * ------------------------------------------------------------- */

	protected function is_member()
	{
		return $this->user->data['user_id'] != ANONYMOUS && empty($this->user->data['is_bot']);
	}

	protected function records_of($user_id)
	{
		if ($this->record_counts === null)
		{
			$this->record_counts = $this->scores->record_counts();
		}

		return isset($this->record_counts[(int) $user_id]) ? $this->record_counts[(int) $user_id] : 0;
	}

	public function post_records($event)
	{
		if (!$this->is_member() || $event['poster_id'] == ANONYMOUS || !($count = $this->records_of($event['poster_id'])))
		{
			return;
		}

		$post_row = $event['post_row'];
		$post_row['AW_RECORDS'] = $this->language->lang('AW_ARCADE_RECORDS', $count);
		$post_row['U_AW_RECORDS'] = $this->helper->route('arcadewelten_arcade_user', ['user_id' => (int) $event['poster_id']]);
		$event['post_row'] = $post_row;
	}

	public function profile_records($event)
	{
		if (!$this->is_member())
		{
			return;
		}

		$user_id = (int) $event['member']['user_id'];

		$this->template->assign_vars([
			'AW_PROFILE_RECORDS'	=> $this->records_of($user_id),
			'U_AW_PROFILE_RECORDS'	=> $this->helper->route('arcadewelten_arcade_user', ['user_id' => $user_id]),
		]);
	}

	public function latest_records()
	{
		if (!$this->is_member() || !$this->access->can_use())
		{
			return;
		}

		foreach ($this->scores->latest_records(5, $this->access->hidden_cats()) as $row)
		{
			$this->template->assign_block_vars('aw_latest_records', [
				'USER'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'GAME'		=> $row['game_name'],
				'DATE'		=> $this->user->format_date($row['score_time']),
				'U_SCORES'	=> $this->helper->route('arcadewelten_arcade_scores', ['game_id' => (int) $row['game_id']]),
			]);
		}

		$this->template->assign_var('U_AW_ARCADE_HALL', $this->helper->route('arcadewelten_arcade_index'));
	}
}
