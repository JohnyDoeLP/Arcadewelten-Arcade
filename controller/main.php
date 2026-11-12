<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\controller;

use arcadewelten\arcade\core\games;
use arcadewelten\arcade\core\ratings;
use Symfony\Component\HttpFoundation\JsonResponse;

class main
{
	protected $games;
	protected $scores;
	protected $access;
	protected $points;
	protected $ratings;
	protected $recent;
	protected $duels;
	protected $forum;
	protected $favorites;
	protected $reports;
	protected $config;
	protected $helper;
	protected $template;
	protected $language;
	protected $user;
	protected $request;
	protected $pagination;
	protected $root_path;

	/** @var \arcadewelten\arcade\core\tourneys */
	protected $tourneys;
	/** @var \arcadewelten\arcade\core\tourney_jobs */
	protected $tourney_jobs;
	/** @var \arcadewelten\arcade\core\jackpot */
	protected $jackpot;

	public function __construct(games $games, \arcadewelten\arcade\core\scores $scores, \arcadewelten\arcade\core\access $access, \arcadewelten\arcade\core\points $points, ratings $ratings, \arcadewelten\arcade\core\recent $recent, \arcadewelten\arcade\core\duels $duels, \arcadewelten\arcade\core\forum $forum, \arcadewelten\arcade\core\favorites $favorites, \arcadewelten\arcade\core\reports $reports, \phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\language\language $language, \phpbb\user $user, \phpbb\request\request_interface $request, \phpbb\pagination $pagination, $root_path, \arcadewelten\arcade\core\tourneys $tourneys, \arcadewelten\arcade\core\tourney_jobs $tourney_jobs, \arcadewelten\arcade\core\jackpot $jackpot)
	{
		$this->games = $games;
		$this->scores = $scores;
		$this->access = $access;
		$this->points = $points;
		$this->ratings = $ratings;
		$this->recent = $recent;
		$this->duels = $duels;
		$this->forum = $forum;
		$this->favorites = $favorites;
		$this->reports = $reports;
		$this->config = $config;
		$this->helper = $helper;
		$this->template = $template;
		$this->language = $language;
		$this->user = $user;
		$this->request = $request;
		$this->pagination = $pagination;
		$this->root_path = $root_path;
		$this->tourneys = $tourneys;
		$this->tourney_jobs = $tourney_jobs;
		$this->jackpot = $jackpot;

		$this->language->add_lang('common', 'arcadewelten/arcade');
	}

	/* ---------------------------------------------------------------
	 * Game list
	 * ------------------------------------------------------------- */

