<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\acp;

use arcadewelten\arcade\core\games;

class main_module
{
	public $u_action;
	public $tpl_name;
	public $page_title;

	/** @var \phpbb\template\template */
	protected $template;
	/** @var \phpbb\language\language */
	protected $language;
	/** @var \phpbb\request\request_interface */
	protected $request;
	/** @var \phpbb\config\config */
	protected $config;
	/** @var games */
	protected $games;
	/** @var \arcadewelten\arcade\core\scores */
	protected $scores;
	/** @var \arcadewelten\arcade\core\importer */
	protected $importer;
	/** @var \arcadewelten\arcade\core\access */
	protected $access;
	/** @var \arcadewelten\arcade\core\points */
	protected $points;
	/** @var \arcadewelten\arcade\core\season */
	protected $season;
	/** @var \phpbb\user */
	protected $user;
	/** @var string */
	protected $root_path;

	public function main($id, $mode)
	{
		global $phpbb_container;

		$this->template = $phpbb_container->get('template');
		$this->language = $phpbb_container->get('language');
		$this->request = $phpbb_container->get('request');
		$this->config = $phpbb_container->get('config');
		$this->games = $phpbb_container->get('arcadewelten.arcade.games');
		$this->scores = $phpbb_container->get('arcadewelten.arcade.scores');
		$this->importer = $phpbb_container->get('arcadewelten.arcade.importer');
		$this->access = $phpbb_container->get('arcadewelten.arcade.access');
		$this->points = $phpbb_container->get('arcadewelten.arcade.points');
		$this->season = $phpbb_container->get('arcadewelten.arcade.season');
		$this->user = $phpbb_container->get('user');
		$this->root_path = $phpbb_container->getParameter('core.root_path');

		$this->language->add_lang(['common', 'acp'], 'arcadewelten/arcade');

		$this->tpl_name = 'acp_aw_arcade';
		$this->page_title = 'ACP_AW_ARCADE_' . strtoupper($mode);

		add_form_key('aw_arcade_acp');

		switch ($mode)
		{
			case 'games':
				$this->mode_games();
			break;

			case 'cats':
				$this->mode_cats();
			break;

			case 'review':
				$this->mode_review();
			break;

			default:
				$this->mode_settings();
		}

		$this->template->assign_vars([
			'AW_MODE'	=> $mode,
			'U_ACTION'	=> $this->u_action,
		]);
	}

	protected function check_form()
	{
		if (!check_form_key('aw_arcade_acp'))
		{
			trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
		}
	}

	/* ---------------------------------------------------------------
	 * Settings
	 * ------------------------------------------------------------- */

