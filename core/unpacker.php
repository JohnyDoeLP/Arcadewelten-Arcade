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

	/** @var array arguments for the language key of the last error */
	public $error_args = [];

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
		$this->skipped = [];
		$this->error_args = [];

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

		try
		{
			$game = $this->find_game($zip, $entries, $file_name);
		}
		catch (\RuntimeException $e)
		{
			$zip->close();
			throw $e;
		}

		$prefix = $game['root'];
		$dir = $game['dir'];

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

		$install = $dir . '.php';
		$written = 0;

		foreach ($entries as $entry)
		{
			// Everything beside the game folder stays out: a read-me, a change
			// log, the folders a packing program adds of its own.
			if ($prefix !== '' && strpos($entry['name'], $prefix . '/') !== 0)
			{
				$this->skipped[] = $entry['name'];
				continue;
			}

			$rel = $prefix !== '' ? substr($entry['name'], strlen($prefix) + 1) : $entry['name'];

			// The install file is written under the name of the folder, whatever
			// it is called in the archive.
			if ($entry['name'] === $game['install'])
			{
				$rel = $install;
			}

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
	 * Which folder of the archive holds the game, and which file installs it.
	 *
	 * An archive rarely looks the way one would like. It may hold the game
	 * folder alone, or a whole arcade/games/ tree, or a read-me beside the
	 * folder. So the install file is searched for instead of assumed: first by
	 * its name (a PHP file named after the folder it sits in), then by what is
	 * written in it.
	 *
	 * @return array root (folder inside the archive), dir (folder to create), install (entry name)
	 */
	protected function find_game(\ZipArchive $zip, array $entries, $file_name)
	{
		$by_name = [];
		$by_content = [];

		foreach ($entries as $entry)
		{
			if (strtolower((string) pathinfo($entry['name'], PATHINFO_EXTENSION)) !== 'php')
			{
				continue;
			}

			$root = (string) dirname($entry['name']);
			$root = ($root === '.' || $root === '/') ? '' : $root;
			$base = basename($entry['name'], '.php');

			if ($root !== '' && $base === basename($root))
			{
				$by_name[$root] = $entry['name'];
				continue;
			}

			// Not named after its folder: read it, a game install file says so
			// itself. Only small files, nothing is executed.
			if ($entry['size'] > 262144)
			{
				continue;
			}

			$data = $zip->getFromName($entry['name']);

			if ($data !== false && strpos($data, '$game_data') !== false)
			{
				$by_content[$root] = $entry['name'];
			}
		}

		$found = $by_name ?: $by_content;

		if (count($found) > 1)
		{
			$names = array_map(function ($root)
			{
				return basename($root);
			}, array_keys($found));
			sort($names);
			$this->error_args = [implode(', ', array_slice($names, 0, 8)) . (count($names) > 8 ? ' …' : '')];

			throw new \RuntimeException('ACP_AW_ARCADE_ZIP_MANY_GAMES');
		}

		if (!$found)
		{
			$names = array_column($entries, 'name');
			$this->error_args = [implode(', ', array_slice($names, 0, 6)) . (count($names) > 6 ? ' …' : '')];

			throw new \RuntimeException('ACP_AW_ARCADE_ZIP_NO_INSTALL');
		}

		$root = (string) array_key_first($found);
		$dir = self::clean_dir($root !== '' ? basename($root) : basename($found[$root], '.php'));
		$dir = $dir !== '' ? $dir : self::clean_dir($file_name);

		if ($dir === '')
		{
			throw new \RuntimeException('ACP_AW_ARCADE_ZIP_NO_NAME');
		}

		return ['root' => $root, 'dir' => $dir, 'install' => $found[$root]];
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
