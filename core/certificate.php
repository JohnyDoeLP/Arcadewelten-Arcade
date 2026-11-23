<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\core;

/**
 * Draws a certificate as a PNG with GD.
 *
 * Three looks, one set of drawing helpers. Everything is drawn at three times
 * the size and scaled down at the end, which is what gives the wreath and the
 * seal smooth edges without any library beyond GD.
 */
class certificate
{
	/** Size of the finished image. */
	const W = 1000;
	const H = 700;

	/** Everything is drawn this many times larger and then scaled down. */
	const S = 3;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\user */
	protected $user;

	/** @var points */
	protected $points;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $ext_path;

	/** @var resource|\GdImage */
	protected $im;

	public function __construct(\phpbb\config\config $config, \phpbb\language\language $language, \phpbb\user $user, points $points, $root_path)
	{
		$this->points = $points;
		$this->config = $config;
		$this->language = $language;
		$this->user = $user;
		$this->root_path = $root_path;
		$this->ext_path = $root_path . 'ext/arcadewelten/arcade/';
	}

	/**
	 * GD with FreeType is needed; without it there is nothing to draw on.
	 */
	public function available()
	{
		if (!function_exists('imagecreatetruecolor') || !function_exists('imagettftext'))
		{
			return false;
		}

		$info = function_exists('gd_info') ? gd_info() : [];

		return !empty($info['FreeType Support']) && !empty($info['PNG Support']);
	}

	/**
	 * The themes a board can pick from.
	 */
	public static function themes()
	{
		return ['classic', 'light', 'dark'];
	}

	/**
	 * Path of a font file. A file of the same name under arcade/fonts/ in the
	 * board folder wins, so a board whose members write in a script DejaVu
	 * does not carry can drop its own font there and keep it over updates.
	 */
	protected function font($name)
	{
		$own = $this->root_path . 'arcade/fonts/' . $name . '.ttf';

		return is_file($own) ? $own : $this->ext_path . 'fonts/' . $name . '.ttf';
	}

	/**
	 * Draw a certificate.
	 *
	 * @param array  $cert  Row from the certificates table
	 * @param string $theme classic|light|dark, the configured one if empty
	 * @param int    $width scale the finished image down to this width
	 * @return string PNG data
	 */
	public function render(array $cert, $theme = '', $width = 0)
	{
		$theme = in_array($theme, self::themes(), true) ? $theme : (string) $this->config['aw_arcade_certs_theme'];
		$theme = in_array($theme, self::themes(), true) ? $theme : 'classic';

		$data = $this->strings($cert);

		$this->im = imagecreatetruecolor(self::W * self::S, self::H * self::S);
		imagealphablending($this->im, true);

		switch ($theme)
		{
			case 'dark':
				$this->draw_dark($data);
			break;

			case 'light':
				$this->draw_light($data);
			break;

			default:
				$this->draw_classic($data);
		}

		$w = ($width > 0 && $width < self::W) ? (int) $width : self::W;
		$h = (int) round($w * self::H / self::W);

		$out = imagecreatetruecolor($w, $h);
		imagecopyresampled($out, $this->im, 0, 0, 0, 0, $w, $h, self::W * self::S, self::H * self::S);
		imagedestroy($this->im);

		ob_start();
		imagepng($out, null, 9);
		imagedestroy($out);

		return ob_get_clean();
	}

