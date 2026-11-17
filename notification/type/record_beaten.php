<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\notification\type;

/**
 * "X beat your record in game Y" – sent to the player who held the record.
 *
 * Only the first place counts: being overtaken further down the highscore
 * list is not worth a notification. Everybody can switch this off under
 * notification settings, like any other phpBB notification.
 */
class record_beaten extends \phpbb\notification\type\base
{
	public static $notification_option = [
		'lang'	=> 'AW_ARCADE_NOTIFICATION_TYPE_RECORD_BEATEN',
		'group'	=> 'AW_ARCADE_NOTIFICATION_GROUP',
	];

	/** @var \phpbb\user_loader */
	protected $user_loader;

	/** @var \phpbb\controller\helper */
	protected $helper;

	public function set_user_loader(\phpbb\user_loader $user_loader)
	{
		$this->user_loader = $user_loader;
	}

	public function set_helper(\phpbb\controller\helper $helper)
	{
		$this->helper = $helper;
	}

	public function get_type()
	{
		return 'arcadewelten.arcade.notification.type.record_beaten';
	}

	public function is_available()
	{
		return $this->auth->acl_get('u_aw_arcade_play');
	}

	public static function get_item_id($data)
	{
		return (int) $data['game_id'];
	}

	public static function get_item_parent_id($data)
	{
		return (int) $data['game_id'];
	}

	public function find_users_for_notification($data, $options = [])
	{
		$this->user_loader->load_users([(int) $data['holder_id'], (int) $data['beaten_user_id']]);

		return $this->check_user_notification_options([(int) $data['beaten_user_id']], $options);
	}

	public function create_insert_array($data, $pre_create_data = [])
	{
		foreach (['game_id', 'game_name', 'holder_id', 'beaten_user_id', 'new_score', 'old_score'] as $key)
		{
			$this->set_data($key, $data[$key]);
		}

		parent::create_insert_array($data, $pre_create_data);
	}

	public function users_to_query()
	{
		return [(int) $this->get_data('holder_id')];
	}

	public function get_avatar()
	{
		return $this->user_loader->get_avatar((int) $this->get_data('holder_id'), false, true);
	}

	public function get_title()
	{
		$this->load_lang();

		return $this->language->lang('AW_ARCADE_NOTIFICATION_RECORD_BEATEN',
			$this->user_loader->get_username((int) $this->get_data('holder_id'), 'no_profile'),
			$this->get_data('game_name'));
	}

	public function get_reference()
	{
		$this->load_lang();

		return $this->language->lang('AW_ARCADE_NOTIFICATION_RECORD_SCORES',
			self::score_text($this->get_data('new_score')),
			self::score_text($this->get_data('old_score')));
	}

	public function get_url()
	{
		return $this->helper->route('arcadewelten_arcade_scores', ['game_id' => (int) $this->get_data('game_id')]);
	}

	public function get_email_template()
	{
		return '@arcadewelten_arcade/record_beaten';
	}

	public function get_email_template_variables()
	{
		return [
			'GAME_NAME'		=> $this->plain($this->get_data('game_name')),
			'OTHER_NAME'	=> $this->plain($this->user_loader->get_username((int) $this->get_data('holder_id'), 'no_profile')),
			'NEW_SCORE'		=> self::score_text($this->get_data('new_score')),
			'OLD_SCORE'		=> self::score_text($this->get_data('old_score')),
			'U_SCORES'		=> $this->helper->route('arcadewelten_arcade_scores', ['game_id' => (int) $this->get_data('game_id')], true, false, \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL),
		];
	}

	protected function load_lang()
	{
		$this->language->add_lang('common', 'arcadewelten/arcade');
	}

	protected function plain($text)
	{
		return html_entity_decode((string) $text, ENT_COMPAT, 'UTF-8');
	}

	protected static function score_text($score)
	{
		$score = (float) $score;

		return (floor($score) == $score) ? number_format($score, 0, ',', '.') : number_format($score, 2, ',', '.');
	}
}
