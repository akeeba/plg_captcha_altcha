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
		// Just in case Joomla! removes this in the future...
		if (!class_exists(\AltchaOrg\Altcha\Altcha::class))
		{
			require_once __DIR__ . '/../vendor/autoload.php';
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
