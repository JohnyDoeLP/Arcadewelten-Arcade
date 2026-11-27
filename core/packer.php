<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\core;

/**
 * Packs a game back into a ZIP, the way the uploader takes one.
 *
 * The game folder goes in at the top, and a gamedata folder belonging to the
 * game comes along inside it - unpacked again by this arcade, those files
 * land where they belong, and another arcade finds them where it looks.
 */
class packer
{
	/** Files beyond this many, or bytes beyond the size below, are refused. */
	const MAX_FILES = 4000;
	const MAX_BYTES = 209715200;

	/** @var games */
	protected $games;

	/** @var string */
	protected $root_path;

	public function __construct(games $games, $root_path)
	{
		$this->games = $games;
		$this->root_path = $root_path;
	}

	public function available()
	{
		return class_exists('\ZipArchive');
	}

	/**
	 * Writes the archive to a temporary file.
	 *
	 * @param array $game row from the games table
	 * @return string path of the file, which the caller deletes
	 * @throws \RuntimeException with a language key
	 */
	public function pack(array $game)
	{
		if (!$this->available())
		{
			throw new \RuntimeException('AW_ARCADE_DOWNLOAD_NO_ZIP');
		}

		$dir = (string) $game['game_dir'];

		if (!preg_match('/^[A-Za-z0-9_\-]+$/', $dir))
		{
			throw new \RuntimeException('AW_ARCADE_DOWNLOAD_FAILED');
		}

		$folders = [$dir => $this->games->games_fs_path() . $dir];
		$data = $this->games->gamedata_fs_path($game['game_scorevar']);

		if ($data !== '')
		{
			$folders[$dir . '/gamedata/' . basename((string) $game['game_scorevar'])] = $data;
		}

		$files = [];

		foreach ($folders as $prefix => $path)
		{
			$this->collect($path, $prefix, $files);
		}

		if (!$files)
		{
			throw new \RuntimeException('AW_ARCADE_DOWNLOAD_EMPTY');
		}

		$tmp = tempnam(sys_get_temp_dir(), 'awzip');

		if ($tmp === false)
		{
			throw new \RuntimeException('AW_ARCADE_DOWNLOAD_FAILED');
		}

		$zip = new \ZipArchive();

		if ($zip->open($tmp, \ZipArchive::OVERWRITE) !== true)
		{
			@unlink($tmp);

			throw new \RuntimeException('AW_ARCADE_DOWNLOAD_FAILED');
		}

		foreach ($files as $name => $path)
		{
			$zip->addFile($path, $name);
		}

		if (!$zip->close())
		{
			@unlink($tmp);

			throw new \RuntimeException('AW_ARCADE_DOWNLOAD_FAILED');
		}

		return $tmp;
	}

	/**
	 * Name the file is offered under.
	 */
	public function file_name(array $game)
	{
		return preg_replace('/[^A-Za-z0-9_\-]/', '', (string) $game['game_dir']) . '.zip';
	}

	/**
	 * Every file below $path, as archive name => path on disk. Follows no
	 * links and leaves the folder it was given through none of them.
	 */
	protected function collect($path, $prefix, array &$files)
	{
		$real = realpath($path);

		if ($real === false || !is_dir($real) || is_link($path))
		{
			return;
		}

		$root = rtrim(str_replace('\\', '/', $real), '/');
		$bytes = 0;

		$walk = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
			\RecursiveIteratorIterator::LEAVES_ONLY
		);

		foreach ($walk as $item)
		{
			if (!$item->isFile() || $item->isLink())
			{
				continue;
			}

			$full = realpath($item->getPathname());

			if ($full === false || strpos(str_replace('\\', '/', $full), $root . '/') !== 0)
			{
				continue;
			}

			$rel = substr(str_replace('\\', '/', $full), strlen($root) + 1);
			$bytes += (int) $item->getSize();

			if (count($files) >= self::MAX_FILES || $bytes > self::MAX_BYTES)
			{
				throw new \RuntimeException('AW_ARCADE_DOWNLOAD_TOO_BIG');
			}

			$files[$prefix . '/' . $rel] = $full;
		}
	}
}
