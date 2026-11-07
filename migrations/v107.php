<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\migrations;

/**
 * Game ratings (1–5 stars, one per member and game).
 */
class v107 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_table_exists($this->table_prefix . 'aw_arcade_ratings');
	}

	public static function depends_on()
	{
		return ['\arcadewelten\arcade\migrations\v106'];
	}

	public function update_schema()
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'aw_arcade_games' => [
					// Kept in sync by core\ratings for fast lists and sorting.
					'game_rating_sum'	=> ['UINT', 0],
					'game_rating_count'	=> ['UINT', 0],
				],
			],
			'add_tables' => [
				$this->table_prefix . 'aw_arcade_ratings' => [
					'COLUMNS' => [
						'game_id'		=> ['UINT', 0],
						'user_id'		=> ['UINT', 0],
						'rating'		=> ['TINT:1', 0],
						'rating_time'	=> ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY' => ['game_id', 'user_id'],
					'KEYS' => [
						'user'	=> ['INDEX', 'user_id'],
					],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'aw_arcade_games'	=> ['game_rating_sum', 'game_rating_count'],
			],
			'drop_tables' => [
				$this->table_prefix . 'aw_arcade_ratings',
			],
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['aw_arcade_ratings', 1]],
			['config.update', ['aw_arcade_version', '1.0.7']],
		];
	}
}
