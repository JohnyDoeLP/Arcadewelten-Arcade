<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\core;

/**
 * Finds addresses of other servers inside a game's own files.
 *
 * Purely local: the files are read on this server, nothing is looked up on the
 * internet. It finds what is written in the files (e.g. an ad script, a library
 * from a CDN, the embed of a game portal), not what such a script may load later.
 */
class external
{
	/** Files that are looked at, and how much of them. */
	const EXTENSIONS = ['html', 'htm', 'js', 'mjs', 'css', 'json', 'xml', 'swf'];
	const MAX_FILES = 400;
	const CHECK_VERSION = 2;
	const MAX_BYTES = 20971520;

	/** Hosts that only appear as names, never as something a browser loads. */
	const IGNORE = [
		'www.w3.org', 'w3.org', 'ns.adobe.com', 'www.adobe.com', 'adobe.com', 'www.macromedia.com', 'macromedia.com',
		'purl.org', 'xmlns.com', 'schemas.microsoft.com', 'schemas.xmlsoap.org', 'json-schema.org',
		'example.com', 'www.example.com', 'localhost', 'www.apple.com',
	];

	protected $config;
	protected $request;
	protected $cache;

	public function __construct(\phpbb\config\config $config, \phpbb\request\request_interface $request, ?\phpbb\cache\driver\driver_interface $cache = null)
	{
		$this->config = $config;
		$this->request = $request;
		$this->cache = $cache;
	}

	/**
	 * Like scan_dir(), but remembers the result until a file in the folder changes.
	 * Used for the import list, which is shown on every visit of the games page.
	 */
	public function scan_dir_cached($path)
	{
		return $this->remember('ext', $path, function () use ($path) {
			return $this->scan_dir($path);
		});
	}

	/**
	 * Cache the result of $work for a game folder until a file in it changes.
	 */
	public function remember($kind, $path, callable $work)
	{
		$path = rtrim($path, '/\\');

		if (!$this->cache || !is_dir($path))
		{
			return $work();
		}

		// The arcade version is part of the key, so improved checks never use old results.
		$key = '_aw_arcade_' . $kind . '_' . sha1($path . '|' . $this->config['aw_arcade_version'] . '|' . self::CHECK_VERSION . '|' . $this->fingerprint($path));
		$value = $this->cache->get($key);

		if ($value === false)
		{
			$value = $work();
			$this->cache->put($key, $value, 7 * 86400);
		}

		return $value;
	}

	/**
	 * Changes whenever a file in the folder is added, removed or changed (names, sizes, times).
	 */
	protected function fingerprint($path)
	{
		$parts = [];
		$items = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS));

		foreach ($items as $item)
		{
			if ($item->isFile() && in_array(strtolower($item->getExtension()), self::EXTENSIONS, true))
			{
				$parts[] = $item->getPathname() . ':' . $item->getSize() . ':' . $item->getMTime();
			}

			if (count($parts) > self::MAX_FILES)
			{
				break;
			}
		}

		return sha1(implode('|', $parts));
	}

	/**
	 * Other servers named in the files of one game folder.
	 *
	 * @param string $path absolute path of the game folder (with or without trailing slash)
	 * @return string[] sorted host names
	 */
	public function scan_dir($path)
	{
		$path = rtrim($path, '/\\');

		if (!is_dir($path) || is_link($path))
		{
			return [];
		}

		$hosts = [];
		$files = 0;
		$items = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS));

		foreach ($items as $item)
		{
			if (!$item->isFile() || $item->isLink() || !in_array(strtolower($item->getExtension()), self::EXTENSIONS, true))
			{
				continue;
			}

			if (++$files > self::MAX_FILES)
			{
				break;
			}

			if ($item->getSize() > self::MAX_BYTES)
			{
				continue;
			}

			$data = @file_get_contents($item->getPathname());

			if ($data === false)
			{
				continue;
			}

			$found = strtolower($item->getExtension()) === 'swf' ? $this->hosts_in_swf($data) : $this->hosts_in_text($data);

			foreach ($found as $host)
			{
				$hosts[$host] = true;
			}
		}

		$hosts = array_keys($hosts);
		sort($hosts);

		return $hosts;
	}

	/** Reserved endings that never exist on the internet (placeholders, local networks). */
	const RESERVED_TLDS = ['invalid', 'test', 'example', 'localhost', 'local', 'lan', 'internal', 'home', 'corp'];

	/** File types a Flash game may load from another server. */
	const SWF_LOADS = 'swf|xml|jpe?g|png|gif|mp3|flv|f4v|js|json|php|aspx?|txt|cfm';

	/**
	 * Addresses that a browser would load: in quotes (src="…", 'https://…') or in url(…).
	 * Addresses only mentioned in comments (licence headers etc.) are not in quotes and are skipped,
	 * and so are plain links (<a href="…">), which only open when somebody clicks them.
	 */
	public function hosts_in_text($text)
	{
		$text = preg_replace('~<a\b[^>]*>~i', '', $text);

		preg_match_all('~(?:["\'`]|url\(\s*["\']?)\s*(?:https?:)?//([a-z0-9](?:[a-z0-9\-]*[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9\-]*[a-z0-9])?)+)(?::\d+)?[/"\'`?#)\s]~i', $text, $m);

		return $this->filter($m[1]);
	}

	/**
	 * Flash files: any http(s) address in the (unpacked) file.
	 */
	public function hosts_in_swf($data)
	{
		if (substr($data, 0, 3) === 'CWS')
		{
			$data = @gzuncompress(substr($data, 8));

			if (!is_string($data))
			{
				return [];
			}
		}
		else if (substr($data, 0, 3) !== 'FWS')
		{
			return [];
		}

		// Only addresses of files the game could load (e.g. .swf, .xml, .php); bare site addresses in old
		// games are mostly "more games" links or lists of sites the game may run on (sitelock).
		preg_match_all('~https?://([a-z0-9](?:[a-z0-9\-]*[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9\-]*[a-z0-9])?)+)(?::\d+)?/[^\s"\'<>\x00-\x1f]*\.(?:' . self::SWF_LOADS . ')\b~i', $data, $m);

		return $this->filter($m[1]);
	}

	protected function filter(array $hosts)
	{
		// The board's own domain, whatever it is (from the board settings and the current request),
		// counts as "own" – with and without "www.", including all its subdomains.
		$own = preg_replace('~^www\.~', '', strtolower((string) $this->config['server_name']));
		$current = strtolower((string) $this->request->server('HTTP_HOST', ''));
		$current = preg_replace(['~:\d+$~', '~^www\.~'], '', $current);
		$out = [];

		foreach ($hosts as $host)
		{
			$host = strtolower(rtrim($host, '.'));

			// Needs a real top-level domain; skips things like "a.b" from minified code.
			if (!preg_match('~\.([a-z]{2,24})$~', $host, $tld) || in_array($host, self::IGNORE, true) || in_array($tld[1], self::RESERVED_TLDS, true))
			{
				continue;
			}

			$bare = preg_replace('~^www\.~', '', $host);

			if ($this->is_own($bare, $own) || $this->is_own($bare, $current))
			{
				continue;
			}

			$out[$host] = true;
		}

		return array_keys($out);
	}

	protected function is_own($host, $own)
	{
		return $own !== '' && ($host === $own || substr($host, -strlen('.' . $own)) === '.' . $own);
	}

	/**
	 * Stored form ("host1,host2") -> list.
	 */
	public static function split($stored)
	{
		return array_values(array_filter(array_map('trim', explode(',', (string) $stored))));
	}
}
