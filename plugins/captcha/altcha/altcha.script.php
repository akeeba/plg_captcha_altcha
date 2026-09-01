<?php
/**
 * @package   plg_captcha_altcha
 * @copyright (C) 2025-2026 Akeeba Ltd
 * @license   GPL-3.0+
 */

\defined('_JEXEC') || die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;

/**
 * Installation script for the CAPTCHA - ALTCHA plugin.
 *
 * Prevents the plugin from being installed on unsupported Joomla! and PHP versions.
 *
 * The version limits below are maintained by the build script; see extra.akcompat in composer.json.
 *
 * @since 2.1.3
 */
class PlgCaptchaAltchaInstallerScript
{
	/**
	 * The minimum Joomla! version required to install this plugin.
	 *
	 * @var   string
	 * @since 2.1.3
	 */
	private string $minimumJoomla = '5.4.0';

	/**
	 * The first Joomla! version which is NOT supported by this plugin.
	 *
	 * @var   string
	 * @since 2.1.3
	 */
	private string $maximumJoomla = '6.2';

	/**
	 * The minimum PHP version required to install this plugin.
	 *
	 * @var   string
	 * @since 2.1.3
	 */
	private string $minimumPhp = '8.2.0';

	/**
	 * The first PHP version which is NOT supported by this plugin.
	 *
	 * @var   string
	 * @since 2.1.3
	 */
	private string $maximumPhp = '8.7';

	/**
	 * Runs before installation, update, or uninstallation of the plugin.
	 *
	 * @param   string            $type    Installation type: install, update, discover_install, or uninstall.
	 * @param   InstallerAdapter  $parent  The parent installer adapter.
	 *
	 * @return  boolean  False to abort the installation.
	 * @since   2.1.3
	 */
	public function preflight(string $type, InstallerAdapter $parent): bool
	{
		// Never block the removal of an already installed plugin.
		if ($type === 'uninstall')
		{
			return true;
		}

		if (version_compare(PHP_VERSION, $this->minimumPhp, 'lt'))
		{
			$this->enqueueError(
				sprintf(
					'ALTCHA requires PHP %s or later. You are currently using PHP %s. Please ask your host to upgrade PHP.',
					$this->minimumPhp,
					PHP_VERSION
				)
			);

			return false;
		}

		// Note: $maximumPhp holds the first PHP version which is NOT supported, hence the 'ge' comparison.
		if (!empty($this->maximumPhp) && version_compare(PHP_VERSION, $this->maximumPhp, 'ge'))
		{
			$this->enqueueError(
				sprintf(
					'ALTCHA supports PHP versions lower than %s. You are currently using PHP %s which has not been tested with it. The installation cannot proceed.',
					$this->maximumPhp,
					PHP_VERSION
				)
			);

			return false;
		}

		if (version_compare(JVERSION, $this->minimumJoomla, 'lt'))
		{
			$this->enqueueError(
				sprintf(
					'ALTCHA requires Joomla! %s or later. You are currently using Joomla! %s. Please update Joomla! before installing this plugin.',
					$this->minimumJoomla,
					JVERSION
				)
			);

			return false;
		}

		// Note: $maximumJoomla holds the first Joomla! version which is NOT supported, hence the 'ge' comparison.
		if (!empty($this->maximumJoomla) && version_compare(JVERSION, $this->maximumJoomla, 'ge'))
		{
			$this->enqueueError(
				sprintf(
					'ALTCHA supports Joomla! versions lower than %s. Your site is using Joomla! %s which has not been tested with it. The installation cannot proceed.',
					$this->maximumJoomla,
					JVERSION
				)
			);

			return false;
		}

		return true;
	}

	/**
	 * Displays an error message to the user, if there is an application to display it with.
	 *
	 * @param   string  $message  The message to display.
	 *
	 * @return  void
	 * @since   2.1.3
	 */
	private function enqueueError(string $message): void
	{
		try
		{
			$app = Factory::getApplication();
		}
		catch (Throwable $e)
		{
			$app = null;
		}

		if ($app instanceof CMSApplicationInterface)
		{
			$app->enqueueMessage($message, 'error');

			return;
		}

		echo $message . "\n";
	}
}