	public function index($cat_id)
	{
		$this->check_use();

		$cat_id = (int) $cat_id;
		$cat = $cat_id ? $this->games->get_cat($cat_id) : false;
		$hidden = $this->access->hidden_cats();

		if ($cat_id && (!$cat || in_array($cat_id, $hidden, true)))
		{
			throw new \phpbb\exception\http_exception(404, 'AW_ARCADE_NO_CAT');
		}

		$is_guest = !$this->is_member();
		$all_games = 0;

		if ($is_guest)
		{
			// Guests: protected categories do not exist for them.
			if ($cat && $this->access->is_protected($cat))
			{
				login_box('', $this->language->lang('AW_ARCADE_LOGIN_FOR_CAT'));
			}

			$all_games = $this->games->count_games();
			$this->games->set_guest_mode(true);
		}

		if ($cat && !$this->access->has_access($cat))
		{
			return $this->password_form($cat, $this->helper->route('arcadewelten_arcade_cat', ['cat_id' => $cat_id]));
		}

		$cats = $this->games->get_cats();
		$locked = [];
		$covers = $this->games->cat_cover_images();
		$cat_champs = $is_guest ? [] : $this->scores->category_champions();

		$new_counts = $this->games->count_new_by_cat();

		foreach ($cats as $c)
		{
			$is_locked = !$this->access->has_access($c);
			$cid = (int) $c['cat_id'];

			// Limited to user groups the member is not in: the category does not exist for them.
			if (in_array($cid, $hidden, true))
			{
				continue;
			}

			if ($is_guest && ($this->access->is_protected($c) || !(int) $c['game_count']))
			{
				$locked[] = $cid;
				continue;
			}

			// Empty categories are not shown as tiles (unless it is the one currently opened).
			if (!(int) $c['game_count'] && $cid !== (int) $cat_id)
			{
				continue;
			}

			if ($is_locked)
			{
				$locked[] = $cid;
			}

			// Own image, otherwise the image of the most played game (not for locked categories).
			$image = '';
			if ($c['cat_image'] !== '')
			{
				$image = generate_board_url() . '/' . str_replace('%2F', '/', rawurlencode($c['cat_image']));
			}
			else if (!$is_locked && isset($covers[$cid]))
			{
				$image = $this->game_web_path($covers[$cid], $covers[$cid]['game_image']);
			}

			$champ = (!$is_locked && isset($cat_champs[$cid])) ? $cat_champs[$cid] : false;

			$this->template->assign_block_vars('cats', [
				'NAME'			=> $c['cat_name'],
				'DESC'			=> $c['cat_desc'],
				'COUNT'			=> (int) $c['game_count'],
				'NEW_COUNT'		=> (!$is_locked && isset($new_counts[$cid])) ? $new_counts[$cid] : 0,
				'IMAGE'			=> $image,
				'CHAMPION'		=> $champ ? get_username_string('no_profile', $champ['user_id'], $champ['username'], $champ['user_colour']) : '',
				'CHAMP_RECORDS'	=> $champ ? $champ['records'] : 0,
				'U_CAT'			=> $this->helper->route('arcadewelten_arcade_cat', ['cat_id' => $cid]),
				'S_ACTIVE'		=> $cid === $cat_id,
				'S_PROTECTED'	=> $this->access->is_protected($c),
				'S_GROUPS'		=> (bool) $this->access->get_cat_groups($cid),
				'S_LOCKED'		=> $is_locked,
			]);
		}

		$search = $this->request->variable('q', '', true);
		$sort = $this->request->variable('sort', 'name');
		if (!isset(games::SORTS[$sort]) || ($sort === 'rating' && !$this->ratings->enabled()))
		{
			$sort = 'name';
		}
		$start = max(0, $this->request->variable('start', 0));
		$per_page = max(1, (int) $this->config['aw_arcade_games_per_page']);
		$exclude = array_merge($cat_id ? [] : $locked, $hidden);

		$guests_blocked = $is_guest && !$this->config['aw_arcade_guest_play'];
		$total = $guests_blocked ? 0 : $this->games->count_games($cat_id, true, $search, $exclude);
		$start = $this->pagination->validate_start($start, $per_page, $total);
		$rows = $guests_blocked ? [] : $this->games->list_games($cat_id, $start, $per_page, true, $search, $exclude, $sort);
		$tops = $is_guest ? [] : $this->scores->get_top($rows, 3);

		$fav_ids = (!$is_guest && $this->favorites->enabled()) ? $this->favorites->ids($this->user->data['user_id']) : [];

		foreach ($rows as $row)
		{
			$id = (int) $row['game_id'];
			$top = isset($tops[$id]) ? $tops[$id] : [];
			$champ = $top ? $top[0] : false;

			$this->template->assign_block_vars('games', [
				'NAME'			=> $row['game_name'],
				'IMAGE'			=> $this->game_web_path($row, $row['game_image']),
				'PLAYS'			=> (int) $row['game_plays'],
				'CAT_NAME'		=> $row['cat_name'],
				'CHAMPION'		=> $champ ? get_username_string('full', $champ['user_id'], $champ['username'], $champ['user_colour']) : '',
				'CHAMP_SCORE'	=> $champ ? $this->format_score($champ['score']) : '',
				'S_NO_SCORES'	=> (int) $row['game_save_type'] === games::SAVE_NONE,
				'S_TOUCH'		=> (bool) $row['game_touch'],
				'S_NEW'			=> $this->games->is_new($row),
				'S_FAV'			=> isset($fav_ids[(int) $row['game_id']]),
				'U_FAV'			=> $this->helper->route('arcadewelten_arcade_favorite', ['game_id' => (int) $row['game_id']]),
				'S_GUEST'		=> $is_guest,
				'RATING_COUNT'	=> $this->ratings->enabled() ? (int) $row['game_rating_count'] : 0,
				'RATING_STARS'	=> $this->stars($row),
				'RATING_AVG'	=> $this->format_rating($row),
				'COST'			=> (!$is_guest && $this->points->active() && ($c = $this->points->value($row, 'cost')) > 0) ? $this->points->format($c) . ' ' . $this->points->name() : '',
				'JACKPOT'		=> (!$is_guest && $this->jackpot->applies($row)) ? $this->points->format($this->jackpot->pot($row)) . ' ' . $this->points->name() : '',
				'U_PLAY'		=> $this->helper->route('arcadewelten_arcade_play', ['game_id' => $id]),
				'U_SCORES'		=> $this->helper->route('arcadewelten_arcade_scores', ['game_id' => $id]),
			]);

			foreach ($top as $i => $t)
			{
				$this->template->assign_block_vars('games.top', [
					'PLACE'	=> $i + 1,
					'USER'	=> get_username_string('full', $t['user_id'], $t['username'], $t['user_colour']),
					'SCORE'	=> $this->format_score($t['score']),
				]);
			}
		}

		if ($fav_ids && !$cat_id && $search === '' && !$start)
		{
			foreach ($this->favorites->get($this->user->data['user_id']) as $row)
			{
				if ($row['cat_id'] && (!$this->access->can_see_cat($row['cat_id']) || !$this->access->has_access($row)))
				{
					continue;
				}

				$this->template->assign_block_vars('favorites', [
					'NAME'		=> $row['game_name'],
					'IMAGE'		=> $this->game_web_path($row, $row['game_image']),
					'S_TOUCH'	=> (bool) $row['game_touch'],
					'U_PLAY'	=> $this->helper->route('arcadewelten_arcade_play', ['game_id' => (int) $row['game_id']]),
				]);
			}
		}

		if (!$is_guest && $this->favorites->enabled())
		{
			add_form_key('aw_arcade_fav', '_AW_FAV');
		}

		if (!$is_guest && !$cat_id && $search === '' && !$start && $this->recent->enabled())
		{
			$shown = 0;
			foreach ($this->recent->get($this->user->data['user_id'], \arcadewelten\arcade\core\recent::KEEP) as $row)
			{
				if ($shown >= 6 || ($row['cat_id'] && (!$this->access->can_see_cat($row['cat_id']) || !$this->access->has_access($row))))
				{
					continue;
				}
				$shown++;

				$this->template->assign_block_vars('recent', [
					'NAME'		=> $row['game_name'],
					'IMAGE'		=> $this->game_web_path($row, $row['game_image']),
					'S_TOUCH'	=> (bool) $row['game_touch'],
					'U_PLAY'	=> $this->helper->route('arcadewelten_arcade_play', ['game_id' => (int) $row['game_id']]),
				]);
			}
		}

		if (!$is_guest && !$cat_id && $search === '' && !$start)
		{
			$medals = ['AW_ARCADE_GOLD', 'AW_ARCADE_SILVER', 'AW_ARCADE_BRONZE'];

			foreach ($this->scores->hall_of_fame(3) as $i => $hof)
			{
				$this->template->assign_block_vars('hall', [
					'PLACE'		=> $i + 1,
					'MEDAL'		=> $this->language->lang($medals[$i]),
					'AVATAR'	=> $hof['avatar_row'] ? phpbb_get_user_avatar($hof['avatar_row'], 'USER_AVATAR', false, false) : '',
					'USER'		=> get_username_string('full', $hof['user_id'], $hof['username'], $hof['user_colour']),
					'RECORDS'	=> $hof['records'],
					'U_RECORDS'	=> $this->helper->route('arcadewelten_arcade_user', ['user_id' => (int) $hof['user_id']]),
				]);
			}
		}

		$params = ($search !== '' ? ['q' => $search] : []) + ($sort !== 'name' ? ['sort' => $sort] : []);
		$base_url = $cat_id
			? $this->helper->route('arcadewelten_arcade_cat', ['cat_id' => $cat_id] + $params)
			: $this->helper->route('arcadewelten_arcade_index', $params);

		foreach (array_keys(games::SORTS) as $key)
		{
			if ($key === 'rating' && !$this->ratings->enabled())
			{
				continue;
			}

			$this->template->assign_block_vars('sorts', [
				'KEY'		=> $key,
				'LABEL'		=> $this->language->lang('AW_ARCADE_SORT_' . strtoupper($key)),
				'S_SEL'		=> $key === $sort,
			]);
		}
		$this->pagination->generate_template_pagination($base_url, 'pagination', 'start', $total, $per_page, $start);

		$this->template->assign_vars([
			'AW_CAT_NAME'		=> $cat ? $cat['cat_name'] : '',
			'AW_SEARCH'			=> $search,
			'U_AW_TOURNEYS'		=> $this->tourneys->enabled() ? $this->helper->route('arcadewelten_arcade_tourneys') : '',
			'AW_DUELS_OPEN'		=> (!$is_guest && $this->duels->enabled()) ? $this->duels->count_incoming($this->user->data['user_id']) : 0,
			'U_AW_DUELS'		=> (!$is_guest && $this->duels->enabled()) ? $this->helper->route('arcadewelten_arcade_duels') : '',
			'AW_SORT'			=> $sort,
			'S_AW_FAVORITES'	=> !$is_guest && $this->favorites->enabled(),
			'AW_TOTAL_GAMES'	=> $total,
			'AW_ALL_GAMES'		=> $all_games,
			'AW_MESSAGE'		=> $this->request->variable('guest_done', 0) ? $this->language->lang('AW_ARCADE_MSG_GUEST') : '',
			'S_AW_GUEST'		=> $is_guest,
			'S_AW_GUESTS_BLOCKED'	=> $guests_blocked,
			'U_AW_LOGIN'		=> append_sid($this->root_path . 'ucp.' . $this->php_ext(), 'mode=login&amp;redirect=' . urlencode($this->helper->route('arcadewelten_arcade_index', [], false))),
			'U_AW_REGISTER'		=> append_sid($this->root_path . 'ucp.' . $this->php_ext(), 'mode=register'),
			'U_AW_ARCADE'		=> $this->helper->route('arcadewelten_arcade_index'),
			'U_AW_SEARCH'		=> $cat_id ? $this->helper->route('arcadewelten_arcade_cat', ['cat_id' => $cat_id]) : $this->helper->route('arcadewelten_arcade_index'),
		]);

		$this->assign_running_tourneys();
		$this->breadcrumbs($cat);

		return $this->helper->render('@arcadewelten_arcade/arcade_index.html', $cat ? $cat['cat_name'] : $this->language->lang('AW_ARCADE'));
	}

	/* ---------------------------------------------------------------
	 * Play
	 * ------------------------------------------------------------- */

