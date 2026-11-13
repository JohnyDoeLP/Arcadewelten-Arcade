<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\migrations;

/**
 * Outlier detection, external content check, results of 0 leave the highscores.
 */
class v119 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'aw_arcade_games', 'game_external');
	}

	public static function depends_on()
	{
		return ['\arcadewelten\arcade\migrations\v118'];
	}

	public function update_schema()
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'aw_arcade_games' => [
					// Other servers named in the game's files ("host1,host2"), found by the local check.
					'game_external'			=> ['TEXT_UNI', ''],
					'game_external_checked'	=> ['BOOL', 0],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'aw_arcade_games' => ['game_external', 'game_external_checked'],
			],
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['aw_arcade_outlier_factor', 20]],
			['config.add', ['aw_arcade_outlier_min', 5]],
			['config.add', ['aw_arcade_outlier_notice', 0]],
			['config.add', ['aw_arcade_external_consent', 1]],
			['config.update', ['aw_arcade_version', '1.1.9']],
			['custom', [[$this, 'remove_zero_scores']]],
		];
	}

	/**
	 * Results of 0 no longer belong in the highscores.
	 */
	public function remove_zero_scores()
	{
		$this->db->sql_query('DELETE FROM ' . $this->table_prefix . 'aw_arcade_scores WHERE score <= 0');
	}
}
