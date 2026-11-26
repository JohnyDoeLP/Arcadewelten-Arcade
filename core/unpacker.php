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

	/** @var bool whether the last archive replaced a game that was already there */
	public $replaced = false;

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
	 * @param bool   $replace   replace the files of a game that is already there
	 * @return string the folder that was created
	 * @throws \RuntimeException with a language key as the message
	 */
	public function unpack($tmp_file, $file_name, $replace = false)
	{
		$this->skipped = [];
		$this->error_args = [];
		$this->replaced = false;

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

		$games_dir = rtrim(str_replace('\\', '/', $base), '/');
		$final = $games_dir . '/' . $dir;
		$exists = is_dir($final);

		if ($exists && !$replace)
		{
			$zip->close();
			$this->error_args = [$dir];

			throw new \RuntimeException('ACP_AW_ARCADE_ZIP_EXISTS');
		}

		// An update is unpacked beside the game and only put in its place once
		// it is whole, so a broken archive cannot leave the game in pieces. The
		// leading dot keeps a leftover out of the list of games found.
		$target = $exists ? $games_dir . '/.aw-new-' . $dir : $final;

		if ($exists)
		{
			$this->remove($target);
		}

		$install = $dir . '.php';
		$written = 0;
		$gamedata = [];

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

			// A game that keeps its gamedata folder inside its own folder is
			// packed that way for good reason - everything together - but the
			// game looks for those files under arcade/gamedata/. Kept aside
			// here and written once the game itself stands.
			if (stripos($rel, 'gamedata/') === 0)
			{
				$gamedata[substr($rel, 9)] = $entry['name'];
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

		if ($exists && !$this->swap($target, $final))
		{
			$this->remove($target);

			throw new \RuntimeException('ACP_AW_ARCADE_ZIP_WRITE');
		}

		$this->replaced = $exists;
		$this->gamedata = $this->write_gamedata($tmp_file, $gamedata);

		return $dir;
	}

	/**
	 * Writes the gamedata files that came along inside the game folder to
	 * arcade/gamedata/, where an IBPro v3 game looks for them.
	 *
	 * Written after the game itself is in place: if the game does not make
	 * it, these files are not left behind either. An existing file is
	 * replaced - it belongs to the game that has just been written.
	 *
	 * @param array $files target path below arcade/gamedata/ => entry in the archive
	 * @return int how many were written
	 */
	protected function write_gamedata($tmp_file, array $files)
	{
		if (!$files)
		{
			return 0;
		}

		$base = $this->root_path . importer::GAMEDATA_PATH;

		if (!is_dir($base) && !@mkdir($base, 0755, true))
		{
			return 0;
		}

		$root = realpath($base);

		if ($root === false)
		{
			return 0;
		}

		$root = rtrim(str_replace('\\', '/', $root), '/');
		$zip = new \ZipArchive();

		if ($zip->open($tmp_file) !== true)
		{
			return 0;
		}

		$written = 0;

		foreach ($files as $rel => $entry)
		{
			$path = $root . '/' . $rel;

			if ($rel === '' || !$this->inside($path, $root))
			{
				$this->skipped[] = $entry;
				continue;
			}

			$folder = dirname($path);

			if (!is_dir($folder) && !@mkdir($folder, 0755, true))
			{
				$this->skipped[] = $entry;
				continue;
			}

			$data = $zip->getFromName($entry);

			if ($data === false || @file_put_contents($path, $data) === false)
			{
				$this->skipped[] = $entry;
				continue;
			}

			$written++;
		}

		$zip->close();

		return $written;
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

			// Ours hold $game_data; a file written by SMF Arcade holds
			// $config with the same things under other names, and the
			// importer translates it.
			if ($data !== false && (strpos($data, '$game_data') !== false
				|| (strpos($data, '$config') !== false && strpos($data, "'gname'") !== false)))
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
			// PHP files first: this message is about a missing installation
			// file, and showing six entries in the order the archive happens
			// to store them can hide the very file the admin is looking for.
			$names = array_column($entries, 'name');
			$php = array_values(array_filter($names, function ($n) {
				return strtolower((string) pathinfo($n, PATHINFO_EXTENSION)) === 'php';
			}));
			$names = array_merge($php, array_values(array_diff($names, $php)));
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
	 * Put the new game in the place of the old one. The old folder is moved
	 * aside first, so a failure here leaves the game as it was.
	 */
	protected function swap($new, $final)
	{
		$old = $final . '.aw-old-' . time();

		if (!@rename($final, $old))
		{
			return false;
		}

		if (!@rename($new, $final))
		{
			@rename($old, $final);

			return false;
		}

		$this->remove($old);

		return true;
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
