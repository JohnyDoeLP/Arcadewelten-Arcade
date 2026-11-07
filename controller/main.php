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
	protected $config;
	protected $helper;
	protected $template;
	protected $language;
	protected $user;
	protected $request;
	protected $pagination;
	protected $root_path;

	public function __construct(games $games, \arcadewelten\arcade\core\scores $scores, \arcadewelten\arcade\core\access $access, \arcadewelten\arcade\core\points $points, ratings $ratings, \phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\language\language $language, \phpbb\user $user, \phpbb\request\request_interface $request, \phpbb\pagination $pagination, $root_path)
	{
		$this->games = $games;
		$this->scores = $scores;
		$this->access = $access;
		$this->points = $points;
		$this->ratings = $ratings;
		$this->config = $config;
		$this->helper = $helper;
		$this->template = $template;
		$this->language = $language;
		$this->user = $user;
		$this->request = $request;
		$this->pagination = $pagination;
		$this->root_path = $root_path;

		$this->language->add_lang('common', 'arcadewelten/arcade');
	}

	/* ---------------------------------------------------------------
	 * Game list
	 * ------------------------------------------------------------- */

	public function index($cat_id)
	{
		$cat_id = (int) $cat_id;
		$cat = $cat_id ? $this->games->get_cat($cat_id) : false;

		if ($cat_id && !$cat)
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

		foreach ($cats as $c)
		{
			$is_locked = !$this->access->has_access($c);
			$cid = (int) $c['cat_id'];

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
				'IMAGE'			=> $image,
				'CHAMPION'		=> $champ ? get_username_string('no_profile', $champ['user_id'], $champ['username'], $champ['user_colour']) : '',
				'CHAMP_RECORDS'	=> $champ ? $champ['records'] : 0,
				'U_CAT'			=> $this->helper->route('arcadewelten_arcade_cat', ['cat_id' => $cid]),
				'S_ACTIVE'		=> $cid === $cat_id,
				'S_PROTECTED'	=> $this->access->is_protected($c),
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
		$exclude = $cat_id ? [] : $locked;

		$guests_blocked = $is_guest && !$this->config['aw_arcade_guest_play'];
		$total = $guests_blocked ? 0 : $this->games->count_games($cat_id, true, $search, $exclude);
		$start = $this->pagination->validate_start($start, $per_page, $total);
		$rows = $guests_blocked ? [] : $this->games->list_games($cat_id, $start, $per_page, true, $search, $exclude, $sort);
		$tops = $is_guest ? [] : $this->scores->get_top($rows, 3);

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
				'S_GUEST'		=> $is_guest,
				'RATING_COUNT'	=> $this->ratings->enabled() ? (int) $row['game_rating_count'] : 0,
				'RATING_STARS'	=> $this->stars($row),
				'RATING_AVG'	=> $this->format_rating($row),
				'COST'			=> (!$is_guest && $this->points->active() && ($c = $this->points->value($row, 'cost')) > 0) ? $this->points->format($c) . ' ' . $this->points->name() : '',
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
			'AW_SORT'			=> $sort,
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

		$this->breadcrumbs($cat);

		return $this->helper->render('@arcadewelten_arcade/arcade_index.html', $cat ? $cat['cat_name'] : $this->language->lang('AW_ARCADE'));
	}

	/* ---------------------------------------------------------------
	 * Play
	 * ------------------------------------------------------------- */

	public function play($game_id)
	{
		$game = $this->games->get_game($game_id);

		if (!$game)
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
		}

		$play_id = $is_member ? $this->scores->start_play($this->user->data['user_id'], $game['game_id']) : '';
		$this->games->count_play($game['game_id']);

		add_form_key('aw_arcade_play');
		$this->assign_rating($game);

		$board_url = generate_board_url() . '/';
		$type = (int) $game['game_type'];

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

		$this->breadcrumbs($game['cat_id'] ? ['cat_id' => $game['cat_id'], 'cat_name' => $game['cat_name']] : false, $game);

		return $this->helper->render('@arcadewelten_arcade/arcade_play.html', $game['game_name']);
	}

	/* ---------------------------------------------------------------
	 * Highscores
	 * ------------------------------------------------------------- */

	public function scores($game_id)
	{
		if (!$this->is_member())
		{
			login_box('', $this->language->lang('AW_ARCADE_LOGIN_FOR_SCORES'));
		}

		$game = $this->games->get_game($game_id);

		if (!$game)
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
			$earned = (float) $this->request->variable('earned', '0');

			if ($earned > 0)
			{
				$message .= ' ' . $this->language->lang('AW_ARCADE_MSG_EARNED', $this->points->format($earned), $this->points->name());
			}
		}

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
		]);

		$this->breadcrumbs($game['cat_id'] ? ['cat_id' => $game['cat_id'], 'cat_name' => $game['cat_name']] : false, $game);

		return $this->helper->render('@arcadewelten_arcade/arcade_scores.html', $this->language->lang('AW_ARCADE_HIGHSCORES') . ' – ' . $game['game_name']);
	}

	/* ---------------------------------------------------------------
	 * Records of one member
	 * ------------------------------------------------------------- */

	public function user($user_id)
	{
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
		$game = $play ? $this->games->get_game($play['game_id']) : false;

		if (!$game)
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
			] + ($earned > 0 ? ['earned' => (string) $earned] : []), false),
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
		$game = $this->ratings->enabled() ? $this->games->get_game($game_id) : false;
		$error = '';

		if (!$game)
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

	protected function is_member()
	{
		return $this->user->data['user_id'] != ANONYMOUS && empty($this->user->data['is_bot']);
	}
}
