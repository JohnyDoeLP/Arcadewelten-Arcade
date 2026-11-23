<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\core;

/**
 * Certificates that have been issued.
 *
 * A certificate is written down at the moment it is earned. The highscores it
 * came from are reset with the next season and a tournament can be recounted,
 * so working the numbers out again later would quietly change a certificate
 * somebody already has in hand.
 */
class certs
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var string */
	protected $certs_table;

	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\config\config $config, $certs_table)
	{
		$this->db = $db;
		$this->config = $config;
		$this->certs_table = $certs_table;
	}

	public function enabled()
	{
		return !empty($this->config['aw_arcade_certs']);
	}

	public function in_posts()
	{
		return $this->enabled() && !empty($this->config['aw_arcade_certs_post']);
	}

	public function on_page()
	{
		return $this->enabled() && !empty($this->config['aw_arcade_certs_page']);
	}

	/**
	 * Write down one certificate.
	 *
	 * @param array $data cert_type, cert_ref, user_id, cert_place, cert_title,
	 *                    cert_value, cert_prize, cert_start, cert_end, cert_name
	 * @return int id of the new certificate
	 */
	public function issue(array $data)
	{
		$row = [
			'cert_type'		=> (string) $data['cert_type'],
			'cert_ref'		=> (int) (isset($data['cert_ref']) ? $data['cert_ref'] : 0),
			'user_id'		=> (int) $data['user_id'],
			'cert_place'	=> (int) $data['cert_place'],
			'cert_title'	=> (string) (isset($data['cert_title']) ? $data['cert_title'] : ''),
			'cert_value'	=> (float) (isset($data['cert_value']) ? $data['cert_value'] : 0),
			'cert_prize'	=> (float) (isset($data['cert_prize']) ? $data['cert_prize'] : 0),
			'cert_start'	=> (int) (isset($data['cert_start']) ? $data['cert_start'] : 0),
			'cert_end'		=> (int) (isset($data['cert_end']) ? $data['cert_end'] : time()),
			'cert_name'		=> (string) $data['cert_name'],
		];

		$this->db->sql_query('INSERT INTO ' . $this->certs_table . ' ' . $this->db->sql_build_array('INSERT', $row));

		return (int) $this->db->sql_nextid();
	}

	/**
	 * Issue certificates for the first three of a ranking.
	 *
	 * @param array $places up to three rows with user_id, username, value, prize
	 * @return array the ids, place => id
	 */
	public function issue_places($type, $ref, array $places, $title, $start, $end)
	{
		$ids = [];

		foreach (array_slice(array_values($places), 0, 3) as $i => $row)
		{
			if (empty($row['user_id']))
			{
				continue;
			}

			$ids[$i + 1] = $this->issue([
				'cert_type'		=> $type,
				'cert_ref'		=> $ref,
				'user_id'		=> (int) $row['user_id'],
				'cert_place'	=> $i + 1,
				'cert_title'	=> $title,
				'cert_value'	=> isset($row['value']) ? $row['value'] : 0,
				'cert_prize'	=> isset($row['prize']) ? $row['prize'] : 0,
				'cert_start'	=> $start,
				'cert_end'		=> $end,
				'cert_name'		=> isset($row['username']) ? $row['username'] : '',
			]);
		}

		return $ids;
	}

	public function get($cert_id)
	{
		$sql = 'SELECT * FROM ' . $this->certs_table . ' WHERE cert_id = ' . (int) $cert_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row ?: null;
	}

	/**
	 * Every certificate of one member, newest first.
	 */
	public function for_user($user_id, $limit = 0)
	{
		$sql = 'SELECT * FROM ' . $this->certs_table . '
			WHERE user_id = ' . (int) $user_id . '
			ORDER BY cert_end DESC, cert_id DESC';
		$result = $limit > 0 ? $this->db->sql_query_limit($sql, (int) $limit) : $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	public function count_for_user($user_id)
	{
		$sql = 'SELECT COUNT(*) AS num FROM ' . $this->certs_table . ' WHERE user_id = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$num = (int) $this->db->sql_fetchfield('num');
		$this->db->sql_freeresult($result);

		return $num;
	}

	/**
	 * The certificates of one season or one tournament, by place.
	 */
	public function for_ref($type, $ref)
	{
		$sql = 'SELECT * FROM ' . $this->certs_table . "
			WHERE cert_type = '" . $this->db->sql_escape($type) . "'
				AND cert_ref = " . (int) $ref . '
			ORDER BY cert_place ASC';
		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

	/**
	 * Members who left take their certificates with them.
	 */
	public function delete_users(array $user_ids)
	{
		$user_ids = array_map('intval', $user_ids);

		if (!$user_ids)
		{
			return;
		}

		$this->db->sql_query('DELETE FROM ' . $this->certs_table . ' WHERE ' . $this->db->sql_in_set('user_id', $user_ids));
	}

	/**
	 * A deleted tournament takes its certificates with it.
	 */
	public function delete_tourney($tourney_id)
	{
		$this->db->sql_query('DELETE FROM ' . $this->certs_table . "
			WHERE cert_type = 'tourney' AND cert_ref = " . (int) $tourney_id);
	}
}
