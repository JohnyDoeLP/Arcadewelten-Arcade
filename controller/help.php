<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\controller;

/**
 * The arcade's help page, inside phpBB's own help system.
 *
 * A board that has duels switched off should not be answering questions
 * about duels, so every block asks first whether that part of the arcade is
 * in use. What a visitor reads is what their board actually does.
 */
class help extends \phpbb\help\controller\controller
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \arcadewelten\arcade\core\duels */
	protected $duels;

	/** @var \arcadewelten\arcade\core\tourneys */
	protected $tourneys;

	/** @var \arcadewelten\arcade\core\points */
	protected $points;

	public function __construct(\phpbb\controller\helper $helper, \phpbb\help\manager $manager, \phpbb\template\template $template, \phpbb\language\language $language, $root_path, $php_ext, \phpbb\config\config $config, \arcadewelten\arcade\core\duels $duels, \arcadewelten\arcade\core\tourneys $tourneys, \arcadewelten\arcade\core\points $points)
	{
		parent::__construct($helper, $manager, $template, $language, $root_path, $php_ext);

		$this->config = $config;
		$this->duels = $duels;
		$this->tourneys = $tourneys;
		$this->points = $points;
	}

	/**
	 * @return string title of the page
	 */
	protected function display()
	{
		if (empty($this->config['aw_arcade_help']))
		{
			throw new \phpbb\exception\http_exception(404, 'AW_ARCADE_HELP_OFF');
		}

		$this->language->add_lang('help/arcade', 'arcadewelten/arcade');

		$this->template->assign_block_vars('navlinks', [
			'BREADCRUMB_NAME'	=> $this->language->lang('AW_ARCADE'),
			'U_BREADCRUMB'		=> $this->helper->route('arcadewelten_arcade_index'),
		]);

		$this->block('START', [
			'WHAT', 'WHERE', 'GUEST', 'MOBILE', 'FULLSCREEN', 'LIGHTS',
		]);

		$this->block('SCORES', [
			'HOW', 'NOT_COUNTED', 'TOO_FAST', 'TOO_HIGH', 'CHECKED', 'BLACK',
			'ZERO', 'BEST_ONLY',
		]);

		$this->block('CATEGORIES', [
			'MISSING', 'PASSWORD', 'SEASON_CAT',
		]);

		$this->block('SEASON', [
			'WHAT_SEASON', 'WHEN', 'RECORDS_GONE', 'CERTIFICATE',
		], !empty($this->config['aw_arcade_season_start']));

		$this->block('DUELS', [
			'WHAT_DUEL', 'CHALLENGE', 'DECIDE', 'EXPIRE',
		], $this->duels->enabled());

		$this->block('TOURNEYS', [
			'WHAT_TOURNEY', 'JOIN', 'POINTS_TOURNEY', 'MISSED',
		], $this->tourneys->enabled());

		$this->block('POINTS', [
			'EARN', 'COST', 'JACKPOT', 'NOT_ENOUGH',
		], $this->points->active());

		$this->block('EXTRAS', [
			'FAVOURITES', 'RATING', 'MEDALS', 'NEW_GAMES',
		]);

		$this->block('DOWNLOAD', [
			'HOW_DOWNLOAD', 'OWN_GAME',
		], !empty($this->config['aw_arcade_download']));

		$this->block('PRIVACY', [
			'STORED', 'DELETE', 'EXTERNAL',
		]);

		return $this->language->lang('AW_ARCADE_HELP_TITLE');
	}

	/**
	 * One block of questions, left out when that part of the arcade is off.
	 *
	 * @param string $name  part after AW_ARCADE_HELP_
	 * @param array  $items part after AW_ARCADE_HELP_<name>_, without
	 *                      _QUESTION and _ANSWER
	 * @param bool   $show  false leaves the whole block out
	 */
	protected function block($name, array $items, $show = true)
	{
		if (!$show)
		{
			return;
		}

		$questions = [];

		foreach ($items as $item)
		{
			$key = 'AW_ARCADE_HELP_' . $name . '_' . $item;
			$questions[$key . '_QUESTION'] = $key . '_ANSWER';
		}

		$this->manager->add_block('AW_ARCADE_HELP_BLOCK_' . $name, false, $questions);
	}
}
