<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\core;

/**
 * Unpacks one uploaded game archive into the games folder.
 *
 * An archive comes from the outside, so nothing in it is trusted: paths that
 * point out of the folder are refused, and of all the files only one may be a
 * PHP file – the install file the arcade reads, named after the folder. Any
 * other program file is left out and named in the report.
 */
class unpacker
{
	/** Ceilings against archives that unpack to something huge. */
	const MAX_FILES = 2000;
	const MAX_BYTES = 209715200;

	/** Files that a game never needs and that change how the server behaves. */
	const BLOCKED = ['php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phps', 'phtml', 'phar', 'inc', 'cgi', 'pl', 'py', 'sh', 'htaccess', 'htpasswd', 'user.ini'];

	protected $games;

	/** @var string[] names of files that were left out */
	public $skipped = [];

	public function __construct(games $games)
	{
		$this->games = $games;
	}

	public function available()
	{
		return class_exists('\ZipArchive');
	}

	/**
	 * A folder name the arcade accepts, made from a file or folder name.
	 */
	public static function clean_dir($name)
	{
		$name = preg_replace('/\.zip$/i', '', (string) $name);
		$name = preg_replace('/[^A-Za-z0-9_\-]/', '', (string) $name);

		return substr((string) $name, 0, 60);
	}

	/**
	 * Unpack an archive into the games folder.
	 *
	 * @param string $tmp_file  the uploaded file
	 * @param string $file_name its name, used when the archive has no folder of its own
	 * @return string the folder that was created
	 * @throws \RuntimeException with a language key as the message
	 */
	public function unpack($tmp_file, $file_name)
	{
		if (!$this->available())
		{
			throw new \RuntimeException('ACP_AW_ARCADE_NO_ZIP');
		}

		$zip = new \ZipArchive();

		if ($zip->open($tmp_file) !== true)
		{
			throw new \RuntimeException('ACP_AW_ARCADE_ZIP_BROKEN');
		}

		$entries = $this->entries($zip);

		if (!$entries)
		{
			$zip->close();
			throw new \RuntimeException('ACP_AW_ARCADE_ZIP_EMPTY');
		}

		$prefix = $this->common_folder($entries);
		$dir = self::clean_dir($prefix !== '' ? $prefix : $file_name);

		if ($dir === '')
		{
			$zip->close();
			throw new \RuntimeException('ACP_AW_ARCADE_ZIP_NO_NAME');
		}

		// An absolute path, so the folder checks below cannot trip over a
		// relative games path like ./../arcade/games/.
		$base = realpath($this->games->games_fs_path());

		if ($base === false)
		{
			$zip->close();
			throw new \RuntimeException('ACP_AW_ARCADE_ZIP_WRITE');
		}

		$target = rtrim(str_replace('\\', '/', $base), '/') . '/' . $dir;

		if (is_dir($target))
		{
			$zip->close();
			throw new \RuntimeException('ACP_AW_ARCADE_ZIP_EXISTS');
		}

		$this->skipped = [];
		$install = $dir . '.php';
		$written = 0;

		foreach ($entries as $entry)
		{
			$rel = $prefix !== '' ? substr($entry['name'], strlen($prefix) + 1) : $entry['name'];

			if ($rel === '' || !$this->allowed($rel, $install))
			{
				$this->skipped[] = $entry['name'];
				continue;
			}

			$path = $target . '/' . $rel;

			if (!$this->inside($path, $target))
			{
				$this->skipped[] = $entry['name'];
				continue;
			}

			$folder = dirname($path);

			if (!is_dir($folder) && !@mkdir($folder, 0755, true))
			{
				$zip->close();
				$this->remove($target);
				throw new \RuntimeException('ACP_AW_ARCADE_ZIP_WRITE');
			}

			$data = $zip->getFromName($entry['name']);

			if ($data === false || @file_put_contents($path, $data) === false)
			{
				$zip->close();
				$this->remove($target);
				throw new \RuntimeException('ACP_AW_ARCADE_ZIP_WRITE');
			}

			$written++;
		}

		$zip->close();

		if (!$written || !is_file($target . '/' . $install))
		{
			$this->remove($target);
			throw new \RuntimeException('ACP_AW_ARCADE_ZIP_NO_INSTALL');
		}

		return $dir;
	}

	/**
	 * The files of the archive, with the ceilings checked on the way.
	 */
	protected function entries(\ZipArchive $zip)
	{
		$entries = [];
		$bytes = 0;

		if ($zip->numFiles > self::MAX_FILES)
		{
			throw new \RuntimeException('ACP_AW_ARCADE_ZIP_TOO_MANY');
		}

		for ($i = 0; $i < $zip->numFiles; $i++)
		{
			$stat = $zip->statIndex($i);

			if ($stat === false)
			{
				continue;
			}

			$name = str_replace('\\', '/', $stat['name']);

			// Folder entries are made on the way, from the files in them.
			if (substr($name, -1) === '/')
			{
				continue;
			}

			$bytes += (int) $stat['size'];

			if ($bytes > self::MAX_BYTES)
			{
				throw new \RuntimeException('ACP_AW_ARCADE_ZIP_TOO_BIG');
			}

			$entries[] = ['name' => $name, 'size' => (int) $stat['size']];
		}

		return $entries;
	}

	/**
	 * The folder every file sits in, if there is one ('' otherwise).
	 */
	protected function common_folder(array $entries)
	{
		$first = $entries[0]['name'];
		$slash = strpos($first, '/');

		if ($slash === false)
		{
			return '';
		}

		$prefix = substr($first, 0, $slash);

		foreach ($entries as $entry)
		{
			if (strpos($entry['name'], $prefix . '/') !== 0)
			{
				return '';
			}
		}

		return $prefix;
	}

	/**
	 * May this file be written? Only the install file may be a PHP file.
	 */
	protected function allowed($rel, $install)
	{
		if (strpos($rel, '..') !== false || substr($rel, 0, 1) === '/' || preg_match('/^[A-Za-z]:/', $rel) || strpos($rel, "\0") !== false)
		{
			return false;
		}

		$ext = strtolower((string) pathinfo($rel, PATHINFO_EXTENSION));
		$base = strtolower(basename($rel));

		if ($rel === $install)
		{
			return true;
		}

		return !in_array($ext, self::BLOCKED, true) && !in_array($base, ['.htaccess', '.htpasswd', '.user.ini', 'web.config'], true);
	}

	/**
	 * Does the path really stay inside the game folder?
	 */
	protected function inside($path, $target)
	{
		$path = str_replace('\\', '/', $path);
		$target = rtrim(str_replace('\\', '/', $target), '/') . '/';

		return strpos($path, $target) === 0 && strpos($path, '/../') === false;
	}

	/**
	 * Remove a half-written folder after an error.
	 */
	protected function remove($dir)
	{
		if (!is_dir($dir))
		{
			return;
		}

		$items = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);

		foreach ($items as $item)
		{
			$item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
		}

		@rmdir($dir);
	}
}