	public function play($game_id)
	{
		$this->check_use();
		$game = $this->games->get_game($game_id);

		if (!$game || !$this->access->can_see_cat($game['cat_id']))
		{
			throw new \phpbb\exception\http_exception(404, 'AW_ARCADE_NO_GAME');
		}

		$cat = $game['cat_id'] ? ['cat_id' => $game['cat_id'], 'cat_password' => $game['cat_password']] : false;

		if (!$this->is_member() && (!$this->config['aw_arcade_guest_play'] || !$game['game_guest'] || ($cat && $this->access->is_protected($cat))))
		{
			login_box('', $this->language->lang('AW_ARCADE_LOGIN_TO_PLAY'));
		}

		if ($cat && !$this->access->has_access($cat))
		{
			return $this->password_form($this->games->get_cat($game['cat_id']), $this->helper->route('arcadewelten_arcade_play', ['game_id' => (int) $game_id]));
		}

		$is_member = $this->is_member();
		$cost = $this->points->active() ? $this->points->value($game, 'cost') : 0;

		if ($is_member && $cost > 0)
		{
			// Charge only once per confirmed start, not on every page view.
			if (!$this->request->is_set_post('aw_pay'))
			{
				return $this->pay_form($game, $cost);
			}

			if (!check_form_key('aw_arcade_pay'))
			{
				return $this->pay_form($game, $cost, $this->language->lang('FORM_INVALID'));
			}

			if (!$this->points->charge($this->user->data['user_id'], $game))
			{
				return $this->pay_form($game, $cost, $this->language->lang('AW_ARCADE_NOT_ENOUGH_POINTS', $this->points->format($cost), $this->points->name(), $this->points->format($this->points->balance($this->user->data['user_id']))));
			}

			$added = $this->jackpot->on_play($game, $cost);
			$game['game_jackpot_pot'] = $this->jackpot->pot($game) + $added;
		}

		$play_id = $is_member ? $this->scores->start_play($this->user->data['user_id'], $game['game_id']) : '';

		if ($is_member)
		{
			$this->recent->add($this->user->data['user_id'], $game['game_id']);

			if ($this->duels->enabled())
			{
				foreach ($this->duels->incoming_for_game($this->user->data['user_id'], $game['game_id']) as $duel)
				{
					$this->template->assign_block_vars('duels_here', [
						'TEXT'	=> $this->language->lang('AW_ARCADE_DUEL_BANNER',
							get_username_string('full', $duel['challenger_id'], $duel['challenger_name'], $duel['challenger_colour']),
							$this->format_score($duel['challenger_score']),
							$this->user->format_date($duel['deadline'])),
					]);
				}
			}
		}
		// Search engines may look at the page, but they do not count as a play.
		if (empty($this->user->data['is_bot']))
		{
			$this->games->count_play($game['game_id']);
		}

		add_form_key('aw_arcade_play');
		$this->assign_rating($game);

		$board_url = generate_board_url() . '/';
		$type = (int) $game['game_type'];

		if ($is_member && ($this->favorites->enabled() || $this->reports->enabled()))
		{
			add_form_key('aw_arcade_fav', '_AW_FAV');
			add_form_key('aw_arcade_report', '_AW_REPORT');
			foreach (\arcadewelten\arcade\core\reports::REASONS as $reason)
			{
				$this->template->assign_block_vars('report_reasons', [
					'KEY'	=> $reason,
					'LABEL'	=> $this->language->lang('AW_ARCADE_REPORT_' . strtoupper($reason)),
				]);
			}
		}

		$this->template->assign_vars([
			'AW_GAME_NAME'		=> $game['game_name'],
			'AW_GAME_DESC'		=> $game['game_desc'],
			'AW_GAME_WIDTH'		=> (int) $game['game_width'],
			'AW_GAME_HEIGHT'	=> (int) $game['game_height'],
			'AW_GAME_URL'		=> $board_url . $this->games->games_path() . rawurlencode($game['game_dir']) . '/' . rawurlencode($game['game_file']) . $this->cache_buster($game),
			'AW_BOARD_URL'		=> $board_url,
			'AW_RUFFLE_URL'		=> $board_url . 'ext/arcadewelten/arcade/styles/all/theme/ruffle/',
			'AW_PLAY_ID'		=> $play_id,
			'S_AW_FLASH'		=> $type === games::TYPE_FLASH,
			'S_AW_HTML5'		=> $type === games::TYPE_HTML5,
			'S_AW_TOUCH'		=> (bool) $game['game_touch'],
			'U_AW_TOPIC'		=> $this->forum->topic_url($game),
			'S_AW_FAVORITES'	=> $is_member && $this->favorites->enabled(),
			'S_AW_FAV'			=> $is_member && $this->favorites->enabled() && $this->favorites->is_favorite($this->user->data['user_id'], $game['game_id']),
			'U_AW_FAV'			=> $this->helper->route('arcadewelten_arcade_favorite', ['game_id' => (int) $game['game_id']]),
			'S_AW_REPORTS'		=> $is_member && $this->reports->enabled(),
			'U_AW_REPORT'		=> $this->helper->route('arcadewelten_arcade_report', ['game_id' => (int) $game['game_id']]),
			'S_AW_GUEST'		=> !$is_member,
			'S_AW_NO_SCORES'	=> (int) $game['game_save_type'] === games::SAVE_NONE,
			'AW_POINTS_INFO'	=> $this->points_info($game),
			'U_AW_SUBMIT'		=> $this->helper->route('arcadewelten_arcade_submit'),
			'U_AW_GUEST_DONE'	=> $this->helper->route('arcadewelten_arcade_index', ['guest_done' => 1], false),
			'U_AW_SCORES'		=> $this->helper->route('arcadewelten_arcade_scores', ['game_id' => (int) $game['game_id']]),
		]);

		if ($is_member)
		{
			$this->assign_top_scores($game, 10);
		}

		if ($this->tourneys->enabled() && (int) $game['game_save_type'] !== games::SAVE_NONE)
		{
			foreach ($this->tourneys->active_for_game($game['game_id']) as $tourney)
			{
				$upcoming = \arcadewelten\arcade\core\tourneys::status($tourney) === \arcadewelten\arcade\core\tourneys::UPCOMING;

				$this->template->assign_block_vars('game_tourneys', [
					'TEXT'	=> $this->language->lang($upcoming ? 'AW_ARCADE_TOURNEY_GAME_SOON' : 'AW_ARCADE_TOURNEY_GAME_NOW', $tourney['tourney_name'], $this->user->format_date((int) ($upcoming ? $tourney['tourney_start'] : $tourney['tourney_end']))),
					'U_VIEW'	=> $this->helper->route('arcadewelten_arcade_tourney', ['tourney_id' => (int) $tourney['tourney_id']]),
				]);
			}
		}

		$this->breadcrumbs($game['cat_id'] ? ['cat_id' => $game['cat_id'], 'cat_name' => $game['cat_name']] : false, $game);

		return $this->helper->render('@arcadewelten_arcade/arcade_play.html', $game['game_name']);
	}

	/* ---------------------------------------------------------------
	 * Highscores
	 * ------------------------------------------------------------- */

