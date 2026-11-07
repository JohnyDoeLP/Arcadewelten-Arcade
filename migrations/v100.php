<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\migrations;

class v100 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['aw_arcade_version'])
			&& phpbb_version_compare($this->config['aw_arcade_version'], '1.0.0', '>=');
	}

	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v330\v330'];
	}

	public function update_schema()
	{
		return [
			'add_tables' => [
				$this->table_prefix . 'aw_arcade_cats' => [
					'COLUMNS' => [
						'cat_id'		=> ['UINT', null, 'auto_increment'],
						'cat_name'		=> ['VCHAR_UNI:255', ''],
						'cat_desc'		=> ['VCHAR_UNI:255', ''],
						'cat_password'	=> ['VCHAR:255', ''],
						'cat_order'		=> ['UINT', 0],
					],
					'PRIMARY_KEY' => 'cat_id',
				],
				$this->table_prefix . 'aw_arcade_games' => [
					'COLUMNS' => [
						'game_id'			=> ['UINT', null, 'auto_increment'],
						'cat_id'			=> ['UINT', 0],
						'game_name'			=> ['VCHAR_UNI:255', ''],
						'game_desc'			=> ['TEXT_UNI', ''],
						'game_dir'			=> ['VCHAR:100', ''],
						'game_scorevar'		=> ['VCHAR:100', ''],
						'game_file'			=> ['VCHAR:255', ''],
						'game_image'		=> ['VCHAR:255', ''],
						'game_type'			=> ['TINT:1', 1],
						'game_save_type'	=> ['TINT:1', 0],
						'game_scoretype'	=> ['TINT:1', 1],
						'game_width'		=> ['USINT', 640],
						'game_height'		=> ['USINT', 480],
						'game_active'		=> ['BOOL', 1],
						'game_plays'		=> ['UINT', 0],
						'game_added'		=> ['TIMESTAMP', 0],
					],
					'PRIMARY_KEY' => 'game_id',
					'KEYS' => [
						'scorevar'	=> ['UNIQUE', 'game_scorevar'],
						'cat'		=> ['INDEX', ['cat_id', 'game_active']],
					],
				],
				$this->table_prefix . 'aw_arcade_scores' => [
					'COLUMNS' => [
						'game_id'		=> ['UINT', 0],
						'user_id'		=> ['UINT', 0],
						'score'			=> ['DECIMAL:20', 0],
						'score_time'	=> ['TIMESTAMP', 0],
						'score_plays'	=> ['UINT', 0],
					],
					'PRIMARY_KEY' => ['game_id', 'user_id'],
					'KEYS' => [
						'user'	=> ['INDEX', 'user_id'],
					],
				],
				$this->table_prefix . 'aw_arcade_sessions' => [
					'COLUMNS' => [
						'play_id'		=> ['VCHAR:32', ''],
						'user_id'		=> ['UINT', 0],
						'game_id'		=> ['UINT', 0],
						'start_time'	=> ['TIMESTAMP', 0],
						'randchar1'		=> ['UINT', 0],
						'randchar2'		=> ['UINT', 0],
						'pending_score'	=> ['DECIMAL:20', 0],
						'pending_set'	=> ['BOOL', 0],
					],
					'PRIMARY_KEY' => 'play_id',
					'KEYS' => [
						'user_game'	=> ['INDEX', ['user_id', 'game_id']],
					],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'aw_arcade_cats',
				$this->table_prefix . 'aw_arcade_games',
				$this->table_prefix . 'aw_arcade_scores',
				$this->table_prefix . 'aw_arcade_sessions',
			],
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['aw_arcade_version', '1.0.0']],
			['config.add', ['aw_arcade_games_path', 'arcade/games/']],
			['config.add', ['aw_arcade_guest_play', 1]],
			['config.add', ['aw_arcade_scores_per_page', 25]],
			['config.add', ['aw_arcade_games_per_page', 30]],
			['config.add', ['aw_arcade_secret', bin2hex(random_bytes(32))]],

			['module.add', ['acp', 'ACP_CAT_DOT_MODS', 'ACP_AW_ARCADE']],
			['module.add', ['acp', 'ACP_AW_ARCADE', [
				'module_basename'	=> '\arcadewelten\arcade\acp\main_module',
				'modes'				=> ['settings', 'games', 'cats'],
			]]],
		];
	}
}
