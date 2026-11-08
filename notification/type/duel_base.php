<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\notification\type;

/**
 * Shared parts of the duel notifications.
 */
abstract class duel_base extends \phpbb\notification\type\base
{
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

	public function is_available()
	{
		return $this->auth->acl_get('u_aw_arcade_play');
	}

	public static function get_item_id($data)
	{
		return (int) $data['duel_id'];
	}

	public static function get_item_parent_id($data)
	{
		return (int) $data['game_id'];
	}

	public function create_insert_array($data, $pre_create_data = [])
	{
		foreach (['game_name', 'game_scoretype', 'challenger_id', 'opponent_id', 'challenger_score', 'opponent_score', 'opponent_played', 'duel_status', 'winner_id', 'deadline'] as $key)
		{
			$this->set_data($key, $data[$key]);
		}

		parent::create_insert_array($data, $pre_create_data);
	}

	public function users_to_query()
	{
		return [(int) $this->get_data('challenger_id'), (int) $this->get_data('opponent_id')];
	}

	/**
	 * The other player from the recipient's point of view.
	 */
	protected function other_id()
	{
		return ((int) $this->user_id === (int) $this->get_data('challenger_id')) ? (int) $this->get_data('opponent_id') : (int) $this->get_data('challenger_id');
	}

	public function get_avatar()
	{
		return $this->user_loader->get_avatar($this->other_id(), false, true);
	}

	protected function load_lang()
	{
		$this->language->add_lang('common', 'arcadewelten/arcade');
	}

	protected static function score_text($score)
	{
		$score = (float) $score;

		return (floor($score) == $score) ? number_format($score, 0, ',', '.') : number_format($score, 2, ',', '.');
	}

	protected function username($user_id)
	{
		return $this->user_loader->get_username((int) $user_id, 'no_profile');
	}

	protected function plain($text)
	{
		return html_entity_decode((string) $text, ENT_COMPAT, 'UTF-8');
	}

	public function get_url()
	{
		return $this->helper->route('arcadewelten_arcade_duels');
	}

	public function get_email_template_variables()
	{
		return [
			'GAME_NAME'		=> $this->plain($this->get_data('game_name')),
			'OTHER_NAME'	=> $this->plain($this->username($this->other_id())),
			'U_DUELS'		=> $this->helper->route('arcadewelten_arcade_duels', [], true, false, \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL),
		];
	}
}
