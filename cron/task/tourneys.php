<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\cron\task;

/**
 * Starts and ends tournaments (forum topics, prizes).
 * Runs at most every 5 minutes through phpBB's normal cron.
 */
class tourneys extends \phpbb\cron\task\base
{
	const INTERVAL = 300;

	protected $tourneys;
	protected $jobs;
	protected $config;

	public function __construct(\arcadewelten\arcade\core\tourneys $tourneys, \arcadewelten\arcade\core\tourney_jobs $jobs, \phpbb\config\config $config)
	{
		$this->tourneys = $tourneys;
		$this->jobs = $jobs;
		$this->config = $config;
	}

	public function is_runnable()
	{
		return $this->tourneys->enabled();
	}

	public function should_run()
	{
		return (int) $this->config['aw_arcade_tourney_cron_last'] < time() - self::INTERVAL;
	}

	public function run()
	{
		$this->config->set('aw_arcade_tourney_cron_last', time(), false);
		$this->jobs->run();
	}
}