	public function scores($game_id)
	{
		$this->check_use();

		if (!$this->is_member())
		{
			login_box('', $this->language->lang('AW_ARCADE_LOGIN_FOR_SCORES'));
		}

		$game = $this->games->get_game($game_id);

		if (!$game || !$this->access->can_see_cat($game['cat_id']))
		{
			throw new \phpbb\exception\http_exception(404, 'AW_ARCADE_NO_GAME');
		}

		if ($game['cat_id'] && !$this->access->has_access(['cat_id' => $game['cat_id'], 'cat_password' => $game['cat_password']]))
		{
			return $this->password_form($this->games->get_cat($game['cat_id']), $this->helper->route('arcadewelten_arcade_scores', ['game_id' => (int) $game_id]));
		}

		$per_page = max(1, (int) $this->config['aw_arcade_scores_per_page']);
		$total = $this->scores->count_scores($game['game_id']);
		$start = $this->pagination->validate_start(max(0, $this->request->variable('start', 0)), $per_page, $total);

		$this->assign_top_scores($game, $per_page, $start);
		$this->pagination->generate_template_pagination($this->helper->route('arcadewelten_arcade_scores', ['game_id' => (int) $game['game_id']]), 'pagination', 'start', $total, $per_page, $start);

		$saved = $this->request->variable('saved', 0);
		$message = '';

		if ($this->request->variable('flagged', 0))
		{
			$message = $this->language->lang('AW_ARCADE_MSG_FLAGGED');
		}
		else if ($this->request->variable('nosession', 0))
		{
			$message = $this->language->lang('AW_ARCADE_MSG_NOSESSION');
		}
		else if ($this->request->variable('rated', 0))
		{
			$message = $this->language->lang('AW_ARCADE_MSG_RATED');
		}
		else if ($saved)
		{
			$last = $this->request->variable('last', '');
			$message = $this->language->lang($saved == 2 ? 'AW_ARCADE_MSG_NEW_BEST' : 'AW_ARCADE_MSG_SAVED', $this->format_score($last));
			$won = (float) $this->request->variable('jackpot', '0');

			if ($won > 0)
			{
				$message .= ' ' . $this->language->lang('AW_ARCADE_MSG_JACKPOT', $this->points->format($won), $this->points->name());
			}

			$earned = (float) $this->request->variable('earned', '0');

			if ($earned > 0)
			{
				$message .= ' ' . $this->language->lang('AW_ARCADE_MSG_EARNED', $this->points->format($earned), $this->points->name());
			}

			if (($won = $this->request->variable('duel', 0)) > 0)
			{
				$message .= ' ' . $this->language->lang('AW_ARCADE_MSG_DUEL_WON', $won);
			}

			if (($counted = $this->request->variable('tourney', 0)) > 0)
			{
				$message .= ' ' . $this->language->lang('AW_ARCADE_MSG_TOURNEY', $counted);
			}
		}
		else if ($this->request->variable('challenged', 0))
		{
			$message = $this->language->lang('AW_ARCADE_MSG_CHALLENGED', $this->request->variable('opponent', '', true));
		}

		$this->assign_duel_form($game);

		$rank = $this->is_member() ? $this->scores->get_rank($game, $this->user->data['user_id']) : 0;
		$this->assign_rating($game);

		$this->template->assign_vars([
			'AW_GAME_NAME'		=> $game['game_name'],
			'AW_GAME_IMAGE'		=> $this->game_web_path($game, $game['game_image']),
			'AW_GAME_PLAYS'		=> (int) $game['game_plays'],
			'AW_TOTAL_SCORES'	=> $total,
			'AW_MESSAGE'		=> $message,
			'AW_MY_RANK'		=> $rank,
			'S_AW_LOW_WINS'		=> (int) $game['game_scoretype'] === games::SCORE_LOW,
			'U_AW_PLAY'			=> $this->helper->route('arcadewelten_arcade_play', ['game_id' => (int) $game['game_id']]),
			'U_AW_TOPIC'		=> $this->forum->topic_url($game),
		]);

		$this->breadcrumbs($game['cat_id'] ? ['cat_id' => $game['cat_id'], 'cat_name' => $game['cat_name']] : false, $game);

		return $this->helper->render('@arcadewelten_arcade/arcade_scores.html', $this->language->lang('AW_ARCADE_HIGHSCORES') . ' – ' . $game['game_name']);
	}

	/* ---------------------------------------------------------------
	 * Records of one member
	 * ------------------------------------------------------------- */

	public function user($user_id)
	{
		$this->check_use();

		if (!$this->is_member())
		{
			login_box('', $this->language->lang('AW_ARCADE_LOGIN_FOR_SCORES'));
		}

		$sql_user = $this->scores->get_user((int) $user_id);

		if (!$sql_user)
		{
			throw new \phpbb\exception\http_exception(404, 'NO_USER');
		}

		foreach ($this->scores->user_records((int) $user_id) as $row)
		{
			if (!$this->access->can_see_cat($row['cat_id']))
			{
				continue;
			}

			$this->template->assign_block_vars('records', [
				'GAME'		=> $row['game_name'],
				'SCORE'		=> $this->format_score($row['score']),
				'DATE'		=> $this->user->format_date($row['score_time']),
				'U_PLAY'	=> $this->helper->route('arcadewelten_arcade_play', ['game_id' => (int) $row['game_id']]),
				'U_SCORES'	=> $this->helper->route('arcadewelten_arcade_scores', ['game_id' => (int) $row['game_id']]),
			]);
		}

		$this->template->assign_vars([
			'AW_USER'	=> get_username_string('full', $sql_user['user_id'], $sql_user['username'], $sql_user['user_colour']),
		]);

		$this->breadcrumbs(false);
		$this->template->assign_block_vars('navlinks', [
			'FORUM_NAME'	=> $sql_user['username'],
			'U_VIEW_FORUM'	=> $this->helper->route('arcadewelten_arcade_user', ['user_id' => (int) $user_id]),
		]);

		return $this->helper->render('@arcadewelten_arcade/arcade_user.html', $this->language->lang('AW_ARCADE_RECORDS_OF', $sql_user['username']));
	}

	/* ---------------------------------------------------------------
	 * Score API for HTML5 games
	 * ------------------------------------------------------------- */

	public function submit()
	{
		if (!$this->is_member())
		{
			return new JsonResponse(['ok' => false, 'error' => 'guest']);
		}

		if (!check_form_key('aw_arcade_play', 4 * 3600))
		{
			return new JsonResponse(['ok' => false, 'error' => 'form'], 403);
		}

		$play = $this->scores->get_play($this->user->data['user_id'], 0, $this->request->variable('play_id', ''));
		$game = ($play && $this->access->can_use()) ? $this->games->get_game($play['game_id']) : false;

		if (!$game || !$this->access->can_see_cat($game['cat_id']))
		{
			return new JsonResponse(['ok' => false, 'error' => 'session'], 400);
		}

		$score = $this->request->variable('score', 0.0);

		if (($reason = $this->scores->check_plausible($game, $play, $score)) !== '')
		{
			$this->scores->flag($game, $this->user->data['user_id'], $score, $play, $reason);
			$this->scores->end_play($play['play_id']);

			return new JsonResponse([
				'ok'		=> true,
				'flagged'	=> true,
				'redirect'	=> $this->helper->route('arcadewelten_arcade_scores', ['game_id' => (int) $game['game_id'], 'flagged' => 1], false),
			]);
		}

		$result = $this->scores->save_score($game, $this->user->data['user_id'], $score);
		$this->scores->end_play($play['play_id']);
		$earned = $this->points->reward_round($this->user->data['user_id'], $game, $score);

		return new JsonResponse([
			'ok'		=> true,
			'improved'	=> $result['improved'],
			'redirect'	=> $this->helper->route('arcadewelten_arcade_scores', [
				'game_id'	=> (int) $game['game_id'],
				'saved'		=> $result['improved'] ? 2 : 1,
				'last'		=> (string) round($score, 2),
			] + ($earned > 0 ? ['earned' => (string) $earned] : []) + (!empty($result['duels_won']) ? ['duel' => (int) $result['duels_won']] : []) + (!empty($result['tourneys']) ? ['tourney' => (int) $result['tourneys']] : []) + (!empty($result['jackpot']) ? ['jackpot' => (string) $result['jackpot']] : []), false),
		]);
	}

	/**
	 * "Random game" from the header menu: only games the visitor may see,
	 * never from categories with a password.
	 */
	public function random()
	{
		$this->check_use();

		$exclude = $this->access->hidden_cats();
		foreach ($this->games->get_cats() as $cat)
		{
			if ((string) $cat['cat_password'] !== '')
			{
				$exclude[] = (int) $cat['cat_id'];
			}
		}

		$is_guest = !$this->is_member();
		$game_id = ($is_guest && !$this->config['aw_arcade_guest_play']) ? 0 : $this->games->random_game_id($exclude);

		return new \Symfony\Component\HttpFoundation\RedirectResponse($game_id
			? $this->helper->route('arcadewelten_arcade_play', ['game_id' => $game_id], false)
			: $this->helper->route('arcadewelten_arcade_index', [], false));
	}

	/* ---------------------------------------------------------------
	 * Tournaments
	 * ------------------------------------------------------------- */

