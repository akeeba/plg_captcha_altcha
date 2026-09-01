<?php
/*
 * @package     plg_captcha_altcha
 * @copyright   (C) 2025-2026 Akeeba Ltd
 * @license     GPL-3.0+
 */

namespace Akeeba\Plugin\Captcha\Altcha\Field;

defined('_JEXEC') || die;

use DateTimeImmutable;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

/**
 * Displays a notice about this plugin's deprecation, worded according to whether Joomla! itself already
 * ships ALTCHA support (Joomla! 6.1+) and whether today is past this plugin's end-of-maintenance date.
 *
 * @since 2.1.3
 */
class DeprecationnoticeField extends FormField
{
	/** @inheritDoc */
	protected $type = 'Deprecationnotice';

	/**
	 * The date this plugin stops receiving even security updates, after which the wording switches from
	 * “deprecated” to “obsolete” and the alert switches from informational to an error.
	 *
	 * @since 2.1.3
	 */
	private const END_OF_MAINTENANCE = '2027-10-15';

	/** @inheritDoc */
	protected function getLabel()
	{
		return '';
	}

	/** @inheritDoc */
	protected function getInput()
	{
		$isUnsupportedJoomla = version_compare(JVERSION, '6.1.0', 'ge');
		$isPastEndOfLife     = (new DateTimeImmutable('now'))->format('Y-m-d') >= self::END_OF_MAINTENANCE;

		$alertClass = $isPastEndOfLife ? 'alert-danger' : 'alert-info';

		$languageKey = match (true)
		{
			$isUnsupportedJoomla && $isPastEndOfLife   => 'PLG_CAPTCHA_ALTCHA_FIELD_DEPRECATION_UNSUPPORTED_OBSOLETE',
			$isUnsupportedJoomla && !$isPastEndOfLife  => 'PLG_CAPTCHA_ALTCHA_FIELD_DEPRECATION_UNSUPPORTED',
			!$isUnsupportedJoomla && $isPastEndOfLife  => 'PLG_CAPTCHA_ALTCHA_FIELD_DEPRECATION_MAINTAINED_OBSOLETE',
			default                                    => 'PLG_CAPTCHA_ALTCHA_FIELD_DEPRECATION_MAINTAINED',
		};

		return sprintf(
			'<div class="alert %s">%s</div>',
			$alertClass,
			Text::_($languageKey)
		);
	}
}
