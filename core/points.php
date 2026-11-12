<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\core;

/**
 * Bridge to the Ultimate Points extension (dmzx/ultimatepoints).
 *
 * Values are resolved game -> category -> global; -1 means "inherit".
 * Everything is a no-op when Ultimate Points is not installed or the
 * arcade points feature is switched off.
 */
class points
{
	const KEYS = ['cost', 'reward_play', 'reward_1', 'reward_2', 'reward_3', 'jackpot'];

	/** @var \dmzx\ultimatepoints\core\api|null */
	protected $api;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\language\language */
	protected $language;

	public function __construct(\phpbb\config\config $config, \phpbb\language\language $language, $api = null)
	{
		$this->config = $config;
		$this->language = $language;
		$this->api = $api;
	}

	/**
	 * Is Ultimate Points installed at all?
	 */
	public function available()
	{
		return $this->api !== null;
	}

	/**
	 * Installed, enabled in Ultimate Points and enabled for the arcade.
	 */
	public function active()
	{
		return $this->api !== null
			&& !empty($this->config['points_enable'])
			&& !empty($this->config['aw_arcade_points_enable']);
	}

	public function name()
	{
		return isset($this->config['points_name']) ? (string) $this->config['points_name'] : 'Points';
	}

	/**
	 * Effective value of one setting for a game.
	 *
	 * @param array  $game  game row, optionally joined with cat_* point columns
	 * @param string $key   one of self::KEYS
	 */
	public function value(array $game, $key)
	{
		if (isset($game['game_' . $key]) && (float) $game['game_' . $key] >= 0)
		{
			return (float) $game['game_' . $key];
		}

		if (isset($game['cat_' . $key]) && $game['cat_' . $key] !== null && (float) $game['cat_' . $key] >= 0)
		{
			return (float) $game['cat_' . $key];
		}

		return max(0, (float) $this->config['aw_arcade_' . $key]);
	}

	public function balance($user_id)
	{
		return $this->api ? (float) $this->api->get_points((int) $user_id) : 0.0;
	}

	public function add($user_id, $amount)
	{
		if ($this->active() && $amount > 0)
		{
			$this->api->add_points((int) $user_id, round((float) $amount, 2));
		}
	}

	public function subtract($user_id, $amount)
	{
		if ($this->active() && $amount > 0)
		{
			$this->api->substract_points((int) $user_id, round((float) $amount, 2));
		}
	}

	/**
	 * Charge the cost of a game.
	 *
	 * @return bool false if the user cannot afford it
	 */
	public function charge($user_id, array $game)
	{
		if (!$this->active())
		{
			return true;
		}

		$cost = $this->value($game, 'cost');

		if ($cost <= 0)
		{
			return true;
		}

		if ($this->balance($user_id) < $cost)
		{
			return false;
		}

		$this->subtract($user_id, $cost);

		return true;
	}

	/**
	 * Reward for a finished round with a score above zero.
	 *
	 * @return float amount paid
	 */
	public function reward_round($user_id, array $game, $score)
	{
		if (!$this->active() || (float) $score <= 0)
		{
			return 0.0;
		}

		$amount = $this->value($game, 'reward_play');
		$this->add($user_id, $amount);

		return $amount;
	}

	public function format($amount)
	{
		$amount = (float) $amount;

		$this->language->add_lang('common', 'arcadewelten/arcade');

		return number_format($amount, (floor($amount) == $amount) ? 0 : 2, $this->language->lang('AW_ARCADE_DEC_POINT'), $this->language->lang('AW_ARCADE_THOUSANDS_SEP'));
	}
}