	/**
	 * List: running, planned and finished tournaments.
	 */
	public function tourneys()
	{
		$this->check_use();
		$this->check_tourneys();

		$players = $this->tourneys->count_players();
		$now = time();

		foreach ($this->tourneys->get_all() as $tourney)
		{
			$status = \arcadewelten\arcade\core\tourneys::status($tourney);
			$id = (int) $tourney['tourney_id'];

			$this->template->assign_block_vars('tourneys_' . $status, [
				'NAME'		=> $tourney['tourney_name'],
				'START'		=> $this->user->format_date((int) $tourney['tourney_start']),
				'END'		=> $this->user->format_date((int) $tourney['tourney_end']),
				'END_TS'	=> (int) $tourney['tourney_end'],
				'START_TS'	=> (int) $tourney['tourney_start'],
				'LEFT'		=> $this->time_left($status === \arcadewelten\arcade\core\tourneys::UPCOMING ? (int) $tourney['tourney_start'] - $now : (int) $tourney['tourney_end'] - $now),
				'GAMES'		=> count($this->visible_tourney_games($id)),
				'PLAYERS'	=> isset($players[$id]) ? $players[$id] : 0,
				'U_VIEW'	=> $this->helper->route('arcadewelten_arcade_tourney', ['tourney_id' => $id]),
			]);
		}

		$this->tourney_breadcrumbs();

		return $this->helper->render('@arcadewelten_arcade/arcade_tourneys.html', $this->language->lang('AW_ARCADE_TOURNEYS'));
	}

	/**
	 * One tournament: countdown, games, overall ranking and places per game.
	 */
	public function tourney($tourney_id)
	{
		$this->check_use();
		$this->check_tourneys();

		$tourney = $this->tourneys->get($tourney_id);

		if (!$tourney)
		{
			throw new \phpbb\exception\http_exception(404, 'AW_ARCADE_TOURNEY_NOT_FOUND');
		}

		$status = \arcadewelten\arcade\core\tourneys::status($tourney);
		$all_games = $this->tourneys->get_games($tourney_id);
		$visible = $this->visible_tourney_games($tourney_id, $all_games);
		$standings = $this->tourneys->standings($tourney, $all_games);
		$me = (int) $this->user->data['user_id'];
		$is_member = $this->is_member();

		foreach ($standings['overall'] as $row)
		{
			$this->template->assign_block_vars('overall', [
				'RANK'		=> (int) $row['rank'],
				'USER'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'POINTS'	=> (int) $row['points'],
				'GAMES'		=> (int) $row['games'],
				'FIRSTS'	=> (int) $row['places'][0],
				'PRIZE'		=> ($row['rank'] <= 3 && $this->points->available() && (float) $tourney['tourney_prize_' . $row['rank']] > 0) ? $this->points->format($tourney['tourney_prize_' . $row['rank']]) . ' ' . $this->points->name() : '',
				'S_ME'		=> $is_member && (int) $row['user_id'] === $me,
			]);
		}

		foreach ($visible as $game_id => $game)
		{
			$rows = isset($standings['games'][$game_id]) ? $standings['games'][$game_id] : [];

			$this->template->assign_block_vars('tgames', [
				'NAME'		=> $game['game_name'],
				'IMAGE'		=> $this->game_web_path($game, $game['game_image']),
				'PLAYERS'	=> count($rows),
				'S_LOW'		=> (int) $game['game_scoretype'] === games::SCORE_LOW,
				'U_PLAY'	=> $this->helper->route('arcadewelten_arcade_play', ['game_id' => (int) $game_id]),
			]);

			foreach (array_slice($rows, 0, 10) as $row)
			{
				$this->template->assign_block_vars('tgames.rows', [
					'PLACE'		=> (int) $row['place'],
					'USER'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
					'SCORE'		=> $this->format_score($row['score']),
					'POINTS'	=> (int) $row['points'],
					'S_ME'		=> $is_member && (int) $row['user_id'] === $me,
				]);
			}
		}

		$prizes = [];
		if ($this->points->available())
		{
			foreach ([1, 2, 3] as $place)
			{
				if ((float) $tourney['tourney_prize_' . $place] > 0)
				{
					$prizes[] = $this->language->lang('AW_ARCADE_TOURNEY_PRIZE_LINE', $place, $this->points->format($tourney['tourney_prize_' . $place]), $this->points->name());
				}
			}
		}

		$this->template->assign_vars([
			'AW_TOURNEY_NAME'		=> $tourney['tourney_name'],
			'AW_TOURNEY_DESC'		=> nl2br($tourney['tourney_desc']),
			'AW_TOURNEY_START'		=> $this->user->format_date((int) $tourney['tourney_start']),
			'AW_TOURNEY_END'		=> $this->user->format_date((int) $tourney['tourney_end']),
			'AW_TOURNEY_STATUS'		=> $status,
			'AW_TOURNEY_TARGET'		=> $status === \arcadewelten\arcade\core\tourneys::UPCOMING ? (int) $tourney['tourney_start'] : (int) $tourney['tourney_end'],
			'AW_TOURNEY_LEFT'		=> $this->time_left(($status === \arcadewelten\arcade\core\tourneys::UPCOMING ? (int) $tourney['tourney_start'] : (int) $tourney['tourney_end']) - time()),
			'AW_TOURNEY_HIDDEN'		=> count($all_games) - count($visible),
			'AW_TOURNEY_PRIZES'		=> implode(' · ', $prizes),
			'AW_TOURNEY_POINTS'		=> implode(', ', \arcadewelten\arcade\core\tourneys::PLACE_POINTS),
		]);

		$this->tourney_breadcrumbs($tourney);

		return $this->helper->render('@arcadewelten_arcade/arcade_tourney.html', $tourney['tourney_name']);
	}

	/**
	 * Banner above the game list.
	 */
	protected function assign_running_tourneys()
	{
		if (!$this->tourneys->enabled())
		{
			return;
		}

		foreach ($this->tourneys->get_running() as $tourney)
		{
			$this->template->assign_block_vars('running_tourneys', [
				'NAME'		=> $tourney['tourney_name'],
				'LEFT'		=> $this->time_left((int) $tourney['tourney_end'] - time()),
				'END'		=> $this->user->format_date((int) $tourney['tourney_end']),
				'U_VIEW'	=> $this->helper->route('arcadewelten_arcade_tourney', ['tourney_id' => (int) $tourney['tourney_id']]),
			]);
		}
	}

	/**
	 * Games of a tournament the current user may see (category groups).
	 * A password does not hide a game here: the category itself asks for it when playing.
	 */
	protected function visible_tourney_games($tourney_id, ?array $games = null)
	{
		$games = $games === null ? $this->tourneys->get_games($tourney_id) : $games;

		return array_filter($games, function ($game) {
			return !empty($game['game_active']) && $this->access->can_see_cat((int) $game['cat_id']);
		});
	}

	protected function check_tourneys()
	{
		if (!$this->tourneys->enabled())
		{
			throw new \phpbb\exception\http_exception(404, 'AW_ARCADE_TOURNEYS_OFF');
		}

		// Start and end what is due right away, without waiting for the cron.
		$this->tourney_jobs->run();
	}

	protected function tourney_breadcrumbs($tourney = false)
	{
		$this->template->assign_block_vars('navlinks', [
			'FORUM_NAME'	=> $this->language->lang('AW_ARCADE'),
			'U_VIEW_FORUM'	=> $this->helper->route('arcadewelten_arcade_index'),
		]);
		$this->template->assign_block_vars('navlinks', [
			'FORUM_NAME'	=> $this->language->lang('AW_ARCADE_TOURNEYS'),
			'U_VIEW_FORUM'	=> $this->helper->route('arcadewelten_arcade_tourneys'),
		]);

		if ($tourney)
		{
			$this->template->assign_block_vars('navlinks', [
				'FORUM_NAME'	=> $tourney['tourney_name'],
				'U_VIEW_FORUM'	=> $this->helper->route('arcadewelten_arcade_tourney', ['tourney_id' => (int) $tourney['tourney_id']]),
			]);
		}
	}

