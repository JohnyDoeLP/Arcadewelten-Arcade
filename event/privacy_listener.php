<?php
/**
 * Arcadewelten Arcade
 *
 * @copyright (c) 2026 Patrick Overmann, arcadewelten.eu
 * @license GNU General Public License, version 2 (GPL-2.0-only)
 */

namespace arcadewelten\arcade\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Appends the arcade section to the board's privacy policy (ucp.php?mode=privacy).
 *
 * ucp.php assigns AGREEMENT_TEXT after page_header() and the template has no
 * event inside the policy box, so the text is extended in core.page_footer,
 * right before the page is rendered. Works with every style.
 */
class privacy_listener implements EventSubscriberInterface
{
	protected $privacy;
	protected $template;
	protected $request;
	protected $user;

	public function __construct(\arcadewelten\arcade\core\privacy $privacy, \phpbb\template\template $template, \phpbb\request\request_interface $request, \phpbb\user $user)
	{
		$this->privacy = $privacy;
		$this->template = $template;
		$this->request = $request;
		$this->user = $user;
	}

	public static function getSubscribedEvents()
	{
		return [
			'core.page_footer'	=> 'privacy_section',
		];
	}

	public function privacy_section()
	{
		if (!$this->privacy->enabled()
			|| (isset($this->user->page['page_name']) ? $this->user->page['page_name'] : '') !== 'ucp.php'
			|| $this->request->variable('mode', '') !== 'privacy'
			|| !$this->template->retrieve_var('S_AGREEMENT'))
		{
			return;
		}

		$text = (string) $this->template->retrieve_var('AGREEMENT_TEXT');

		$this->template->assign_var('AGREEMENT_TEXT', $text . $this->privacy->html());
	}
}
