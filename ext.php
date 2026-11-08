<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade;

class ext extends \phpbb\extension\base
{
	/**
	 * Requires phpBB 3.3 and PHP 8.2.
	 */
	public function is_enableable()
	{
		$config = $this->container->get('config');

		return phpbb_version_compare($config['version'], '3.3.0', '>=')
			&& phpbb_version_compare(PHP_VERSION, '8.2.0', '>=');
	}

	/** Notification types of the extension (duels). */
	const NOTIFICATION_TYPES = [
		'arcadewelten.arcade.notification.type.duel_challenge',
		'arcadewelten.arcade.notification.type.duel_result',
	];

	public function enable_step($old_state)
	{
		if ($old_state === false)
		{
			$manager = $this->container->get('notification_manager');
			foreach (self::NOTIFICATION_TYPES as $type)
			{
				$manager->enable_notifications($type);
			}

			return 'notifications';
		}

		return parent::enable_step($old_state);
	}

	public function disable_step($old_state)
	{
		if ($old_state === false)
		{
			$manager = $this->container->get('notification_manager');
			foreach (self::NOTIFICATION_TYPES as $type)
			{
				$manager->disable_notifications($type);
			}

			return 'notifications';
		}

		return parent::disable_step($old_state);
	}

	public function purge_step($old_state)
	{
		if ($old_state === false)
		{
			$manager = $this->container->get('notification_manager');
			foreach (self::NOTIFICATION_TYPES as $type)
			{
				try
				{
					$manager->purge_notifications($type);
				}
				catch (\phpbb\notification\exception $e)
				{
					// Type was never enabled.
				}
			}

			return 'notifications';
		}

		return parent::purge_step($old_state);
	}
}
