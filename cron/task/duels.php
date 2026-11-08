<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\cron\task;

/**
 * Ends duels whose deadline has passed (the challenger wins) and notifies both players.
 * Runs at most every 15 minutes through phpBB's normal cron.
 */
class duels extends \phpbb\cron\task\base
{
	const INTERVAL = 900;

	protected $duels;
	protected $config;

	public function __construct(\arcadewelten\arcade\core\duels $duels, \phpbb\config\config $config)
	{
		$this->duels = $duels;
		$this->config = $config;
	}

	public function is_runnable()
	{
		return $this->duels->enabled();
	}

	public function should_run()
	{
		return (int) $this->config['aw_arcade_duel_cron_last'] < time() - self::INTERVAL;
	}

	public function run()
	{
		$this->config->set('aw_arcade_duel_cron_last', time(), false);
		$this->duels->expire();
	}
}
