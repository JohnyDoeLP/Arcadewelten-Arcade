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
	const MAX_BYTES = 20971520;

	/** Hosts that only appear as names, never as something a browser loads. */
	const IGNORE = [
		'www.w3.org', 'w3.org', 'ns.adobe.com', 'www.adobe.com', 'adobe.com', 'www.macromedia.com', 'macromedia.com',
		'purl.org', 'xmlns.com', 'schemas.microsoft.com', 'schemas.xmlsoap.org', 'json-schema.org',
		'example.com', 'www.example.com', 'localhost', 'www.apple.com',
	];

	protected $config;
	protected $request;

	public function __construct(\phpbb\config\config $config, \phpbb\request\request_interface $request)
	{
		$this->config = $config;
		$this->request = $request;
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
		$own = strtolower((string) $this->config['server_name']);
		$current = strtolower((string) $this->request->server('HTTP_HOST', ''));
		$current = preg_replace('~:\d+$~', '', $current);
		$out = [];

		foreach ($hosts as $host)
		{
			$host = strtolower(rtrim($host, '.'));

			// Needs a real top-level domain; skips things like "a.b" from minified code.
			if (!preg_match('~\.([a-z]{2,24})$~', $host, $tld) || in_array($host, self::IGNORE, true) || in_array($tld[1], self::RESERVED_TLDS, true))
			{
				continue;
			}

			if ($host === $own || $host === $current || ($own !== '' && substr($host, -strlen('.' . $own)) === '.' . $own))
			{
				continue;
			}

			$out[$host] = true;
		}

		return array_keys($out);
	}

	/**
	 * Stored form ("host1,host2") -> list.
	 */
	public static function split($stored)
	{
		return array_values(array_filter(array_map('trim', explode(',', (string) $stored))));
	}
}
