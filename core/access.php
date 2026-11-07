<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\core;

/**
 * Password protected categories.
 *
 * After the correct password a signed browser-session cookie unlocks the
 * category for this user. Changing the password invalidates old cookies.
 * Administrators always have access.
 */
class access
{
	protected $config;
	protected $user;
	protected $auth;
	protected $request;
	protected $passwords;

	public function __construct(\phpbb\config\config $config, \phpbb\user $user, \phpbb\auth\auth $auth, \phpbb\request\request_interface $request, \phpbb\passwords\manager $passwords)
	{
		$this->config = $config;
		$this->user = $user;
		$this->auth = $auth;
		$this->request = $request;
		$this->passwords = $passwords;
	}

	public function is_protected($cat)
	{
		return !empty($cat['cat_password']);
	}

	public function has_access($cat)
	{
		if (!$cat || !$this->is_protected($cat) || $this->auth->acl_get('a_'))
		{
			return true;
		}

		$cookie = $this->request->variable($this->config['cookie_name'] . '_awcat_' . (int) $cat['cat_id'], '', false, \phpbb\request\request_interface::COOKIE);

		return $cookie !== '' && hash_equals($this->token($cat), $cookie);
	}

	/**
	 * Check the password and set the unlock cookie.
	 */
	public function unlock($cat, $password)
	{
		if (!$cat || !$this->is_protected($cat) || $password === '' || !$this->passwords->check($password, $cat['cat_password']))
		{
			return false;
		}

		// Cookie time 0 = valid until the browser is closed.
		$this->user->set_cookie('awcat_' . (int) $cat['cat_id'], $this->token($cat), 0);

		return true;
	}

	public function hash($password)
	{
		return $this->passwords->hash($password);
	}

	protected function token($cat)
	{
		return hash_hmac('sha256', (int) $cat['cat_id'] . '|' . (int) $this->user->data['user_id'] . '|' . $cat['cat_password'], (string) $this->config['aw_arcade_secret']);
	}
}
