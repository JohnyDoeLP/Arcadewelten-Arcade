<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\migrations;

/**
 * Forum integration: a bot user posts new games and season results.
 */
class v112 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['aw_arcade_bot_id']);
	}

	public static function depends_on()
	{
		return ['\arcadewelten\arcade\migrations\v111'];
	}

	public function update_schema()
	{
		return [
			'add_columns' => [
				$this->table_prefix . 'aw_arcade_cats' => [
					// News forum for games of this category (0 = default news forum).
					'cat_news_forum'	=> ['UINT', 0],
				],
				$this->table_prefix . 'aw_arcade_games' => [
					// Forum topic announcing the game (doubles as discussion thread).
					'game_topic_id'		=> ['UINT', 0],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'aw_arcade_cats'	=> ['cat_news_forum'],
				$this->table_prefix . 'aw_arcade_games'	=> ['game_topic_id'],
			],
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['aw_arcade_bot_id', 0]],
			['config.add', ['aw_arcade_news_forum', 0]],
			['config.add', ['aw_arcade_post_games', 1]],
			['config.add', ['aw_arcade_post_season', 1]],
			['config.add', ['aw_arcade_post_batch', 5]],
			['config.update', ['aw_arcade_version', '1.1.2']],
		];
	}
}
