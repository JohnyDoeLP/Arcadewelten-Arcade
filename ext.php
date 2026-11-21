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
	 * Requires phpBB 3.3 and PHP 8.2. Says which of the two is missing,
	 * so the board owner does not only see phpBB's general refusal.
	 */
	public function is_enableable()
	{
		$config = $this->container->get('config');
		$errors = [];

		if (!phpbb_version_compare($config['version'], '3.3.0', '>='))
		{
			$errors[] = ['ACP_AW_ARCADE_NEED_PHPBB', '3.3.0', $config['version']];
		}

		if (!phpbb_version_compare(PHP_VERSION, '8.2.0', '>='))
		{
			$errors[] = ['ACP_AW_ARCADE_NEED_PHP', '8.2', PHP_VERSION];
		}

		if (!$errors)
		{
			return true;
		}

		return array_map([$this, 'enable_error'], $errors);
	}

	/**
	 * Turns one error into a sentence, in the language of the board when the
	 * language file can be read, in English when it cannot.
	 */
	protected function enable_error(array $error)
	{
		[$key, $want, $have] = $error;

		try
		{
			$language = $this->container->get('language');
			$language->add_lang('acp', 'arcadewelten/arcade');
			$text = $language->lang($key, $want, $have);

			if ($text !== $key)
			{
				return $text;
			}
		}
		catch (\Throwable $e)
		{
			// Falls through to the English sentence below.
		}

		$what = ($key === 'ACP_AW_ARCADE_NEED_PHP') ? 'PHP' : 'phpBB';

		return sprintf('Arcadewelten Arcade needs %1$s %2$s or newer. This board runs %1$s %3$s.', $what, $want, $have);
	}

	/** Notification types of the extension (duels). */
	const NOTIFICATION_TYPES = [
		'arcadewelten.arcade.notification.type.duel_challenge',
		'arcadewelten.arcade.notification.type.duel_result',
		'arcadewelten.arcade.notification.type.record_beaten',
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