	/**
	 * Everything the three looks put on the paper, already worded.
	 */
	protected function strings(array $cert)
	{
		$place = (int) $cert['cert_place'];
		$season = (string) $cert['cert_type'] === 'season';
		$value = (float) $cert['cert_value'];

		$fmt = $this->language->lang('AW_ARCADE_CERT_DATE');
		$period = $this->language->lang('AW_ARCADE_CERT_PERIOD',
			$this->user->format_date((int) $cert['cert_start'], $fmt),
			$this->user->format_date((int) $cert['cert_end'], $fmt)
		);

		return [
			'board'		=> (string) $this->config['sitename'],
			'kind'		=> $this->language->lang('AW_ARCADE_CERT_KIND_' . ($season ? 'SEASON' : 'TOURNEY') . ($place === 1 ? '' : '_P')),
			'place'		=> $place,
			'title'		=> $this->language->lang('AW_ARCADE_CERT_TITLE'),
			'won'		=> $this->language->lang('AW_ARCADE_CERT_PLACE_' . max(1, min(3, $place))),
			'subject'	=> $season ? '' : (string) $cert['cert_title'],
			'name'		=> (string) $cert['cert_name'],
			'for'		=> $this->language->lang('AW_ARCADE_CERT_FOR'),
			'value_label' => $this->language->lang($season ? 'AW_ARCADE_CERT_RECORDS_LABEL' : 'AW_ARCADE_CERT_POINTS_LABEL'),
			'value'		=> $season ? $this->language->lang('AW_ARCADE_CERT_RECORDS', (int) $value) : $this->num($value),
			// "First place - 14 records" reads better than repeating the label
			'won_line'	=> $this->language->lang('AW_ARCADE_CERT_PLACE_' . max(1, min(3, $place))) . ' – '
				. ($season ? $this->language->lang('AW_ARCADE_CERT_RECORDS', (int) $value) : $this->language->lang('AW_ARCADE_CERT_POINTS', $this->num($value))),
			'period'	=> $period,
			'period_label' => $this->language->lang($season ? 'AW_ARCADE_CERT_SEASON' : 'AW_ARCADE_CERT_TOURNEY'),
			'prize'		=> (float) $cert['cert_prize'] > 0 ? trim($this->num((float) $cert['cert_prize']) . ' ' . $this->points->name()) : '',
			'prize_label' => $this->language->lang('AW_ARCADE_CERT_PRIZE'),
			'issued'	=> $this->language->lang('AW_ARCADE_CERT_ISSUED', $this->user->format_date((int) $cert['cert_end'], 'd.m.Y')),
			'site'		=> $this->host(),
		];
	}

