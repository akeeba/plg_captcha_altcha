<?php
/*
 * @package     plg_captcha_altcha
 * @copyright   (C) 2025-2026 Akeeba Ltd
 * @license     GPL-3.0+
 */

defined('_JEXEC') or die;

use Akeeba\Plugin\Captcha\Altcha\Extension\Altcha;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;

return new class () implements ServiceProviderInterface {
	public function register(Container $container)
	{
		// Enforce minimum / maximum PHP and Joomla versions.
		$minimumPhp    = '8.2.0';
		$maximumPhp    = '8.7';
		$minimumJoomla = '5.4.0';
		$maximumJoomla = '6.2';

		if (
			version_compare(PHP_VERSION, $minimumPhp, 'lt')
			|| version_compare(PHP_VERSION, $maximumPhp, 'ge')
			|| version_compare(JVERSION, $minimumJoomla, 'lt')
			|| version_compare(JVERSION, $maximumJoomla, 'ge')
		)
		{
			return;
		}

		$container->set(
			PluginInterface::class,
			fn(Container $container) => new Altcha(
				(array) PluginHelper::getPlugin('captcha', 'altcha'),
				Factory::getApplication()
			)
		);
	}
};