	/**
	 * "2 Tage 5 Std." / "3 Std. 12 Min." / "12 Min."
	 */
	protected function time_left($seconds)
	{
		$seconds = max(0, (int) $seconds);
		$days = intdiv($seconds, 86400);
		$hours = intdiv($seconds % 86400, 3600);
		$minutes = intdiv($seconds % 3600, 60);

		if ($days > 0)
		{
			return $this->language->lang('AW_ARCADE_LEFT_DAYS', $days, $hours);
		}

		if ($hours > 0)
		{
			return $this->language->lang('AW_ARCADE_LEFT_HOURS', $hours, $minutes);
		}

		return $this->language->lang('AW_ARCADE_LEFT_MINUTES', max(1, $minutes));
	}

	/* ---------------------------------------------------------------
	 * Favourites and reports
	 * ------------------------------------------------------------- */

	/**
	 * Heart on/off. AJAX: JSON; without JavaScript: back to the page.
	 */
	public function favorite($game_id)
	{
		$this->check_use();
		$ajax = $this->request->is_ajax();
		$game = $this->games->get_game($game_id);
		$error = '';

		if (!$this->is_member() || !$this->favorites->enabled() || !$game || !$this->access->can_see_cat($game['cat_id']))
		{
			$error = $this->language->lang('AW_ARCADE_NO_GAME');
		}
		else if (!check_form_key('aw_arcade_fav', 4 * 3600))
		{
			$error = $this->language->lang('FORM_INVALID');
		}

		$state = false;
		if ($error === '')
		{
			$state = $this->favorites->toggle($this->user->data['user_id'], $game['game_id']);

			if ($state === null)
			{
				$error = $this->language->lang('AW_ARCADE_FAV_MAX', \arcadewelten\arcade\core\favorites::MAX);
			}
		}

		if ($ajax)
		{
			return new JsonResponse($error === ''
				? ['ok' => true, 'fav' => $state, 'label' => $this->language->lang($state ? 'AW_ARCADE_FAV_REMOVE' : 'AW_ARCADE_FAV_ADD')]
				: ['ok' => false, 'error' => $error], $error === '' ? 200 : 400);
		}

		if ($error !== '')
		{
			trigger_error($error, E_USER_WARNING);
		}

		redirect($this->request->variable('back', '') === 'list'
			? $this->helper->route('arcadewelten_arcade_index')
			: $this->helper->route('arcadewelten_arcade_play', ['game_id' => (int) $game['game_id']]));
	}

	/**
	 * "Report game" form on the play page.
	 */
	public function report($game_id)
	{
		$this->check_use();
		$ajax = $this->request->is_ajax();
		$game = $this->games->get_game($game_id);
		$error = '';

		if (!$this->is_member() || !$this->reports->enabled() || !$game || !$this->access->can_see_cat($game['cat_id']))
		{
			$error = $this->language->lang('AW_ARCADE_NO_GAME');
		}
		else if (!check_form_key('aw_arcade_report', 4 * 3600))
		{
			$error = $this->language->lang('FORM_INVALID');
		}

		if ($error === '')
		{
			$this->reports->add($game['game_id'], $this->user->data['user_id'], $this->request->variable('reason', ''), $this->request->variable('text', '', true));
		}

		if ($ajax)
		{
			return new JsonResponse($error === ''
				? ['ok' => true, 'message' => $this->language->lang('AW_ARCADE_REPORT_THANKS')]
				: ['ok' => false, 'error' => $error], $error === '' ? 200 : 400);
		}

		if ($error !== '')
		{
			trigger_error($error, E_USER_WARNING);
		}

		$url = $this->helper->route('arcadewelten_arcade_play', ['game_id' => (int) $game['game_id']]);
		trigger_error($this->language->lang('AW_ARCADE_REPORT_THANKS') . '<br><br><a href="' . $url . '">' . $this->language->lang('AW_ARCADE_BACK_TO_GAME') . '</a>');
	}

	/* ---------------------------------------------------------------
	 * Duels
	 * ------------------------------------------------------------- */

	/**
	 * Overview: duels to answer, own challenges, recent results.
	 */
	public function duels()
	{
		$this->check_use();

		if (!$this->is_member())
		{
			login_box('', $this->language->lang('AW_ARCADE_LOGIN_FOR_DUELS'));
		}

		if (!$this->duels->enabled())
		{
			throw new \phpbb\exception\http_exception(404, 'AW_ARCADE_DUELS_OFF');
		}

		// Close what is overdue right away, without waiting for the cron.
		$this->duels->expire();

		$me = (int) $this->user->data['user_id'];
		$lists = $this->duels->for_user($me);
		add_form_key('aw_arcade_duel');

		foreach ($lists as $block => $rows)
		{
			foreach ($rows as $duel)
			{
				if (!$this->access->can_see_cat($duel['cat_id']))
				{
					continue;
				}

				$i_am_challenger = (int) $duel['challenger_id'] === $me;
				$other = $i_am_challenger
					? get_username_string('full', $duel['opponent_id'], $duel['opponent_name'], $duel['opponent_colour'])
					: get_username_string('full', $duel['challenger_id'], $duel['challenger_name'], $duel['challenger_colour']);

				$result = '';
				if ((int) $duel['duel_status'] === \arcadewelten\arcade\core\duels::DECLINED)
				{
					$result = $this->language->lang('AW_ARCADE_DUEL_DECLINED');
				}
				else if ((int) $duel['duel_status'] === \arcadewelten\arcade\core\duels::FINISHED)
				{
					$result = $this->language->lang((int) $duel['winner_id'] === $me ? 'AW_ARCADE_DUEL_WON' : 'AW_ARCADE_DUEL_LOST');
				}

				$this->template->assign_block_vars($block, [
					'GAME'			=> $duel['game_name'],
					'GAME_IMAGE'	=> $this->game_web_path($duel, $duel['game_image']),
					'OTHER'			=> $other,
					'TO_BEAT'		=> $this->format_score($duel['challenger_score']),
					'OPP_SCORE'		=> $duel['opponent_played'] ? $this->format_score($duel['opponent_score']) : '–',
					'DEADLINE'		=> $this->user->format_date($duel['deadline']),
					'END'			=> $duel['end_time'] ? $this->user->format_date($duel['end_time']) : '',
					'RESULT'		=> $result,
					'S_WON'			=> (int) $duel['winner_id'] === $me,
					'U_PLAY'		=> $this->helper->route('arcadewelten_arcade_play', ['game_id' => (int) $duel['game_id']]),
					'U_SCORES'		=> $this->helper->route('arcadewelten_arcade_scores', ['game_id' => (int) $duel['game_id']]),
					'U_DECLINE'		=> $this->helper->route('arcadewelten_arcade_duel_decline', ['duel_id' => (int) $duel['duel_id']]),
				]);
			}
		}

		$message = '';
		if ($this->request->variable('declined', 0))
		{
			$message = $this->language->lang('AW_ARCADE_MSG_DUEL_DECLINED');
		}

		$this->template->assign_vars([
			'AW_MESSAGE'		=> $message,
			'AW_DUEL_DAYS'		=> $this->duels->max_days(),
			'S_AW_LOW_WINS'		=> false,
		]);

		$this->template->assign_block_vars('navlinks', [
			'FORUM_NAME'	=> $this->language->lang('AW_ARCADE'),
			'U_VIEW_FORUM'	=> $this->helper->route('arcadewelten_arcade_index'),
		]);
		$this->template->assign_block_vars('navlinks', [
			'FORUM_NAME'	=> $this->language->lang('AW_ARCADE_DUELS'),
			'U_VIEW_FORUM'	=> $this->helper->route('arcadewelten_arcade_duels'),
		]);

		return $this->helper->render('@arcadewelten_arcade/arcade_duels.html', $this->language->lang('AW_ARCADE_DUELS'));
	}