	protected function num($value)
	{
		return rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',');
	}

	protected function host()
	{
		$url = generate_board_url();
		$host = parse_url($url, PHP_URL_HOST);

		return $host ? (string) $host : $url;
	}

	/* ---------------------------------------------------------------
	 * The three looks
	 * ------------------------------------------------------------- */

	protected function draw_classic(array $d)
	{
		$serif = $this->font('serif');
		$bold = $this->font('serif-bold');
		$italic = $this->font('serif-italic');

		$this->fill('FAF5E8');
		$gold = $this->col('A8853C');
		$gold2 = $this->col('C9A94E');
		$ink = $this->col('3B3121');
		$ink2 = $this->col('6B5B3C');

		for ($i = 0; $i < 22; $i++)
		{
			$this->frame($i * 0.5, $i * 0.5, self::W - $i, self::H - $i, 0.6, imagecolorallocatealpha($this->im, 180, 150, 95, 104 + $i));
		}

		$this->frame(30, 30, self::W - 60, self::H - 60, 3, $gold);
		$this->frame(40, 40, self::W - 80, self::H - 80, 1, $gold2);

		foreach ([[30, 30], [self::W - 30, 30], [30, self::H - 30], [self::W - 30, self::H - 30]] as $p)
		{
			$this->diamond($p[0], $p[1], 9, $gold);
		}

		$cx = self::W / 2;
		$this->wreath($cx, 148, 52, $gold2);
		$this->ring($cx, 148, 62, 2, $gold);
		$this->centre($bold, 30, $cx, 148, $gold, (string) $d['place']);

		$this->text($bold, 42, $cx, 252, $ink, $this->upper($d['title']), 'c', 11);
		$this->line($cx - 150, 272, $cx - 20, 272, 1, $gold2);
		$this->line($cx + 20, 272, $cx + 150, 272, 1, $gold2);
		$this->diamond($cx, 272, 6, $gold);
		$this->text($serif, 15, $cx, 300, $ink2, $d['kind'], 'c', 3);

		$this->text($italic, 14, $cx, 362, $ink2, $d['for'], 'c');
		$this->text($bold, $this->fit($bold, 40, $d['name'], 620), $cx, 414, $ink, $d['name'], 'c');
		$this->line($cx - 190, 432, $cx + 190, 432, 1, $gold2);

		$this->text($serif, $this->fit($serif, 16, $d['won_line'], 760), $cx, 474, $ink, $d['won_line'], 'c');

		if ($d['subject'] !== '')
		{
			$this->text($bold, $this->fit($bold, 17, $d['subject'], 700), $cx, 504, $ink, $d['subject'], 'c');
			$this->text($serif, 13, $cx, 530, $ink2, $d['period'], 'c');
		}
		else
		{
			$this->text($serif, 14, $cx, 504, $ink2, $d['period'], 'c');
		}

		if ($d['prize'] !== '')
		{
			$prize = $d['prize_label'] . ': ' . $d['prize'];
			$this->roundrect($cx - 170, 556, 340, 40, 20, $this->col('F2E8CE'));
			$this->text($serif, $this->fit($serif, 14, $prize, 300), $cx, 582, $ink, $prize, 'c');
		}

		$this->line(80, 614, self::W - 80, 614, 1, $gold2);
		$this->text($serif, 12, 80, 642, $ink2, $d['board'] . ' · ' . $d['site']);
		$this->text($serif, 12, self::W - 80, 642, $ink2, $d['issued'], 'r');
	}

	protected function draw_dark(array $d)
	{
		$sans = $this->font('sans');
		$bold = $this->font('sans-bold');
		$light = $this->font('sans-light');

		$this->fill('1B2230');
		$gold = $this->col('E3B341');
		$white = $this->col('F4F6FA');
		$grey = $this->col('8D97A8');
		$panel = $this->col('232C3D');
		$rule = $this->col('3A4660');

		$sheen = imagecolorallocatealpha($this->im, 60, 80, 120, 118);
		for ($i = 0; $i < 70; $i++)
		{
			$this->line(-200 + $i * 9, 0, 100 + $i * 9, self::H, 3, $sheen);
		}

		$this->rect(0, 0, 10, self::H, $gold);
		$this->frame(36, 36, self::W - 72, self::H - 72, 1, $rule);

		$mx = 820;
		$my = 160;
		$this->wreath($mx, $my, 54, $gold);
		$this->disc($mx, $my, 86, $panel);
		$this->ring($mx, $my, 86, 2, $gold);
		$this->centre($bold, 34, $mx, $my, $gold, (string) $d['place']);

		$this->text($sans, 13, 90, 120, $gold, $this->upper($d['board']), 'l', 5);
		$this->text($light, $this->fit($light, 52, $d['kind'], 620), 90, 196, $white, $d['kind']);
		$this->line(90, 226, 420, 226, 2, $gold);

		$this->text($sans, 13, 90, 292, $grey, $this->upper($d['won']), 'l', 3);
		$this->text($bold, $this->fit($bold, 46, $d['name'], 660), 90, 350, $white, $d['name']);

		$this->text($sans, 13, 90, 420, $grey, $d['value_label'], 'l', 3);
		$this->text($bold, $this->fit($bold, 30, $d['value'], 300), 90, 462, $gold, $d['value']);

		if ($d['prize'] !== '')
		{
			$this->text($sans, 13, 430, 420, $grey, $d['prize_label'], 'l', 3);
			$this->text($bold, $this->fit($bold, 30, $d['prize'], 400), 430, 462, $gold, $d['prize']);
		}

		$label = $d['subject'] !== '' ? $d['subject'] . ' · ' . $d['period'] : $d['period'];
		$this->roundrect(90, 516, 760, 46, 23, $panel);
		$this->text($sans, $this->fit($sans, 14, $label, 710), 114, 545, $white, $label);

		$this->line(90, 616, self::W - 70, 616, 1, $rule);
		$this->text($sans, 12, 90, 646, $grey, $d['site']);
		$this->text($sans, 12, self::W - 70, 646, $grey, $d['issued'], 'r');
	}

	protected function draw_light(array $d)
	{
		$sans = $this->font('sans');
		$bold = $this->font('sans-bold');
		$light = $this->font('sans-light');

		$this->fill('FFFFFF');
		$blue = $this->col('105289');
		$white = $this->col('FFFFFF');
		$ink = $this->col('23282D');
		$grey = $this->col('6E7C8A');
		$soft = $this->col('EEF3F7');
		$rule = $this->col('D7DEE5');
		$gold = $this->col('D4A72C');

		for ($y = 0; $y < 210; $y++)
		{
			$t = $y / 210;
			$c = imagecolorallocate($this->im, (int) (16 + 10 * $t), (int) (82 + 42 * $t), (int) (137 + 47 * $t));
			imagefilledrectangle($this->im, 0, (int) ($y * self::S), self::W * self::S, (int) (($y + 1) * self::S), $c);
		}

		$this->rect(0, 206, self::W, 4, $gold);

		$this->text($sans, 13, 70, 74, $this->col('A8CBE4'), $this->upper($d['board']), 'l', 5);
		$this->text($light, 46, 70, 142, $white, $d['title']);
		$this->text($sans, $this->fit($sans, 15, $d['kind'], 600), 70, 176, $this->col('CFE2F0'), $d['kind']);

		$mx = 840;
		$my = 206;
		$this->disc($mx, $my, 128, $white);
		$this->disc($mx, $my, 114, $blue);
		$this->wreath($mx, $my, 44, $gold);
		$this->centre($bold, 40, $mx, $my, $gold, (string) $d['place']);

		$this->text($sans, 13, 70, 290, $grey, $this->upper($d['won']), 'l', 4);
		$this->text($bold, $this->fit($bold, 48, $d['name'], 640), 70, 348, $ink, $d['name']);
		$this->line(70, 378, 560, 378, 1, $rule);

		$rows = [[$d['value_label'], $d['value']], [$d['period_label'], $d['subject'] !== '' ? $d['subject'] : $d['period']]];

		if ($d['subject'] !== '')
		{
			$rows[] = [$this->language->lang('AW_ARCADE_CERT_WHEN'), $d['period']];
		}

		if ($d['prize'] !== '')
		{
			$rows[] = [$d['prize_label'], $d['prize']];
		}

		$y = 416;
		foreach (array_slice($rows, 0, 4) as $row)
		{
			$this->roundrect(70, $y, 700, 46, 8, $soft);
			$this->text($sans, 14, 94, $y + 30, $grey, $row[0]);
			$size = $this->fit($bold, 16, $row[1], 420, 12);
			$this->text($bold, $size, 750, $y + 30, $ink, $this->clip($bold, $size, $row[1], 420), 'r');
			$y += 56;
		}

		$this->line(70, 630, self::W - 70, 630, 1, $rule);
		$this->text($sans, 12, 70, 660, $grey, $d['site']);
		$this->text($sans, 12, self::W - 70, 660, $grey, $d['issued'], 'r');
	}

	/* ---------------------------------------------------------------
	 * Drawing helpers. Every coordinate is in finished-image pixels and
	 * is multiplied up here, so the looks above read at their real size.
	 * ------------------------------------------------------------- */

	protected function col($hex, $alpha = 0)
	{
		return imagecolorallocatealpha($this->im, hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)), $alpha);
	}

	protected function fill($hex)
	{
		imagefilledrectangle($this->im, 0, 0, self::W * self::S, self::H * self::S, $this->col($hex));
	}

	protected function rect($x, $y, $w, $h, $c)
	{
		imagefilledrectangle($this->im, (int) ($x * self::S), (int) ($y * self::S), (int) (($x + $w) * self::S), (int) (($y + $h) * self::S), $c);
	}

	protected function frame($x, $y, $w, $h, $t, $c)
	{
		imagesetthickness($this->im, max(1, (int) round($t * self::S)));
		imagerectangle($this->im, (int) ($x * self::S), (int) ($y * self::S), (int) (($x + $w) * self::S), (int) (($y + $h) * self::S), $c);
		imagesetthickness($this->im, 1);
	}

	protected function roundrect($x, $y, $w, $h, $r, $c)
	{
		$x *= self::S;
		$y *= self::S;
		$w *= self::S;
		$h *= self::S;
		$r *= self::S;

		imagefilledrectangle($this->im, (int) ($x + $r), (int) $y, (int) ($x + $w - $r), (int) ($y + $h), $c);
		imagefilledrectangle($this->im, (int) $x, (int) ($y + $r), (int) ($x + $w), (int) ($y + $h - $r), $c);

		foreach ([[$x + $r, $y + $r], [$x + $w - $r, $y + $r], [$x + $r, $y + $h - $r], [$x + $w - $r, $y + $h - $r]] as $p)
		{
			imagefilledellipse($this->im, (int) $p[0], (int) $p[1], (int) ($r * 2), (int) ($r * 2), $c);
		}
	}

	protected function disc($cx, $cy, $d, $c)
	{
		imagefilledellipse($this->im, (int) round($cx * self::S), (int) round($cy * self::S), (int) round($d * self::S), (int) round($d * self::S), $c);
	}

	protected function ring($cx, $cy, $d, $t, $c)
	{
		imagesetthickness($this->im, max(1, (int) round($t * self::S)));
		imageellipse($this->im, (int) round($cx * self::S), (int) round($cy * self::S), (int) round($d * self::S), (int) round($d * self::S), $c);
		imagesetthickness($this->im, 1);
	}

	protected function line($x1, $y1, $x2, $y2, $t, $c)
	{
		imagesetthickness($this->im, max(1, (int) round($t * self::S)));
		imageline($this->im, (int) round($x1 * self::S), (int) round($y1 * self::S), (int) round($x2 * self::S), (int) round($y2 * self::S), $c);
		imagesetthickness($this->im, 1);
	}

	protected function diamond($cx, $cy, $r, $c)
	{
		imagefilledpolygon($this->im, [
			(int) round($cx * self::S), (int) round(($cy - $r) * self::S),
			(int) round(($cx + $r) * self::S), (int) round($cy * self::S),
			(int) round($cx * self::S), (int) round(($cy + $r) * self::S),
			(int) round(($cx - $r) * self::S), (int) round($cy * self::S),
		], $c);
	}

	protected function leaf($cx, $cy, $rx, $ry, $deg, $c)
	{
		$p = [];
		$rad = deg2rad($deg);

		for ($t = 0; $t < 360; $t += 10)
		{
			$a = deg2rad($t);
			$x = cos($a) * $rx;
			$y = sin($a) * $ry;
			$p[] = (int) round(($cx + $x * cos($rad) - $y * sin($rad)) * self::S);
			$p[] = (int) round(($cy + $x * sin($rad) + $y * cos($rad)) * self::S);
		}

		imagefilledpolygon($this->im, $p, $c);
	}

	/**
	 * Laurel wreath: two arcs of leaves on a thin stem, open at the top.
	 */
	protected function wreath($cx, $cy, $r, $c)
	{
		foreach ([-1, 1] as $dir)
		{
			for ($t = 20; $t <= 160; $t += 2)
			{
				$a = deg2rad(90 + $dir * $t);
				$this->disc($cx + cos($a) * $r, $cy + sin($a) * $r, $r * 0.045, $c);
			}

			for ($i = 0; $i <= 6; $i++)
			{
				$a = deg2rad(90 + $dir * (28 + $i * 22));
				$sc = 1 - $i * 0.07;
				$this->leaf($cx + cos($a) * $r * 1.16, $cy + sin($a) * $r * 1.16, $r * 0.26 * $sc, $r * 0.105 * $sc, rad2deg($a) + 90, $c);
			}
		}
	}

	/**
	 * Width of a string at a given size, in finished-image pixels.
	 */
	protected function width($font, $size, $str)
	{
		$b = imagettfbbox($size * self::S, 0, $font, $str);

		return ($b[2] - $b[0]) / self::S;
	}

	/**
	 * Largest size at or below $size at which $str still fits into $max.
	 * Member names and tournament names are as long as somebody made them.
	 */
	protected function fit($font, $size, $str, $max, $min = 8)
	{
		while ($size > $min && $this->width($font, $size, $str) > $max)
		{
			$size--;
		}

		return $size;
	}

	/**
	 * Cuts a string down until it fits, with an ellipsis. For the places
	 * where shrinking the type any further would only make it unreadable.
	 */
	protected function clip($font, $size, $str, $max)
	{
		if ($this->width($font, $size, $str) <= $max)
		{
			return $str;
		}

		$chars = preg_split('//u', $str, -1, PREG_SPLIT_NO_EMPTY);

		while (count($chars) > 1)
		{
			array_pop($chars);
			$cut = rtrim(implode('', $chars)) . '…';

			if ($this->width($font, $size, $cut) <= $max)
			{
				return $cut;
			}
		}

		return $str;
	}

	/**
	 * @param string $align l, c or r, measured against $x
	 * @param float  $track extra space between the letters
	 */
	protected function text($font, $size, $x, $y, $c, $str, $align = 'l', $track = 0)
	{
		if ($str === '')
		{
			return;
		}

		if ($track <= 0)
		{
			$w = $this->width($font, $size, $str);
			$x = $align === 'c' ? $x - $w / 2 : ($align === 'r' ? $x - $w : $x);
			imagettftext($this->im, $size * self::S, 0, (int) round($x * self::S), (int) round($y * self::S), $c, $font, $str);

			return;
		}

		$chars = preg_split('//u', $str, -1, PREG_SPLIT_NO_EMPTY);
		$total = -$track;

		foreach ($chars as $ch)
		{
			$total += $this->width($font, $size, $ch) + $track;
		}

		$x = $align === 'c' ? $x - $total / 2 : ($align === 'r' ? $x - $total : $x);

		foreach ($chars as $ch)
		{
			imagettftext($this->im, $size * self::S, 0, (int) round($x * self::S), (int) round($y * self::S), $c, $font, $ch);
			$x += $this->width($font, $size, $ch) + $track;
		}
	}

	/**
	 * Puts a string in the middle of a point, measured by the ink of the
	 * glyphs and not by their advance width - that is what makes the number
	 * sit in the middle of the seal instead of a little below it.
	 */
	protected function centre($font, $size, $cx, $cy, $c, $str)
	{
		$b = imagettfbbox($size * self::S, 0, $font, $str);
		$x = $cx * self::S - ($b[2] - $b[0]) / 2 - $b[0];
		$y = $cy * self::S + ($b[1] - $b[7]) / 2 - $b[1];

		imagettftext($this->im, $size * self::S, 0, (int) round($x), (int) round($y), $c, $font, $str);
	}

	/**
	 * Upper case that also works on the letters PHP's strtoupper leaves alone.
	 */
	protected function upper($str)
	{
		return function_exists('mb_strtoupper') ? mb_strtoupper($str, 'UTF-8') : utf8_strtoupper($str);
	}
}
