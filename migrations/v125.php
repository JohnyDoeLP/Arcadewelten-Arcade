<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\migrations;

/**
 * Certificates for the winners of a season and of a tournament.
 *
 * What a certificate says is written down when it is issued. The highscores
 * it came from are reset with the next season and the standings of a
 * tournament can be recounted, so reading the numbers again later would
 * quietly change a certificate that somebody already has in hand.
 */
class v125 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'aw_arcade_certs');
	}

	public static function depends_on()
	{
		return ['\arcadewelten\arcade\migrations\v124'];
	}

	public function update_schema()
	{
		return [
			'add_tables' => [
				$this->table_prefix . 'aw_arcade_certs' => [
					'COLUMNS' => [
						'cert_id'		=> ['UINT', null, 'auto_increment'],
						// 'season' or 'tourney'
						'cert_type'		=> ['VCHAR:10', ''],
						// tournament id, 0 for a season
						'cert_ref'		=> ['UINT', 0],
						'user_id'		=> ['UINT', 0],
						'cert_place'	=> ['USINT', 0],
						// name of the tournament, empty for a season
						'cert_title'	=> ['VCHAR_UNI', ''],
						// records held, or points scored in a tournament
						'cert_value'	=> ['DECIMAL:20', 0],
						'cert_prize'	=> ['DECIMAL:20', 0],
						'cert_start'	=> ['TIMESTAMP', 0],
						'cert_end'		=> ['TIMESTAMP', 0],
						// name the member had when the certificate was issued
						'cert_name'		=> ['VCHAR_UNI', ''],
					],
					'PRIMARY_KEY' => 'cert_id',
					'KEYS' => [
						'user'	=> ['INDEX', 'user_id'],
						'ref'	=> ['INDEX', ['cert_type', 'cert_ref']],
					],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'aw_arcade_certs',
			],
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['aw_arcade_certs', 1]],
			['config.add', ['aw_arcade_certs_theme', 'classic']],
			['config.add', ['aw_arcade_certs_post', 1]],
			['config.add', ['aw_arcade_certs_page', 1]],
			['config.update', ['aw_arcade_version', '1.2.5']],
		];
	}
}