	/**
	 * Challenge another member with the own personal best (form on the highscores page).
	 */
	public function duel_new($game_id)
	{
		$this->check_use();
		$game = $this->games->get_game($game_id);

		if (!$this->is_member() || !$this->duels->enabled() || !$game || !$this->access->can_see_cat($game['cat_id']))
		{
			throw new \phpbb\exception\http_exception(404, 'AW_ARCADE_NO_GAME');
		}

		$back = $this->helper->route('arcadewelten_arcade_scores', ['game_id' => (int) $game['game_id']]);
		$fail = function ($key, ...$args) use ($back) {
			trigger_error($this->language->lang($key, ...$args) . '<br><br><a href="' . $back . '">' . $this->language->lang('AW_ARCADE_BACK_TO_SCORES') . '</a>', E_USER_WARNING);
		};

		if (!check_form_key('aw_arcade_duel'))
		{
			$fail('FORM_INVALID');
		}

		$me = (int) $this->user->data['user_id'];
		$score = $this->duels->best_score($game['game_id'], $me);

		if ((int) $game['game_save_type'] === games::SAVE_NONE || $score === null)
		{
			$fail('AW_ARCADE_DUEL_NEED_SCORE');
		}

		global $db;

		$name = trim($this->request->variable('opponent', '', true));
		$sql = 'SELECT user_id, username, user_type FROM ' . USERS_TABLE . "
			WHERE username_clean = '" . $db->sql_escape(utf8_clean_string($name)) . "'";
		$result = $db->sql_query($sql);
		$opponent = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!$opponent || !in_array((int) $opponent['user_type'], [USER_NORMAL, USER_FOUNDER], true))
		{
			$fail('AW_ARCADE_DUEL_NO_USER');
		}

		$opp = (int) $opponent['user_id'];

		if ($opp === $me)
		{
			$fail('AW_ARCADE_DUEL_SELF');
		}

		if (!$this->access->user_can_use($opp) || !$this->access->user_can_see_cat($opp, $game['cat_id']))
		{
			$fail('AW_ARCADE_DUEL_NOT_ALLOWED', $opponent['username']);
		}

		if ($this->duels->exists_open($game['game_id'], $me, $opp))
		{
			$fail('AW_ARCADE_DUEL_EXISTS', $opponent['username']);
		}

		if ($this->duels->count_open_by($me) >= $this->duels->max_open())
		{
			$fail('AW_ARCADE_DUEL_MAX', $this->duels->max_open());
		}

		$days = max(1, min($this->duels->max_days(), $this->request->variable('days', $this->duels->max_days())));
		$this->duels->create($game, $me, $opp, $score, $days);

