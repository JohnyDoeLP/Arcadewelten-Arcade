<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\core;

/**
 * What happens at the start and the end of a tournament:
 * forum topic at the start, prizes and result topic at the end.
 * Called by the cron task and by the tournament pages.
 */
class tourney_jobs
{
	protected $tourneys;
	protected $points;
	protected $forum;
	protected $access;
	protected $log;
	protected $user;

	public function __construct(tourneys $tourneys, points $points, forum $forum, access $access, \phpbb\log\log_interface $log, \phpbb\user $user)
	{
		$this->tourneys = $tourneys;
		$this->points = $points;
		$this->forum = $forum;
		$this->access = $access;
		$this->log = $log;
		$this->user = $user;
	}

	public function run()
	{
		if (!$this->tourneys->enabled())
		{
			return;
		}

		foreach ($this->tourneys->get_due_finishes() as $tourney)
		{
			$this->finish($tourney);
		}

		foreach ($this->tourneys->get_due_announcements() as $tourney)
		{
			if ($this->tourneys->claim($tourney['tourney_id'], 'tourney_announced'))
			{
				$this->announce($tourney);
			}
		}
	}

	/**
	 * Prizes (Ultimate Points) for places 1-3 and the result topic.
	 *
	 * @return array user_id => paid amount
	 */
	public function finish(array $tourney)
	{
		if (!$this->tourneys->claim($tourney['tourney_id'], 'tourney_finished'))
		{
			return [];
		}

		// Ended before anybody saw it start: nothing was announced, and nothing will be.
		$this->tourneys->claim($tourney['tourney_id'], 'tourney_announced');

		$standings = $this->tourneys->standings($tourney, $this->tourneys->get_games($tourney['tourney_id']));
		$paid = [];

		if ($this->points->active())
		{
			foreach (array_slice($standings['overall'], 0, 3) as $i => $row)
			{
				$amount = (float) $tourney['tourney_prize_' . ($i + 1)];

				if ($amount > 0)
				{
					$this->points->add((int) $row['user_id'], $amount);
					$paid[(int) $row['user_id']] = $amount;
				}
			}
		}

		$this->forum->announce_tourney_result($tourney, $standings['overall'], $paid);

		$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_AW_ARCADE_TOURNEY_FINISHED', false, [
			$tourney['tourney_name'],
			count($standings['overall']),
			$this->points->format(array_sum($paid)),
		]);

		return $paid;
	}

	/**
	 * Start topic: games from categories with a password or a group limit are only counted, not named.
	 */
	protected function announce(array $tourney)
	{
		$restricted = array_keys($this->access->all_cat_groups());
		$public = [];
		$hidden = 0;

		foreach ($this->tourneys->get_games($tourney['tourney_id']) as $game)
		{
			if ((string) $game['cat_password'] !== '' || in_array((int) $game['cat_id'], $restricted, true))
			{
				$hidden++;
				continue;
			}

			$public[] = $game;
		}

		$prizes = [];
		foreach ([1, 2, 3] as $place)
		{
			if ((float) $tourney['tourney_prize_' . $place] > 0)
			{
				$prizes[$place] = (float) $tourney['tourney_prize_' . $place];
			}
		}

		$this->forum->announce_tourney_start($tourney, $public, $hidden, $prizes);
	}
}
