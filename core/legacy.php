<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\core;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Score endpoints expected by old arcade games.
 *
 * Those games post to the board's index.php with hard-coded parameters:
 *  - IBPro:     index.php?act=Arcade&do=newscore          (POST gname, gscore)
 *  - Activity:  newscore.php in the board root (starts phpBB only), POST game_name, score
 *  - IBPro v3:  index.php?autocom=arcade&do=verifyscore   (answer randchar/randchar2)
 *               index.php?autocom=arcade&do=savescore     (POST gname, gscore, enscore)
 *  - IBPro v32: index.php?autocom=arcade&do=sessionstart  (POST gamename)
 *               index.php?autocom=arcade&do=permrequest   (POST id, score, gametime, fakekey)
 *               index.php?autocom=arcade&do=burn          (GET id, microone)
 */
class legacy
{
	protected $games;
	protected $scores;
	protected $points;
	protected $request;
	protected $user;
	protected $auth;
	protected $helper;

	protected $config;
	protected $log;

	public function __construct(games $games, scores $scores, points $points, \phpbb\request\request_interface $request, \phpbb\user $user, \phpbb\auth\auth $auth, \phpbb\controller\helper $helper, \phpbb\config\config $config, \phpbb\log\log_interface $log)
	{
		$this->config = $config;
		$this->log = $log;
		$this->games = $games;
		$this->scores = $scores;
		$this->points = $points;
		$this->request = $request;
		$this->user = $user;
		$this->auth = $auth;
		$this->helper = $helper;
	}

	/**
	 * Does the current request look like a legacy score call?
	 */
	public function is_legacy_request()
	{
		$act = strtolower($this->request->variable('act', ''));
		$do = strtolower($this->request->variable('do', ''));
		$autocom = strtolower($this->request->variable('autocom', ''));

		return ($act === 'arcade' && $do === 'newscore') || $autocom === 'arcade';
	}

	/**
	 * @return Response
	 */
	public function handle($activity_mod = false)
	{
		$this->user->session_begin();
		$this->auth->acl($this->user->data);

		if (!empty($this->config['aw_arcade_debug']))
		{
			$this->debug_log($activity_mod);
		}

		if ($activity_mod)
		{
			return $this->ibpro_newscore();
		}

		$do = strtolower($this->request->variable('do', ''));

		if (strtolower($this->request->variable('autocom', '')) !== 'arcade')
		{
			return $this->ibpro_newscore();
		}

		switch ($do)
		{
			case 'verifyscore':
				return $this->v3_verify();

			case 'savescore':
			case 'newscore':
				return $this->v3_save();

			case 'sessionstart':
				return $this->v32_start();

			case 'permrequest':
				return $this->v32_perm();

			case 'burn':
				return $this->v32_burn();
		}

		return $this->text('&result=FAIL');
	}

