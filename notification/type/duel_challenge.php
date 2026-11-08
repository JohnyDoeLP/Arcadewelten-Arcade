<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\notification\type;

/**
 * "X challenges you in game Y" – sent to the opponent.
 */
class duel_challenge extends duel_base
{
	public static $notification_option = [
		'lang'	=> 'AW_ARCADE_NOTIFICATION_TYPE_DUEL_CHALLENGE',
		'group'	=> 'AW_ARCADE_NOTIFICATION_GROUP',
	];

	public function get_type()
	{
		return 'arcadewelten.arcade.notification.type.duel_challenge';
	}

	public function find_users_for_notification($data, $options = [])
	{
		$this->user_loader->load_users([(int) $data['challenger_id'], (int) $data['opponent_id']]);

		return $this->check_user_notification_options([(int) $data['opponent_id']], $options);
	}

	public function get_title()
	{
		$this->load_lang();

		return $this->language->lang('AW_ARCADE_NOTIFICATION_DUEL_CHALLENGE',
			$this->user_loader->get_username((int) $this->get_data('challenger_id'), 'no_profile'),
			$this->get_data('game_name'));
	}

	public function get_reference()
	{
		$this->load_lang();

		return $this->language->lang('AW_ARCADE_NOTIFICATION_DUEL_BEAT',
			self::score_text($this->get_data('challenger_score')),
			$this->user->format_date((int) $this->get_data('deadline')));
	}

	public function get_email_template()
	{
		return '@arcadewelten_arcade/duel_challenge';
	}

	public function get_email_template_variables()
	{
		return parent::get_email_template_variables() + [
			'SCORE'		=> self::score_text($this->get_data('challenger_score')),
			'DEADLINE'	=> $this->plain($this->user->format_date((int) $this->get_data('deadline'))),
		];
	}
}
