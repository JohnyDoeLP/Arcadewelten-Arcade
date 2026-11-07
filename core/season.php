<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\core;

/**
 * Season end: pay places 1-3 of every game and optionally reset all highscores.
 */
class season
{
	protected $games;
	protected $scores;
	protected $points;
	protected $config;
	protected $log;
	protected $user;
	protected $language;

	public function __construct(games $games, scores $scores, points $points, \phpbb\config\config $config, \phpbb\log\log_interface $log, \phpbb\user $user, \phpbb\language\language $language)
	{
		$this->language = $language;
		$this->games = $games;
		$this->scores = $scores;
		$this->points = $points;
		$this->config = $config;
		$this->log = $log;
		$this->user = $user;
	}

	/**
	 * Planned payouts.
	 *
	 * @return array ['rows' => [...], 'users' => user_id => total, 'total' => float]
	 */
	public function preview()
	{
		$games = $this->games->list_games(0, 0, 100000, true);
		$tops = $this->scores->get_top($games, 3);
		$rows = [];
		$users = [];
		$total = 0.0;

		foreach ($games as $game)
		{
			if ((int) $game['game_save_type'] === games::SAVE_NONE || empty($tops[(int) $game['game_id']]))
			{
				continue;
			}

			foreach ($tops[(int) $game['game_id']] as $i => $entry)
			{
				$amount = $this->points->value($game, 'reward_' . ($i + 1));

				$rows[] = [
					'game_id'		=> (int) $game['game_id'],
					'game_name'		=> $game['game_name'],
					'place'			=> $i + 1,
					'user_id'		=> (int) $entry['user_id'],
					'username'		=> $entry['username'],
					'user_colour'	=> $entry['user_colour'],
					'score'			=> $entry['score'],
					'amount'		=> $amount,
				];

				if ($amount > 0)
				{
					$uid = (int) $entry['user_id'];
					$users[$uid] = (isset($users[$uid]) ? $users[$uid] : 0) + $amount;
					$total += $amount;
				}
			}
		}

		arsort($users);

		return ['rows' => $rows, 'users' => $users, 'total' => $total];
	}

	/**
	 * Pay out and optionally reset.
	 *
	 * @return array ['paid' => float, 'players' => int]
	 */
	public function close($reset, $reset_plays = false)
	{
		$plan = $this->preview();
		$paid = 0.0;

		if ($this->points->active())
		{
			foreach ($plan['users'] as $user_id => $amount)
			{
				$this->points->add($user_id, $amount);
				$paid += $amount;
			}
		}

		if ($reset)
		{
			$this->scores->reset_all();
		}

		if ($reset_plays)
		{
			$this->games->reset_plays();
		}

		$this->config->set('aw_arcade_season_start', time());

		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_AW_ARCADE_SEASON_CLOSED', false, [
			$this->points->format($paid),
			count($plan['users']),
			$this->language->lang($reset ? 'YES' : 'NO'),
			$this->language->lang($reset_plays ? 'YES' : 'NO'),
		]);

		return ['paid' => $paid, 'players' => count($plan['users'])];
	}
}
