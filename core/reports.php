<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\core;

/**
 * "Report game": members tell the admin that a game does not work properly.
 * One open report per member and game (a new report replaces the old one).
 */
class reports
{
	const REASONS = ['scores', 'graphics', 'mobile', 'loading', 'other'];

	protected $db;
	protected $config;
	protected $reports_table;
	protected $games_table;

	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, $reports_table, $games_table)
	{
		$this->db = $db;
		$this->config = $config;
		$this->reports_table = $reports_table;
		$this->games_table = $games_table;
	}

	public function enabled()
	{
		return !empty($this->config['aw_arcade_reports']);
	}

	public function add($game_id, $user_id, $reason, $text)
	{
		$reason = in_array($reason, self::REASONS, true) ? $reason : 'other';
		$text = truncate_string(trim((string) $text), 255, 255, false, '');

		$this->db->sql_query('DELETE FROM ' . $this->reports_table . ' WHERE game_id = ' . (int) $game_id . ' AND user_id = ' . (int) $user_id);
		$this->db->sql_query('INSERT INTO ' . $this->reports_table . ' ' . $this->db->sql_build_array('INSERT', [
			'game_id'		=> (int) $game_id,
			'user_id'		=> (int) $user_id,
			'report_reason'	=> $reason,
			'report_text'	=> $text,
			'report_time'	=> time(),
		]));
	}

	/**
	 * All reports, newest first, with game and reporter.
	 */
	public function get_all()
	{
		$sql = 'SELECT r.*, g.game_name, u.username, u.user_colour
			FROM ' . $this->reports_table . ' r
			LEFT JOIN ' . $this->games_table . ' g ON g.game_id = r.game_id
			LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = r.user_id
			ORDER BY r.report_time DESC';
		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	public function count()
	{
		$result = $this->db->sql_query('SELECT COUNT(report_id) AS total FROM ' . $this->reports_table);
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		return $total;
	}

	public function delete($report_id)
	{
		$this->db->sql_query('DELETE FROM ' . $this->reports_table . ' WHERE report_id = ' . (int) $report_id);
	}

	/**
	 * Close all reports of a game (e.g. after fixing it).
	 */
	public function delete_game($game_id)
	{
		$this->db->sql_query('DELETE FROM ' . $this->reports_table . ' WHERE game_id = ' . (int) $game_id);
	}

	public function delete_users(array $user_ids)
	{
		if ($user_ids)
		{
			$this->db->sql_query('DELETE FROM ' . $this->reports_table . ' WHERE ' . $this->db->sql_in_set('user_id', array_map('intval', $user_ids)));
		}
	}
}