	/**
	 * Write what a game sent into the admin log (diagnostic mode only).
	 */
	protected function debug_log($activity_mod)
	{
		$fields = [];
		$super = $this->request->get_super_global(\phpbb\request\request_interface::POST);
		foreach (array_slice($super, 0, 25, true) as $k => $v)
		{
			$fields[] = 'POST ' . $k . '=' . (is_array($v) ? '[array]' : utf8_substr((string) $v, 0, 60));
		}

		$super = $this->request->get_super_global(\phpbb\request\request_interface::GET);
		foreach (array_slice($super, 0, 15, true) as $k => $v)
		{
			$fields[] = 'GET ' . $k . '=' . (is_array($v) ? '[array]' : utf8_substr((string) $v, 0, 60));
		}

		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_AW_ARCADE_DEBUG', false, [
			($activity_mod ? 'newscore.php' : 'index.php') . ' ' . $this->request->server('REQUEST_METHOD', ''),
			$this->user->data['username'],
			implode(' | ', $fields) ?: '-',
		]);
	}

	/**
	 * Request value as string, empty for missing or "undefined" (AVM1).
	 */
	protected function value($name)
	{
		$v = trim($this->request->variable($name, '', true));

		return ($v === 'undefined' || $v === 'null' || $v === 'NaN') ? '' : $v;
	}

	protected function is_member()
	{
		return $this->user->data['user_id'] != ANONYMOUS && empty($this->user->data['is_bot']);
	}

	protected function text($body)
	{
		return new Response($body, 200, [
			'Content-Type'	=> 'text/plain; charset=UTF-8',
			'Cache-Control'	=> 'no-store',
		]);
	}

	/**
	 * Store the score of a running play session and send the player to the highscores.
	 */
	protected function finish($game, $score, $play = false)
	{
		if (!$game)
		{
			return new RedirectResponse($this->helper->route('arcadewelten_arcade_index', [], false));
		}

		if (!$this->is_member())
		{
			return new RedirectResponse($this->helper->route('arcadewelten_arcade_index', ['guest_done' => 1], false));
		}

		$params = ['game_id' => (int) $game['game_id']];

		if (!$play)
		{
			$params['nosession'] = 1;
		}
		else if (($full = $this->games->get_game($game['game_id'])) && ($reason = $this->scores->check_plausible($full, $play, $score)) !== '')
		{
			// Implausible: keep it for the admins to review, not in the highscores.
			$this->scores->flag($full, $this->user->data['user_id'], $score, $play, $reason);
			$this->scores->end_play($play['play_id']);
			$params['flagged'] = 1;
		}
		else
		{
			$result = $this->scores->save_score($game, $this->user->data['user_id'], $score);
			$this->scores->end_play($play['play_id']);
			$params['saved'] = $result['improved'] ? 2 : 1;
			$params['last'] = (string) round((float) $score, 2);

			$full = $this->games->get_game($game['game_id']);
			$earned = $full ? $this->points->reward_round($this->user->data['user_id'], $full, $score) : 0;

			if ($earned > 0)
			{
				$params['earned'] = (string) $earned;
			}

			if (!empty($result['jackpot']))
			{
				$params['jackpot'] = (string) $result['jackpot'];
			}
		}

		return new RedirectResponse($this->helper->route('arcadewelten_arcade_scores', $params, false));
	}

	/* IBPro --------------------------------------------------------- */

	protected function ibpro_newscore()
	{
		// IBPro sends gname/gscore, Activity Mod (via newscore.php) game_name/score.
		// Games patched for Ruffle may send unused names with the value "undefined".
		$scorevar = $this->value('gname');
		$scorevar = $scorevar !== '' ? $scorevar : $this->value('game_name');
		$raw = $this->value('gscore');
		$raw = $raw !== '' ? $raw : $this->value('score');
		$score = (float) str_replace(',', '.', $raw);
		$game = $this->games->get_game_by_scorevar($scorevar);
		$play = ($game && $this->is_member()) ? $this->scores->get_play($this->user->data['user_id'], $game['game_id']) : false;

		return $this->finish($game, $score, $play);
	}

	/* IBPro v3 ------------------------------------------------------ */

	protected function v3_verify()
	{
		$r1 = random_int(1, 300);
		$r2 = random_int(1, 300);

		if ($this->is_member() && ($play = $this->scores->get_play($this->user->data['user_id'])))
		{
			$this->scores->update_play($play['play_id'], ['randchar1' => $r1, 'randchar2' => $r2]);
		}

		return $this->text('&randchar=' . $r1 . '&randchar2=' . $r2 . '&savescore=1&blah=OK');
	}

	protected function v3_save()
	{
		$game = $this->games->get_game_by_scorevar($this->request->variable('gname', ''));
		$score = $this->request->variable('gscore', 0.0);
		$enscore = $this->request->variable('enscore', 0);
		$play = ($game && $this->is_member()) ? $this->scores->get_play($this->user->data['user_id'], $game['game_id']) : false;

		// Integrity check used by v3 games: enscore = gscore * randchar ^ randchar2
		if ($play && $play['randchar1'])
		{
			$expected = ((int) $score * (int) $play['randchar1']) ^ (int) $play['randchar2'];

			if ($expected !== (int) $enscore)
			{
				$play = false;
			}
		}

		return $this->finish($game, $score, $play);
	}

	/* IBPro v32 ----------------------------------------------------- */

	protected function v32_start()
	{
		$game = $this->games->get_game_by_scorevar($this->request->variable('gamename', ''));

		if (!$game)
		{
			return $this->text('&connStatus=0&result=FAIL');
		}

		// Play sessions are only created by the game page (category passwords are checked there).
		return $this->text('&connStatus=1&initbar=' . random_int(1000, 9999) . '&gametime=' . time() . '&lastid=' . (int) $game['game_id'] . '&result=OK');
	}

	protected function v32_perm()
	{
		$game_id = $this->request->variable('id', 0);
		$score = $this->request->variable('score', 0.0);

		if ($this->is_member() && ($play = $this->scores->get_play($this->user->data['user_id'], $game_id)))
		{
			$this->scores->update_play($play['play_id'], ['pending_score' => round($score, 2), 'pending_set' => 1]);
		}

		return $this->text('&validate=1&microone=' . str_replace('.', '', (string) microtime(true)) . '&result=OK');
	}

	protected function v32_burn()
	{
		$game = $this->games->get_game($this->request->variable('id', 0));
		$play = ($game && $this->is_member()) ? $this->scores->get_play($this->user->data['user_id'], $game['game_id']) : false;

		if ($play && !$play['pending_set'])
		{
			$play = false;
		}

		return $this->finish($game, $play ? $play['pending_score'] : 0, $play);
	}
}
