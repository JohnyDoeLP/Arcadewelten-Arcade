<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\core;

/**
 * Who may use the arcade and which categories they see.
 *
 * - Permission u_aw_arcade_play ("Can use the arcade") from the phpBB permission system.
 * - Categories limited to user groups are invisible for everybody else.
 * - Password protected categories: after the correct password a signed
 *   browser-session cookie unlocks the category for this user. Changing the
 *   password invalidates old cookies.
 * Administrators always have access.
 */
class access
{
	protected $db;
	protected $cat_groups_table;
	/** @var array|null cat_id => [group_id, ...] */
	protected $cat_groups;
	/** @var array|null */
	protected $hidden;
	protected $config;
	protected $user;
	protected $auth;
	protected $request;
	protected $passwords;

	public function __construct(\phpbb\config\config $config, \phpbb\user $user, \phpbb\auth\auth $auth, \phpbb\request\request_interface $request, \phpbb\passwords\manager $passwords, \phpbb\db\driver\driver_interface $db, $cat_groups_table)
	{
		$this->db = $db;
		$this->cat_groups_table = $cat_groups_table;
		$this->config = $config;
		$this->user = $user;
		$this->auth = $auth;
		$this->request = $request;
		$this->passwords = $passwords;
	}

	/* ---------------------------------------------------------------
	 * Permission and group visibility
	 * ------------------------------------------------------------- */

	/**
	 * Permission "Can use the arcade" (administrators always).
	 */
	public function can_use()
	{
		return (bool) ($this->auth->acl_get('u_aw_arcade_play') || $this->auth->acl_get('a_'));
	}

	/**
	 * Groups a category is limited to, for all categories (empty = everybody).
	 *
	 * @return array cat_id => [group_id, ...]
	 */
	public function all_cat_groups()
	{
		if ($this->cat_groups === null)
		{
			$this->cat_groups = [];
			$result = $this->db->sql_query('SELECT cat_id, group_id FROM ' . $this->cat_groups_table);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->cat_groups[(int) $row['cat_id']][] = (int) $row['group_id'];
			}
			$this->db->sql_freeresult($result);
		}

		return $this->cat_groups;
	}

	public function get_cat_groups($cat_id)
	{
		$all = $this->all_cat_groups();

		return isset($all[(int) $cat_id]) ? $all[(int) $cat_id] : [];
	}

	public function set_cat_groups($cat_id, array $group_ids)
	{
		$cat_id = (int) $cat_id;
		$this->db->sql_query('DELETE FROM ' . $this->cat_groups_table . ' WHERE cat_id = ' . $cat_id);

		$rows = [];
		foreach (array_unique(array_filter(array_map('intval', $group_ids))) as $group_id)
		{
			$rows[] = ['cat_id' => $cat_id, 'group_id' => $group_id];
		}

		if ($rows)
		{
			$this->db->sql_multi_insert($this->cat_groups_table, $rows);
		}

		$this->cat_groups = $this->hidden = null;
	}

	public function delete_cat($cat_id)
	{
		$this->set_cat_groups($cat_id, []);
	}

	/**
	 * Categories the current user must not see (limited to groups the user is not in).
	 *
	 * @return int[]
	 */
	public function hidden_cats()
	{
		if ($this->hidden !== null)
		{
			return $this->hidden;
		}

		$this->hidden = [];
		$all = $this->all_cat_groups();

		if (!$all || $this->auth->acl_get('a_'))
		{
			return $this->hidden;
		}

		$mine = [];
		$sql = 'SELECT group_id FROM ' . USER_GROUP_TABLE . '
			WHERE user_id = ' . (int) $this->user->data['user_id'] . '
				AND user_pending = 0';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$mine[] = (int) $row['group_id'];
		}
		$this->db->sql_freeresult($result);

		foreach ($all as $cat_id => $groups)
		{
			if (!array_intersect($groups, $mine))
			{
				$this->hidden[] = (int) $cat_id;
			}
		}

		return $this->hidden;
	}

	public function can_see_cat($cat_id)
	{
		return !$cat_id || !in_array((int) $cat_id, $this->hidden_cats(), true);
	}

	/* ---------------------------------------------------------------
	 * Category passwords
	 * ------------------------------------------------------------- */

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
