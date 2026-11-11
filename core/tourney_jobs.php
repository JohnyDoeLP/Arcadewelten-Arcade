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
	protected $games;

	public function __construct(tourneys $tourneys, points $points, forum $forum, access $access, \phpbb\log\log_interface $log, \phpbb\user $user, games $games)
	{
		$this->tourneys = $tourneys;
		$this->points = $points;
		$this->forum = $forum;
		$this->access = $access;
		$this->log = $log;
		$this->user = $user;
		$this->games = $games;
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

		$result_topic = $this->forum->announce_tourney_result($tourney, $standings['overall'], $paid);
		$this->forum->reply_tourney_closed($tourney, $result_topic);

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

		$topic_id = $this->forum->announce_tourney_start($tourney, $public, $hidden, $prizes);

		if ($topic_id)
		{
			$this->tourneys->set_topic($tourney['tourney_id'], $topic_id);
		}
	}

	/**
	 * A running, already announced tournament was changed in the ACP:
	 * reply in its start topic with what changed (end, games, prizes).
	 *
	 * @param array $before tournament row before saving
	 * @param array $before_games game ids before saving
	 * @return bool whether a reply was posted
	 */
	public function changed(array $before, array $before_games, $tourney_id)
	{
		$after = $this->tourneys->get($tourney_id);

		if (!$after || empty($before['tourney_announced']) || tourneys::status($after) !== tourneys::RUNNING)
		{
			return false;
		}

		$lines = [];

		if ((int) $before['tourney_end'] !== (int) $after['tourney_end'])
		{
			$lines[] = $this->forum->post_lang('AW_ARCADE_POST_TOURNEY_CHANGE_END',
				$this->user->format_date((int) $after['tourney_end'], 'd.m.Y H:i'),
				$this->user->format_date((int) $before['tourney_end'], 'd.m.Y H:i'));
		}

		$games = $this->tourneys->get_games($tourney_id);
		$restricted = array_keys($this->access->all_cat_groups());
		$added = array_diff(array_keys($games), $before_games);
		$removed = array_diff($before_games, array_keys($games));

		if ($added)
		{
			$lines[] = $this->forum->post_lang('AW_ARCADE_POST_TOURNEY_CHANGE_ADDED', $this->game_list($added, $games, $restricted));
		}

		if ($removed)
		{
			// Removed games are no longer in the tournament: look them up directly.
			$gone = [];
			foreach ($removed as $game_id)
			{
				$game = $this->games->get_game($game_id);
				if ($game)
				{
					$gone[(int) $game_id] = $game;
				}
			}
			$lines[] = $this->forum->post_lang('AW_ARCADE_POST_TOURNEY_CHANGE_REMOVED', $this->game_list($removed, $gone, $restricted));
		}

		$prizes_changed = false;
		$prize_parts = [];
		foreach ([1, 2, 3] as $place)
		{
			$old = (float) $before['tourney_prize_' . $place];
			$new = (float) $after['tourney_prize_' . $place];
			$prizes_changed = $prizes_changed || abs($old - $new) > 0.001;

			if ($new > 0)
			{
				$prize_parts[] = $this->forum->post_lang('AW_ARCADE_POST_TOURNEY_PRIZE_LINE', $place, $this->points->format($new), $this->points->name());
			}
		}

		if ($prizes_changed && $this->points->active())
		{
			$lines[] = $prize_parts
				? $this->forum->post_lang('AW_ARCADE_POST_TOURNEY_CHANGE_PRIZES', implode(' · ', $prize_parts))
				: $this->forum->post_lang('AW_ARCADE_POST_TOURNEY_CHANGE_NO_PRIZES');
		}

		return $this->forum->reply_tourney_change($after, $lines);
	}

	/**
	 * "Farbblasen, Juwelen und 1 weiteres Spiel": restricted games are only counted.
	 */
	protected function game_list(array $ids, array $games, array $restricted)
	{
		$names = [];
		$hidden = 0;

		foreach ($ids as $game_id)
		{
			if (!isset($games[$game_id]))
			{
				$hidden++;
				continue;
			}

			$game = $games[$game_id];
			$cat_password = isset($game['cat_password']) ? (string) $game['cat_password'] : '';

			if ($cat_password !== '' || in_array((int) $game['cat_id'], $restricted, true))
			{
				$hidden++;
				continue;
			}

			$names[] = $game['game_name'];
		}

		if ($hidden)
		{
			$names[] = $this->forum->post_lang('AW_ARCADE_POST_TOURNEY_MORE', $hidden);
		}

		return implode(', ', $names);
	}
}