		redirect($this->helper->route('arcadewelten_arcade_scores', [
			'game_id'		=> (int) $game['game_id'],
			'challenged'	=> 1,
			'opponent'		=> $opponent['username'],
		], false));
	}

	/**
	 * The challenged member declines.
	 */
	public function duel_decline($duel_id)
	{
		$this->check_use();
		$duel = $this->duels->get($duel_id);

		if (!$this->is_member() || !$duel || (int) $duel['opponent_id'] !== (int) $this->user->data['user_id'] || (int) $duel['duel_status'] !== \arcadewelten\arcade\core\duels::OPEN)
		{
			throw new \phpbb\exception\http_exception(404, 'AW_ARCADE_DUEL_NOT_FOUND');
		}

		if (!check_form_key('aw_arcade_duel'))
		{
			trigger_error('FORM_INVALID', E_USER_WARNING);
		}

		$this->duels->decline($duel);

		redirect($this->helper->route('arcadewelten_arcade_duels', ['declined' => 1], false));
	}

	/**
	 * Challenge form on the highscores page (only with an own score).
	 */
	protected function assign_duel_form(array $game)
	{
		if (!$this->duels->enabled() || !$this->is_member() || (int) $game['game_save_type'] === games::SAVE_NONE)
		{
			return;
		}

		$score = $this->duels->best_score($game['game_id'], $this->user->data['user_id']);

		if ($score === null)
		{
			return;
		}

		add_form_key('aw_arcade_duel', '_AW_DUEL');

		$days = [];
		foreach ([1, 3, 7, 14, 30] as $d)
		{
			if ($d <= $this->duels->max_days())
			{
				$days[] = $d;
			}
		}
		if (!in_array($this->duels->max_days(), $days, true))
		{
			$days[] = $this->duels->max_days();
		}

		foreach ($days as $d)
		{
			$this->template->assign_block_vars('duel_days', [
				'DAYS'	=> $d,
				'S_SEL'	=> $d === min(7, $this->duels->max_days()),
			]);
		}

		$this->template->assign_vars([
			'S_AW_DUEL_FORM'	=> true,
			'AW_DUEL_MY_SCORE'	=> $this->format_score($score),
			'U_AW_DUEL_NEW'		=> $this->helper->route('arcadewelten_arcade_duel_new', ['game_id' => (int) $game['game_id']]),
			'U_AW_DUELS'		=> $this->helper->route('arcadewelten_arcade_duels'),
			'U_AW_FIND_USER'	=> append_sid($this->root_path . 'memberlist.' . $this->php_ext(), 'mode=searchuser&amp;form=aw_duel&amp;field=opponent&amp;select_single=true'),
		]);
	}

	/* ---------------------------------------------------------------
	 * Ratings
	 * ------------------------------------------------------------- */

	/**
	 * Store a rating. AJAX: JSON answer; without JavaScript: back to the scores page.
	 */
	public function rate($game_id)
	{
		$ajax = $this->request->is_ajax();
		$game = ($this->ratings->enabled() && $this->access->can_use()) ? $this->games->get_game($game_id) : false;
		$error = '';

		if (!$game || !$this->access->can_see_cat($game['cat_id']))
		{
			$error = 'AW_ARCADE_NO_GAME';
		}
		else if (!$this->is_member())
		{
			$error = 'AW_ARCADE_RATE_LOGIN';
		}
		else if (!check_form_key('aw_arcade_rate', 4 * 3600))
		{
			$error = 'FORM_INVALID';
		}
		else if ($game['cat_id'] && !$this->access->has_access(['cat_id' => $game['cat_id'], 'cat_password' => $game['cat_password']]))
		{
			$error = 'AW_ARCADE_NO_GAME';
		}
		else if (!$this->ratings->can_rate($game['game_id'], $this->user->data['user_id']))
		{
			$error = 'AW_ARCADE_RATE_PLAY_FIRST';
		}

		$rating = $this->request->variable('rating', 0);

		if ($error === '' && ($rating < 1 || $rating > ratings::MAX))
		{
			$error = 'AW_ARCADE_RATE_INVALID';
		}

		if ($error !== '')
		{
			if ($ajax)
			{
				return new JsonResponse(['ok' => false, 'error' => $this->language->lang($error)], 400);
			}

			trigger_error($error, E_USER_WARNING);
		}

		$totals = $this->ratings->rate($game['game_id'], $this->user->data['user_id'], $rating);

		if ($ajax)
		{
			$row = ['game_rating_sum' => $totals['sum'], 'game_rating_count' => $totals['count']];

			return new JsonResponse([
				'ok'		=> true,
				'rating'	=> (int) $rating,
				'stars'		=> $this->stars($row),
				'summary'	=> $this->language->lang('AW_ARCADE_RATING_SUMMARY', $this->format_rating($row), $totals['count']),
				'message'	=> $this->language->lang('AW_ARCADE_MSG_RATED'),
			]);
		}

		redirect($this->helper->route('arcadewelten_arcade_scores', ['game_id' => (int) $game['game_id'], 'rated' => 1], false) . '#aw-rating');
	}

	/**
	 * Rating box on the play and scores pages.
	 */
	protected function assign_rating(array $game)
	{
		if (!$this->ratings->enabled())
		{
			return;
		}

		$is_member = $this->is_member();
		$mine = $is_member ? $this->ratings->get_user_rating($game['game_id'], $this->user->data['user_id']) : 0;
		$can_rate = $is_member && ($mine || $this->ratings->can_rate($game['game_id'], $this->user->data['user_id']));

		if ($can_rate)
		{
			add_form_key('aw_arcade_rate', '_AW_RATE');
		}

		$this->template->assign_vars([
			'S_AW_RATINGS'		=> true,
			'S_AW_CAN_RATE'		=> $can_rate,
			'AW_RATING_STARS'	=> $this->stars($game),
			'AW_RATING_COUNT'	=> (int) $game['game_rating_count'],
			'AW_RATING_SUMMARY'	=> $this->language->lang('AW_ARCADE_RATING_SUMMARY', $this->format_rating($game), (int) $game['game_rating_count']),
			'AW_MY_RATING'		=> $mine,
			'AW_RATING_MAX'		=> ratings::MAX,
			'U_AW_RATE'			=> $this->helper->route('arcadewelten_arcade_rate', ['game_id' => (int) $game['game_id']]),
		]);
	}

	/**
	 * Average rounded to half stars, for the star display (0–5).
	 */
	protected function stars(array $game)
	{
		return round(ratings::average($game) * 2) / 2;
	}

	protected function format_rating(array $game)
	{
		return number_format(ratings::average($game), 1, $this->language->lang('AW_ARCADE_DEC_POINT'), '');
	}

	/* ---------------------------------------------------------------
	 * Helpers
	 * ------------------------------------------------------------- */

	protected function password_form($cat, $u_return)
	{
		if (!$cat)
		{
			throw new \phpbb\exception\http_exception(404, 'AW_ARCADE_NO_CAT');
		}

		$error = '';

		if ($this->request->is_set_post('aw_unlock'))
		{
			if (!check_form_key('aw_arcade_unlock'))
			{
				$error = $this->language->lang('FORM_INVALID');
			}
			else if ($this->access->unlock($cat, $this->request->variable('aw_password', '', true)))
			{
				redirect($u_return);
			}
			else
			{
				$error = $this->language->lang('AW_ARCADE_WRONG_PASSWORD');
			}
		}

		add_form_key('aw_arcade_unlock');

		$this->template->assign_vars([
			'AW_CAT_NAME'	=> $cat['cat_name'],
			'AW_ERROR'		=> $error,
			'U_AW_ACTION'	=> $u_return,
		]);

		$this->breadcrumbs($cat);

		return $this->helper->render('@arcadewelten_arcade/arcade_password.html', $cat['cat_name']);
	}

	protected function pay_form(array $game, $cost, $error = '')
	{
		add_form_key('aw_arcade_pay');

		$this->template->assign_vars([
			'AW_GAME_NAME'	=> $game['game_name'],
			'AW_COST'		=> $this->points->format($cost),
			'AW_BALANCE'	=> $this->points->format($this->points->balance($this->user->data['user_id'])),
			'AW_POINTS_NAME'	=> $this->points->name(),
			'AW_POINTS_INFO'	=> $this->points_info($game),
			'AW_ERROR'		=> $error,
			'U_AW_ACTION'	=> $this->helper->route('arcadewelten_arcade_play', ['game_id' => (int) $game['game_id']]),
			'U_AW_ARCADE'	=> $this->helper->route('arcadewelten_arcade_index'),
		]);

		$this->breadcrumbs($game['cat_id'] ? ['cat_id' => $game['cat_id'], 'cat_name' => $game['cat_name']] : false, $game);

		return $this->helper->render('@arcadewelten_arcade/arcade_pay.html', $game['game_name']);
	}

	/**
	 * Short text with cost and rewards of a game, empty if points are off.
	 */
	protected function points_info(array $game)
	{
		if (!$this->points->active() || !$this->is_member())
		{
			return '';
		}

		$name = $this->points->name();
		$parts = [];

		if (($v = $this->points->value($game, 'cost')) > 0)
		{
			$parts[] = $this->language->lang('AW_ARCADE_INFO_COST', $this->points->format($v), $name);
		}

		if ((int) $game['game_save_type'] !== games::SAVE_NONE)
		{
			if (($v = $this->points->value($game, 'reward_play')) > 0)
			{
				$parts[] = $this->language->lang('AW_ARCADE_INFO_ROUND', $this->points->format($v), $name);
			}

			$r = [];
			foreach ([1, 2, 3] as $place)
			{
				$r[] = $this->points->format($this->points->value($game, 'reward_' . $place));
			}

			if (array_sum(array_map('floatval', [$this->points->value($game, 'reward_1'), $this->points->value($game, 'reward_2'), $this->points->value($game, 'reward_3')])) > 0)
			{
				$parts[] = $this->language->lang('AW_ARCADE_INFO_SEASON', $r[0], $r[1], $r[2], $name);
			}

			if ($this->jackpot->applies($game))
			{
				$parts[] = $this->language->lang('AW_ARCADE_INFO_JACKPOT', $this->points->format($this->jackpot->pot($game)), $name);
			}
		}

		return implode(' • ', $parts);
	}

	protected function assign_top_scores(array $game, $limit, $start = 0)
	{
		$rank = $start;

		foreach ($this->scores->get_scores($game, $start, $limit) as $row)
		{
			$rank++;
			$this->template->assign_block_vars('scores', [
				'RANK'		=> $rank,
				'USER'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'SCORE'		=> $this->format_score($row['score']),
				'DATE'		=> $this->user->format_date($row['score_time']),
				'PLAYS'		=> (int) $row['score_plays'],
				'S_ME'		=> (int) $row['user_id'] === (int) $this->user->data['user_id'],
			]);
		}
	}

	protected function breadcrumbs($cat, $game = false)
	{
		$this->template->assign_block_vars('navlinks', [
			'FORUM_NAME'	=> $this->language->lang('AW_ARCADE'),
			'U_VIEW_FORUM'	=> $this->helper->route('arcadewelten_arcade_index'),
		]);

		if ($cat)
		{
			$this->template->assign_block_vars('navlinks', [
				'FORUM_NAME'	=> $cat['cat_name'],
				'U_VIEW_FORUM'	=> $this->helper->route('arcadewelten_arcade_cat', ['cat_id' => (int) $cat['cat_id']]),
			]);
		}

		if ($game)
		{
			$this->template->assign_block_vars('navlinks', [
				'FORUM_NAME'	=> $game['game_name'],
				'U_VIEW_FORUM'	=> $this->helper->route('arcadewelten_arcade_play', ['game_id' => (int) $game['game_id']]),
			]);
		}
	}

	/**
	 * "?v=<mtime>" so browsers load a replaced game file instead of a cached copy.
	 */
	protected function cache_buster(array $game)
	{
		$file = $this->games->games_fs_path() . $game['game_dir'] . '/' . $game['game_file'];
		$mtime = @filemtime($file);

		return $mtime ? '?v=' . $mtime : '';
	}

	protected function game_web_path(array $game, $file)
	{
		if ($file === '' || $file === null)
		{
			return '';
		}

		return generate_board_url() . '/' . $this->games->games_path() . rawurlencode($game['game_dir']) . '/' . rawurlencode($file);
	}

	protected function format_score($score)
	{
		$score = (float) $score;
		$decimals = (floor($score) == $score) ? 0 : 2;

		return number_format($score, $decimals, $this->language->lang('AW_ARCADE_DEC_POINT'), $this->language->lang('AW_ARCADE_THOUSANDS_SEP'));
	}

	protected function php_ext()
	{
		global $phpEx;

		return $phpEx;
	}

	/**
	 * Permission "Can use the arcade": guests are asked to log in, members get an error.
	 */
	protected function check_use()
	{
		if ($this->access->can_use())
		{
			return;
		}

		if (!$this->is_member())
		{
			login_box('', $this->language->lang('AW_ARCADE_LOGIN_TO_PLAY'));
		}

		throw new \phpbb\exception\http_exception(403, 'AW_ARCADE_NO_PERMISSION');
	}

	protected function is_member()
	{
		return $this->user->data['user_id'] != ANONYMOUS && empty($this->user->data['is_bot']);
	}
}
