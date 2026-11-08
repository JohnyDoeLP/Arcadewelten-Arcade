<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\notification\type;

use arcadewelten\arcade\core\duels;

/**
 * Duel won, lost or declined – sent to both players (declined: only to the challenger).
 */
class duel_result extends duel_base
{
	public static $notification_option = [
		'lang'	=> 'AW_ARCADE_NOTIFICATION_TYPE_DUEL_RESULT',
		'group'	=> 'AW_ARCADE_NOTIFICATION_GROUP',
	];

	public function get_type()
	{
		return 'arcadewelten.arcade.notification.type.duel_result';
	}

	public function find_users_for_notification($data, $options = [])
	{
		$users = ((int) $data['duel_status'] === duels::DECLINED)
			? [(int) $data['challenger_id']]
			: [(int) $data['challenger_id'], (int) $data['opponent_id']];

		$this->user_loader->load_users([(int) $data['challenger_id'], (int) $data['opponent_id']]);

		return $this->check_user_notification_options($users, $options);
	}

	/**
	 * Language key for the recipient: WON, LOST or DECLINED.
	 */
	protected function outcome()
	{
		if ((int) $this->get_data('duel_status') === duels::DECLINED)
		{
			return 'DECLINED';
		}

		return ((int) $this->get_data('winner_id') === (int) $this->user_id) ? 'WON' : 'LOST';
	}

	public function get_title()
	{
		$this->load_lang();

		return $this->language->lang('AW_ARCADE_NOTIFICATION_DUEL_' . $this->outcome(),
			$this->username($this->other_id()),
			$this->get_data('game_name'));
	}

	public function get_reference()
	{
		$this->load_lang();

		if ($this->outcome() === 'DECLINED')
		{
			return '';
		}

		return $this->language->lang('AW_ARCADE_NOTIFICATION_DUEL_SCORES',
			self::score_text($this->get_data('challenger_score')),
			$this->get_data('opponent_played') ? self::score_text($this->get_data('opponent_score')) : '–');
	}

	public function get_email_template()
	{
		return '@arcadewelten_arcade/duel_result';
	}

	public function get_email_template_variables()
	{
		$this->load_lang();

		return parent::get_email_template_variables() + [
			'RESULT'	=> $this->plain(strip_tags($this->get_title())),
			'SCORES'	=> $this->plain(strip_tags($this->get_reference())),
		];
	}
}