	protected function mode_settings()
	{
		if ($this->request->variable('action', '') === 'season')
		{
			$this->season_page();
			return;
		}

		if ($this->request->variable('action', '') === 'reset_plays')
		{
			if (confirm_box(true))
			{
				$this->games->reset_plays();
				trigger_error($this->language->lang('ACP_AW_ARCADE_PLAYS_RESET_DONE') . adm_back_link($this->u_action));
			}

			confirm_box(false, $this->language->lang('ACP_AW_ARCADE_PLAYS_RESET_CONFIRM'), build_hidden_fields(['action' => 'reset_plays']));
			redirect($this->u_action);
		}

		if ($this->request->is_set_post('submit'))
		{
			$this->check_form();

			$path = trim(str_replace('\\', '/', $this->request->variable('aw_games_path', '')), '/');

			if ($path === '' || strpos($path, '..') !== false || !preg_match('#^[A-Za-z0-9_\-/]+$#', $path))
			{
				trigger_error($this->language->lang('ACP_AW_ARCADE_PATH_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$this->config->set('aw_arcade_games_path', $path . '/');
			$this->config->set('aw_arcade_guest_play', $this->request->variable('aw_guest_play', 0) ? 1 : 0);
			$this->config->set('aw_arcade_debug', $this->request->variable('aw_debug', 0) ? 1 : 0);
			$this->config->set('aw_arcade_credit', $this->request->variable('aw_credit', 0) ? 1 : 0);
			$this->config->set('aw_arcade_privacy', $this->request->variable('aw_privacy', 0) ? 1 : 0);
			$this->config->set('aw_arcade_min_time', max(0, min(600, $this->request->variable('aw_min_time', 5))));
			$this->config->set('aw_arcade_games_per_page', max(5, min(200, $this->request->variable('aw_games_per_page', 30))));
			$this->config->set('aw_arcade_scores_per_page', max(5, min(200, $this->request->variable('aw_scores_per_page', 25))));

			if ($this->points->available())
			{
				$this->config->set('aw_arcade_points_enable', $this->request->variable('aw_points_enable', 0) ? 1 : 0);

				foreach (\arcadewelten\arcade\core\points::KEYS as $key)
				{
					$this->config->set('aw_arcade_' . $key, max(0, (float) str_replace(',', '.', $this->request->variable('aw_' . $key, '0'))));
				}
			}

			trigger_error($this->language->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
		}

		$this->template->assign_vars([
			'AW_GAMES_PATH'			=> $this->games->games_path(),
			'AW_GAMES_PATH_OK'		=> is_dir($this->games->games_fs_path()),
			'AW_GAMEDATA_OK'		=> is_dir($this->games->games_fs_path() . '../gamedata'),
			'AW_AMOD_BRIDGE_OK'		=> is_file($this->root_path . 'newscore.php'),
			'AW_GUEST_PLAY'			=> (bool) $this->config['aw_arcade_guest_play'],
			'AW_DEBUG'				=> (bool) $this->config['aw_arcade_debug'],
			'AW_CREDIT'				=> (bool) $this->config['aw_arcade_credit'],
			'AW_PRIVACY'			=> (bool) $this->config['aw_arcade_privacy'],
			'U_AW_PRIVACY'			=> generate_board_url() . '/ucp.php?mode=privacy#' . \arcadewelten\arcade\core\privacy::ANCHOR,
			'AW_MIN_TIME'			=> (int) $this->config['aw_arcade_min_time'],
			'AW_GAMES_PER_PAGE'		=> (int) $this->config['aw_arcade_games_per_page'],
			'AW_SCORES_PER_PAGE'	=> (int) $this->config['aw_arcade_scores_per_page'],
			'S_AW_POINTS_AVAILABLE'	=> $this->points->available(),
			'S_AW_POINTS_ENABLE'	=> (bool) $this->config['aw_arcade_points_enable'],
			'AW_POINTS_NAME'		=> $this->points->name(),
			'AW_SEASON_START'		=> $this->user->format_date((int) $this->config['aw_arcade_season_start']),
			'U_AW_SEASON'			=> $this->u_action . '&amp;action=season',
			'U_AW_RESET_PLAYS'		=> $this->u_action . '&amp;action=reset_plays',
		]);

		foreach (\arcadewelten\arcade\core\points::KEYS as $key)
		{
			$this->template->assign_var('AW_' . strtoupper($key), $this->points->format($this->config['aw_arcade_' . $key]));
		}
	}

	protected function season_page()
	{
		$confirmed = confirm_box(true);

		if ($this->request->is_set_post('season_close') || $confirmed)
		{
			$reset = $this->request->variable('season_reset', 0) ? 1 : 0;
			$reset_plays = $this->request->variable('season_reset_plays', 0) ? 1 : 0;

			if ($confirmed)
			{
				$result = $this->season->close($reset, $reset_plays);
				trigger_error($this->language->lang('ACP_AW_ARCADE_SEASON_DONE', $this->points->format($result['paid']), $this->points->name(), $result['players']) . adm_back_link($this->u_action));
			}

			confirm_box(false, $this->language->lang($reset ? 'ACP_AW_ARCADE_SEASON_CONFIRM_RESET' : 'ACP_AW_ARCADE_SEASON_CONFIRM'), build_hidden_fields([
				'action'				=> 'season',
				'season_reset'			=> $reset,
				'season_reset_plays'	=> $reset_plays,
			]));

			redirect($this->u_action . '&amp;action=season');
		}

		$plan = $this->season->preview();

		foreach ($plan['rows'] as $row)
		{
			$this->template->assign_block_vars('season_rows', [
				'GAME'		=> $row['game_name'],
				'PLACE'		=> $row['place'],
				'USER'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'SCORE'		=> $row['score'] + 0,
				'AMOUNT'	=> $this->points->format($row['amount']),
			]);
		}

		$names = [];
		foreach ($plan['rows'] as $row)
		{
			$names[$row['user_id']] = $row;
		}

		foreach ($plan['users'] as $uid => $amount)
		{
			$this->template->assign_block_vars('season_users', [
				'USER'		=> get_username_string('full', $uid, $names[$uid]['username'], $names[$uid]['user_colour']),
				'AMOUNT'	=> $this->points->format($amount),
			]);
		}

		$this->template->assign_vars([
			'S_AW_SEASON'			=> true,
			'S_AW_POINTS_ACTIVE'	=> $this->points->active(),
			'AW_SEASON_TOTAL'		=> $this->points->format($plan['total']),
			'AW_POINTS_NAME'		=> $this->points->name(),
			'AW_SEASON_START'		=> $this->user->format_date((int) $this->config['aw_arcade_season_start']),
			'U_AW_SEASON_ACTION'	=> $this->u_action . '&amp;action=season',
			'U_BACK'				=> $this->u_action,
		]);
	}

	/**
	 * Read the five point fields of a game or category form. Empty = inherit (-1).
	 */
	protected function read_point_fields($prefix)
	{
		$data = [];

		foreach (\arcadewelten\arcade\core\points::KEYS as $key)
		{
			$raw = trim(str_replace(',', '.', $this->request->variable('pt_' . $key, '')));
			$data[$prefix . '_' . $key] = $raw === '' ? -1 : max(0, (float) $raw);
		}

		return $data;
	}

	protected function assign_point_fields(array $row, $prefix)
	{
		$this->template->assign_var('S_AW_POINTS_AVAILABLE', $this->points->available());

		foreach (\arcadewelten\arcade\core\points::KEYS as $key)
		{
			$v = isset($row[$prefix . '_' . $key]) ? (float) $row[$prefix . '_' . $key] : -1;
			$this->template->assign_block_vars('point_fields', [
				'KEY'		=> $key,
				'LABEL'		=> $this->language->lang('ACP_AW_ARCADE_PT_' . strtoupper($key)),
				'VALUE'		=> $v >= 0 ? $v + 0 : '',
				'GLOBAL'	=> $this->points->format($this->config['aw_arcade_' . $key]),
			]);
		}
	}

	/* ---------------------------------------------------------------
	 * Games
	 * ------------------------------------------------------------- */

	protected function mode_games()
	{
		$action = $this->request->variable('action', '');
		$game_id = $this->request->variable('g', 0);

		// Import from the games folder
		if ($this->request->is_set_post('import'))
		{
			$this->check_form();

			$dirs = $this->request->variable('import_dirs', ['']);
			$cat_id = $this->request->variable('import_cat', 0);
			$new_cat = $this->request->variable('import_new_cat', '', true);

			if ($new_cat !== '')
			{
				$cat_id = $this->games->cat_id_by_name($new_cat);
			}

			$count = $this->importer->import(array_filter($dirs), $cat_id, true);

			trigger_error($this->language->lang('ACP_AW_ARCADE_IMPORTED', $count) . adm_back_link($this->u_action));
		}

		// Bulk actions on installed games
		if ($this->request->is_set_post('bulk'))
		{
			$this->check_form();

			$ids = array_map('intval', $this->request->variable('game_ids', [0]));
			$bulk_action = $this->request->variable('bulk_action', '');
			$cat_id = $this->request->variable('bulk_cat', 0);

			foreach (array_filter($ids) as $id)
			{
				switch ($bulk_action)
				{
					case 'activate':
						$this->games->update_game($id, ['game_active' => 1]);
					break;

					case 'deactivate':
						$this->games->update_game($id, ['game_active' => 0]);
					break;

					case 'guest_on':
						$this->games->update_game($id, ['game_guest' => 1]);
					break;

					case 'guest_off':
						$this->games->update_game($id, ['game_guest' => 0]);
					break;

					case 'move':
						$this->games->update_game($id, ['cat_id' => $cat_id]);
					break;
				}
			}

			$this->scores->purge_cache();
			trigger_error($this->language->lang('ACP_AW_ARCADE_UPDATED') . adm_back_link($this->u_action));
		}

		if ($action === 'delete' && $game_id)
		{
			$game = $this->games->get_game($game_id, false);

			if (!$game)
			{
				trigger_error($this->language->lang('AW_ARCADE_NO_GAME') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			if (confirm_box(true))
			{
				$this->scores->delete_game_scores($game_id);
				$this->games->delete_game($game_id);
				trigger_error($this->language->lang('ACP_AW_ARCADE_GAME_DELETED') . adm_back_link($this->u_action));
			}

			confirm_box(false, $this->language->lang('ACP_AW_ARCADE_DELETE_GAME_CONFIRM', $game['game_name']), build_hidden_fields([
				'action'	=> 'delete',
				'g'			=> $game_id,
			]));

			redirect($this->u_action);
		}

		if ($action === 'edit' && $game_id)
		{
			$this->edit_game($game_id);
			return;
		}

		// List installed games
		$start = max(0, $this->request->variable('start', 0));
		$filter_cat = $this->request->variable('fc', -1);
		$search = $this->request->variable('q', '', true);
		$per_page = 50;

		$cat_filter = $filter_cat > 0 ? $filter_cat : 0;
		$total = $this->games->count_games($cat_filter, false, $search);
		$rows = $this->games->list_games($cat_filter, $start, $per_page, false, $search);

		foreach ($rows as $row)
		{
			$this->template->assign_block_vars('games', [
				'ID'			=> (int) $row['game_id'],
				'NAME'			=> $row['game_name'],
				'DIR'			=> $row['game_dir'],
				'CAT'			=> $row['cat_name'],
				'SAVE_TYPE'		=> $this->language->lang('ACP_AW_ARCADE_SAVE_' . (int) $row['game_save_type']),
				'PLAYS'			=> (int) $row['game_plays'],
				'S_ACTIVE'		=> (bool) $row['game_active'],
				'S_GUEST'		=> (bool) $row['game_guest'],
				'U_EDIT'		=> $this->u_action . '&amp;action=edit&amp;g=' . (int) $row['game_id'],
				'U_DELETE'		=> $this->u_action . '&amp;action=delete&amp;g=' . (int) $row['game_id'],
			]);
		}

		global $phpbb_container;
		$pagination = $phpbb_container->get('pagination');
		$base = $this->u_action . ($filter_cat > 0 ? '&amp;fc=' . $filter_cat : '') . ($search !== '' ? '&amp;q=' . urlencode($search) : '');
		$pagination->generate_template_pagination($base, 'pagination', 'start', $total, $per_page, $start);

		// Not yet imported games
		$scan = $this->importer->scan();
		$importable = 0;

		foreach ($scan as $g)
		{
			if ($g['installed'])
			{
				continue;
			}

			if ($g['supported'])
			{
				$importable++;
			}

			$this->template->assign_block_vars('import', [
				'DIR'			=> $g['dir'],
				'NAME'			=> $g['name'],
				'SAVE_TYPE'		=> $this->language->lang('ACP_AW_ARCADE_SAVE_' . (int) $g['save_type']),
				'SIZE'			=> $g['width'] . ' × ' . $g['height'],
				'S_SUPPORTED'	=> $g['supported'],
				'REASON'		=> $g['reason'] !== '' ? $this->language->lang('ACP_AW_ARCADE_REASON_' . $g['reason']) : '',
			]);
		}

		$this->assign_cat_options($cat_filter);

		$this->template->assign_vars([
			'AW_TOTAL_GAMES'	=> $total,
			'AW_FILTER_CAT'		=> $filter_cat,
			'AW_SEARCH'			=> $search,
			'AW_IMPORTABLE'		=> $importable,
			'AW_GAMES_PATH'		=> $this->games->games_path(),
			'S_AW_GAMES_DIR'	=> is_dir($this->games->games_fs_path()),
		]);
	}

	protected function edit_game($game_id)
	{
		$game = $this->games->get_game($game_id, false);

		if (!$game)
		{
			trigger_error($this->language->lang('AW_ARCADE_NO_GAME') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		if ($this->request->is_set_post('submit'))
		{
			$this->check_form();

			$this->games->update_game($game_id, [
				'game_name'			=> $this->request->variable('game_name', '', true),
				'game_desc'			=> $this->request->variable('game_desc', '', true),
				'cat_id'			=> $this->request->variable('cat_id', 0),
				'game_width'		=> max(100, min(4000, $this->request->variable('game_width', 640))),
				'game_height'		=> max(100, min(4000, $this->request->variable('game_height', 480))),
				'game_scoretype'	=> $this->request->variable('game_scoretype', 1) == games::SCORE_LOW ? games::SCORE_LOW : games::SCORE_HIGH,
				'game_save_type'	=> min(4, max(0, $this->request->variable('game_save_type', 0))),
				'game_active'		=> $this->request->variable('game_active', 0) ? 1 : 0,
				'game_guest'		=> $this->request->variable('game_guest', 0) ? 1 : 0,
				'game_min_time'		=> max(0, min(3600, $this->request->variable('game_min_time', 0))),
				'game_max_score'	=> max(0, (float) str_replace(',', '.', $this->request->variable('game_max_score', '0'))),
			] + $this->read_point_fields('game'));

			if ($this->request->variable('reset_scores', 0))
			{
				$this->scores->delete_game_scores($game_id);
			}

			if ($this->request->variable('reset_plays', 0))
			{
				$this->games->reset_plays($game_id);
			}

			$this->scores->purge_cache();

			trigger_error($this->language->lang('ACP_AW_ARCADE_UPDATED') . adm_back_link($this->u_action));
		}

		foreach ([games::SAVE_NONE, games::SAVE_IBPRO, games::SAVE_IBPROV3, games::SAVE_AMOD, games::SAVE_HTML5] as $type)
		{
			$this->template->assign_block_vars('save_types', [
				'VALUE'		=> $type,
				'LABEL'		=> $this->language->lang('ACP_AW_ARCADE_SAVE_' . $type),
				'S_SEL'		=> (int) $game['game_save_type'] === $type,
			]);
		}

		$this->assign_cat_options((int) $game['cat_id']);
		$this->assign_point_fields($game, 'game');

		$this->template->assign_vars([
			'S_AW_EDIT'			=> true,
			'GAME_ID'			=> (int) $game['game_id'],
			'GAME_NAME'			=> $game['game_name'],
			'GAME_DESC'			=> $game['game_desc'],
			'GAME_DIR'			=> $game['game_dir'],
			'GAME_FILE'			=> $game['game_file'],
			'GAME_SCOREVAR'		=> $game['game_scorevar'],
			'GAME_WIDTH'		=> (int) $game['game_width'],
			'GAME_HEIGHT'		=> (int) $game['game_height'],
			'S_LOW_WINS'		=> (int) $game['game_scoretype'] === games::SCORE_LOW,
			'S_GAME_ACTIVE'		=> (bool) $game['game_active'],
			'S_GAME_GUEST'		=> (bool) $game['game_guest'],
			'GAME_MIN_TIME'		=> (int) $game['game_min_time'],
			'GAME_MAX_SCORE'	=> (float) $game['game_max_score'] + 0,
			'AW_MIN_TIME'		=> (int) $this->config['aw_arcade_min_time'],
			'U_EDIT_ACTION'		=> $this->u_action . '&amp;action=edit&amp;g=' . (int) $game['game_id'],
			'U_BACK'			=> $this->u_action,
		]);
	}

	/* ---------------------------------------------------------------
	 * Review list (implausible results)
	 * ------------------------------------------------------------- */

	protected function mode_review()
	{
		$action = $this->request->variable('action', '');
		$flag_id = $this->request->variable('f', 0);

		if (in_array($action, ['approve', 'delete'], true) && $flag_id)
		{
			if (!check_link_hash($this->request->variable('hash', ''), 'aw_arcade_review'))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$flag = $this->scores->get_flag($flag_id);

			if ($flag && $action === 'approve' && ($game = $this->games->get_game($flag['game_id'], false)))
			{
				$this->scores->save_score($game, $flag['user_id'], $flag['score']);
				$this->points->reward_round($flag['user_id'], $game, $flag['score']);
			}

			$this->scores->delete_flag($flag_id);
			redirect($this->u_action);
		}

		$hash = generate_link_hash('aw_arcade_review');

		foreach ($this->scores->get_flagged() as $row)
		{
			$this->template->assign_block_vars('flagged', [
				'GAME'		=> $row['game_name'],
				'USER'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'SCORE'		=> $row['score'] + 0,
				'SECONDS'	=> (int) $row['play_seconds'],
				'REASON'	=> $this->language->lang('ACP_AW_ARCADE_FLAG_' . strtoupper($row['flag_reason'])),
				'DATE'		=> $this->user->format_date($row['flag_time']),
				'U_APPROVE'	=> $this->u_action . '&amp;action=approve&amp;f=' . (int) $row['flag_id'] . '&amp;hash=' . $hash,
				'U_DELETE'	=> $this->u_action . '&amp;action=delete&amp;f=' . (int) $row['flag_id'] . '&amp;hash=' . $hash,
			]);
		}
	}

	/* ---------------------------------------------------------------
	 * Categories
	 * ------------------------------------------------------------- */

	protected function mode_cats()
	{
		$action = $this->request->variable('action', '');
		$cat_id = $this->request->variable('c', 0);

		if ($action === 'add' && $this->request->is_set_post('submit'))
		{
			$this->check_form();
			$name = $this->request->variable('cat_name', '', true);

			if ($name === '')
			{
				trigger_error($this->language->lang('ACP_AW_ARCADE_CAT_NAME_EMPTY') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$new_id = $this->games->add_cat($name);
			$this->save_cat_details($new_id);

			trigger_error($this->language->lang('ACP_AW_ARCADE_UPDATED') . adm_back_link($this->u_action));
		}

		if (in_array($action, ['up', 'down'], true) && $cat_id)
		{
			if (!check_link_hash($this->request->variable('hash', ''), 'aw_arcade_move'))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			$this->games->move_cat($cat_id, $action);
			redirect($this->u_action);
		}

		if ($action === 'delete' && $cat_id)
		{
			$cat = $this->games->get_cat($cat_id);

			if ($cat && confirm_box(true))
			{
				$this->games->delete_cat($cat_id);
				trigger_error($this->language->lang('ACP_AW_ARCADE_UPDATED') . adm_back_link($this->u_action));
			}

			if ($cat)
			{
				confirm_box(false, $this->language->lang('ACP_AW_ARCADE_DELETE_CAT_CONFIRM', $cat['cat_name']), build_hidden_fields([
					'action'	=> 'delete',
					'c'			=> $cat_id,
				]));
			}

			redirect($this->u_action);
		}

		if ($action === 'edit' && $cat_id)
		{
			$cat = $this->games->get_cat($cat_id);

			if (!$cat)
			{
				trigger_error($this->language->lang('AW_ARCADE_NO_CAT') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			if ($this->request->is_set_post('submit'))
			{
				$this->check_form();
				$name = $this->request->variable('cat_name', '', true);

				if ($name === '')
				{
					trigger_error($this->language->lang('ACP_AW_ARCADE_CAT_NAME_EMPTY') . adm_back_link($this->u_action), E_USER_WARNING);
				}

				$this->games->update_cat($cat_id, ['cat_name' => $name]);
				$this->save_cat_details($cat_id);

				trigger_error($this->language->lang('ACP_AW_ARCADE_UPDATED') . adm_back_link($this->u_action));
			}

			$this->assign_point_fields($cat, 'cat');
			$this->assign_cat_images($cat['cat_image']);

			$this->template->assign_vars([
				'S_AW_CAT_EDIT'		=> true,
				'CAT_NAME'			=> $cat['cat_name'],
				'CAT_DESC'			=> $cat['cat_desc'],
				'S_CAT_PROTECTED'	=> $cat['cat_password'] !== '',
				'U_CAT_ACTION'		=> $this->u_action . '&amp;action=edit&amp;c=' . $cat_id,
				'U_BACK'			=> $this->u_action,
			]);

			return;
		}

		$hash = generate_link_hash('aw_arcade_move');

		foreach ($this->games->get_cats() as $cat)
		{
			$id = (int) $cat['cat_id'];
			$this->template->assign_block_vars('cats', [
				'NAME'			=> $cat['cat_name'],
				'DESC'			=> $cat['cat_desc'],
				'COUNT'			=> (int) $cat['game_count'],
				'S_PROTECTED'	=> $cat['cat_password'] !== '',
				'U_EDIT'		=> $this->u_action . '&amp;action=edit&amp;c=' . $id,
				'U_DELETE'		=> $this->u_action . '&amp;action=delete&amp;c=' . $id,
				'U_UP'			=> $this->u_action . '&amp;action=up&amp;c=' . $id . '&amp;hash=' . $hash,
				'U_DOWN'		=> $this->u_action . '&amp;action=down&amp;c=' . $id . '&amp;hash=' . $hash,
			]);
		}

		$this->assign_point_fields([], 'cat');
		$this->assign_cat_images('');
		$this->template->assign_var('U_CAT_ADD', $this->u_action . '&amp;action=add');
	}

	/**
	 * Bundled category images plus the board's own folder images/arcade/cats/.
	 *
	 * @return array path relative to the board root => label
	 */
	protected function cat_image_choices()
	{
		$choices = [];
		$dirs = [
			'ext/arcadewelten/arcade/images/cats/'	=> '',
			'images/arcade/cats/'					=> $this->language->lang('ACP_AW_ARCADE_CAT_IMAGE_OWN') . ': ',
		];

		foreach ($dirs as $dir => $prefix)
		{
			$files = is_dir($this->root_path . $dir) ? scandir($this->root_path . $dir) : [];

			foreach ($files as $file)
			{
				if (preg_match('#^[A-Za-z0-9_\-.]+\.(png|gif|jpe?g|webp|svg)$#i', $file))
				{
					$name = pathinfo($file, PATHINFO_FILENAME);
					$key = 'ACP_AW_ARCADE_IMG_' . strtoupper($name);
					$label = ($prefix === '' && $this->language->is_set($key)) ? $this->language->lang($key) : ucwords(str_replace(['_', '-'], ' ', $name));
					$choices[$dir . $file] = $prefix . $label;
				}
			}
		}

		return $choices;
	}

	protected function assign_cat_images($current)
	{
		$choices = $this->cat_image_choices();
		$custom = ($current !== '' && !isset($choices[$current])) ? $current : '';

		foreach ($choices as $path => $label)
		{
			$this->template->assign_block_vars('cat_images', [
				'PATH'		=> $path,
				'LABEL'		=> $label,
				'URL'		=> $this->root_path . $path,
				'S_SELECTED'	=> $path === $current,
			]);
		}

		$this->template->assign_vars([
			'CAT_IMAGE_CUSTOM'	=> $custom,
			'CAT_IMAGE_PREVIEW'	=> $current !== '' ? $this->root_path . $current : '',
			'AW_BOARD_ROOT'		=> $this->root_path,
		]);
	}

	protected function save_cat_details($cat_id)
	{
		$image = trim(str_replace('\\', '/', $this->request->variable('cat_image_custom', '')), '/');

		if ($image === '')
		{
			$picked = $this->request->variable('cat_image_pick', '');
			$image = isset($this->cat_image_choices()[$picked]) ? $picked : '';
		}

		if ($image !== '' && (strpos($image, '..') !== false || !preg_match('#^[A-Za-z0-9_\-./]+\.(png|gif|jpe?g|webp|svg)$#i', $image)))
		{
			trigger_error($this->language->lang('ACP_AW_ARCADE_CAT_IMAGE_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
		}

		$data = ['cat_desc' => $this->request->variable('cat_desc', '', true), 'cat_image' => $image] + $this->read_point_fields('cat');
		$password = $this->request->variable('cat_password', '', true);

		if ($this->request->variable('cat_password_remove', 0))
		{
			$data['cat_password'] = '';
		}
		else if ($password !== '')
		{
			$data['cat_password'] = $this->access->hash($password);
		}

		$this->games->update_cat($cat_id, $data);
	}

	protected function assign_cat_options($selected)
	{
		foreach ($this->games->get_cats() as $cat)
		{
			$this->template->assign_block_vars('cat_options', [
				'ID'		=> (int) $cat['cat_id'],
				'NAME'		=> $cat['cat_name'],
				'S_SEL'		=> (int) $cat['cat_id'] === (int) $selected,
			]);
		}
	}
}
