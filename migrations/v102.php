<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\migrations;

/**
 * Points (Ultimate Points): cost per play, reward per round, season rewards for places 1-3.
 * Value -1 on a game or category means "inherit".
 */
class v102 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'aw_arcade_games', 'game_cost');
	}

	public static function depends_on()
	{
		return ['\arcadewelten\arcade\migrations\v101'];
	}

	protected function point_columns($prefix)
	{
		return [
			$prefix . '_cost'			=> ['DECIMAL:20', -1],
			$prefix . '_reward_play'	=> ['DECIMAL:20', -1],
			$prefix . '_reward_1'		=> ['DECIMAL:20', -1],
			$prefix . '_reward_2'		=> ['DECIMAL:20', -1],
			$prefix . '_reward_3'		=> ['DECIMAL:20', -1],
		];
	}

	public function update_schema()
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'aw_arcade_games'	=> $this->point_columns('game'),
				$this->table_prefix . 'aw_arcade_cats'	=> $this->point_columns('cat'),
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'aw_arcade_games'	=> array_keys($this->point_columns('game')),
				$this->table_prefix . 'aw_arcade_cats'	=> array_keys($this->point_columns('cat')),
			],
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['aw_arcade_points_enable', 0]],
			['config.add', ['aw_arcade_cost', 0]],
			['config.add', ['aw_arcade_reward_play', 0]],
			['config.add', ['aw_arcade_reward_1', 0]],
			['config.add', ['aw_arcade_reward_2', 0]],
			['config.add', ['aw_arcade_reward_3', 0]],
			['config.add', ['aw_arcade_season_start', time()]],
			['config.update', ['aw_arcade_version', '1.0.2']],
		];
	}
}
