<?php
/*
 * @package     plg_captcha_altcha
 * @copyright   (C) 2025-2026 Akeeba Ltd
 * @license     GPL-3.0+
 */

namespace Akeeba\Plugin\Captcha\Altcha\Extension;

defined('_JEXEC') || die;

use AltchaOrg\Altcha\Altcha as AltchaApi;
use AltchaOrg\Altcha\Challenge;
use AltchaOrg\Altcha\ChallengeOptions;
use AltchaOrg\Altcha\Hasher\Algorithm;
use DateInterval;
use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\CaptchaField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Session\SessionInterface;
use JsonException;

/**
 * Implements the self-hosted ALTCHA as a Joomla! CAPTCHA plugin.
 *
 * @since  1.0.0
 */
final class Altcha extends CMSPlugin implements SubscriberInterface
{
	/** @inheritDoc */
	public function __construct(array $config = [], ?CMSApplication $app = null)
	{
		if (version_compare(JVERSION, '5.9999.9999', 'lt'))
		{
			// Joomla! 5: legacy plugin initialisation with a Dispatcher object
			parent::__construct($app->getDispatcher(), $config);
		}
		else
		{
			// Joomla! 6 and later: plugin initialisation with just the plugin config
			parent::__construct($config);
		}


		$this->setApplication($app);
		$this->processExpiration();
	}

	public static function getSubscribedEvents(): array
	{
		return [
			'onAjaxAltcha' => 'ajaxHandler',
		];
	}

	/**
	 * Handles the AJAX callback through com_ajax
	 *
	 * @param   Event  $e  The event we are handling
	 *
	 * @return  void
	 * @since        1.0.0
	 *
	 * @noinspection PhpUnused
	 */
	public function ajaxHandler(Event $e)
	{
		$id = trim($this->getApplication()->input->getRaw('id', '') ?: '');

		@ob_end_clean();

		header('HTTP/1.1 200 OK');
		header('Content-Type: application/json');

		if (!Session::checkToken('get') || empty($id))
		{
			echo json_encode([]);
			exit();
		}

		try
		{
			$challenge = $this->generateChallenge($id);
		}
		catch (\Throwable $e)
		{
			$challenge = [
				'Exception' => $e->getMessage()
			];
		}

		$challengeJson = json_encode($challenge);

		/**
		 * Remove the maxnumber variable for improved security.
		 *
		 * @link https://altcha.org/docs/server-integration/#creating-a-challenge
		 */
		$temp = json_decode($challengeJson, true);
		unset($temp['maxnumber']);
		$challengeJson = json_encode($temp);

		echo $challengeJson;

		$this->getApplication()->close();
	}

	/**
	 * Initialises the CAPTCHA plugin.
	 *
	 * @param   string  $id  The id of the field.
	 *
	 * @return  bool  True on success (always).
	 * @since        1.0.0
	 * @noinspection PhpUnused
	 */
	public function onInit(string $id = 'altcha_1'): bool
	{
		static $initialised = false;

		if ($initialised)
		{
			return true;
		}

		$initialised = true;
		$app         = $this->getApplication();

		if (!$app instanceof CMSWebApplicationInterface)
		{
			return false;
		}

		$wam = $app->getDocument()->getWebAssetManager();

		if (!$wam->getRegistry()->exists('preset', 'plg_captcha_altcha.altcha'))
		{
			$wam->getRegistry()->addExtensionRegistryFile('plg_captcha_altcha');
		}

		$wam->usePreset('plg_captcha_altcha.altcha');

		$css = ($this->getCSS(false) ?: '') . ($this->getCSS(true) ?: '');

		if (!empty($css))
		{
			$wam->addInlineStyle($css);
		}

		return true;
	}

	/**
	 * Get the HTML for the ALTCHA field.
	 *
	 * @param   string|null  $name   The control name.
	 * @param   string       $id     The id for the control.
	 * @param   string       $class  Value for the HTML class attribute
	 *
	 * @return  string  The HTML to render the ALTCHA
	 * @since   1.1.0
	 */
	public function onDisplay(
		?string $name = null, string $id = 'altcha_1', string $class = ''
	): string
	{
		$autoMode   = $this->params->get('auto', 'onsubmit');
		$delay      = $this->params->get('delay', 0);
		$hideFooter = $this->params->get('hidefooter', 0) == 1;
		$hideLogo   = $this->params->get('hidelogo', 0) == 1;

		$this->loadLanguage('plg_captcha_altcha');

		$htmlAttributes = [
			'name'         => $name,
			'id'           => $id,
			'credentials'  => 'same-origin',
			'class'        => $class,
			'challengeurl' => $this->getChallengeUrl($id),
			'delay'        => $delay,
			'strings'      => json_encode(
				[
					'ariaLinkLabel' => Text::_('PLG_CAPTCHA_ALTCHA_ARIALINKLABEL'),
					'error'         => Text::_('PLG_CAPTCHA_ALTCHA_ERROR'),
					'expired'       => Text::_('PLG_CAPTCHA_ALTCHA_EXPIRED'),
					'footer'        => Text::_('PLG_CAPTCHA_ALTCHA_FOOTER'),
					'label'         => Text::_('PLG_CAPTCHA_ALTCHA_LABEL'),
					'verified'      => Text::_('PLG_CAPTCHA_ALTCHA_VERIFIED'),
					'verifying'     => Text::_('PLG_CAPTCHA_ALTCHA_VERIFYING'),
					'waitAlert'     => Text::_('PLG_CAPTCHA_ALTCHA_WAITALERT'),
				]
			),
			'hidefooter'   => (bool) $hideFooter,
			'hidelogo'     => (bool) $hideLogo,
			'auto'         => $autoMode,
		];

		return sprintf(
			"<altcha-widget %s></altcha-widget>",
			$this->arrayToString($htmlAttributes)
		);
	}

	/**
	 * Checks if the answer is correct.
	 *
	 * @param   string|null  $code  The answer.
	 *
	 * @return  bool
	 * @since        1.0.0
	 * @noinspection PhpUnused
	 */
	public function onCheckAnswer(?string $code = null): bool
	{
		// We need a solution to work with.
		if (empty(trim($code ?? '')))
		{
			return false;
		}

		try
		{
			$code = @base64_decode($code);
		}
		catch (Exception $e)
		{
			$code = null;
		}

		if (empty($code))
		{
			return false;
		}

		// The solution must be a JSON-encoded object with a `salt` property.
		try
		{
			$decoded = @json_decode($code, flags: JSON_THROW_ON_ERROR);
		}
		catch (JsonException $e)
		{
			$decoded = null;
		}

		if (!is_object($decoded) || !isset($decoded->salt) || !isset($decoded->number))
		{
			return false;
		}

		// Extract the custom `keyHash` parameter from the salt
		$parts = explode('?', $decoded->salt, 2);

		if (count($parts) < 2)
		{
			return false;
		}

		@parse_str($parts[1], $params);

		if (!is_array($params) || !isset($params['keyHash']) || empty($params['keyHash']))
		{
			return false;
		}

		// The keyHash must exist in the session
		/** @var SessionInterface $session */
		$session   = Factory::getContainer()->get(SessionInterface::class);
		$challenge = $session->get('altcha_challenge.' . $params['keyHash'], null);

		if (empty($challenge))
		{
			return false;
		}

		// Remove the challenge from the session, thus preventing reuse.
		$session->remove('altcha_challenge.' . $params['keyHash']);

		// Make sure the in-session challenge is valid
		try
		{
			$challenge = @json_decode($challenge, flags: JSON_THROW_ON_ERROR);
		}
		catch (JsonException $e)
		{
			$challenge = null;
		}

		if (
			!is_object($challenge)
			|| !isset($challenge->algorithm)
			|| empty($challenge->algorithm)
			|| !isset($challenge->challenge)
			|| empty($challenge->challenge)
			|| !isset($challenge->salt)
			|| empty($challenge->salt)
			|| !isset($challenge->signature)
			|| empty($challenge->signature))
		{
			return false;
		}

		// Verify the solution
		$altcha  = new AltchaApi($this->getApplication()->get('secret'));
		$payload = [
			'algorithm' => $challenge->algorithm,
			'challenge' => $challenge->challenge,
			'number'    => $decoded->number,
			'salt'      => $challenge->salt,
			'signature' => $challenge->signature,
		];

		return $altcha->verifySolution($payload, true);
	}

	/**
	 * Modify the CAPTCHA field if necesary when it's being set up in the form.
	 *
	 * @param   CaptchaField       $field    Captcha field instance
	 * @param   \SimpleXMLElement  $element  XML form definition
	 *
	 * @return  void
	 * @since        1.0.0
	 * @noinspection PhpUnused
	 */
	public function onSetupField(CaptchaField $field, \SimpleXMLElement $element)
	{
		// No-op, for now.
	}

	/**
	 * Generates and returns a URL to generate and return the ALTCHA challenge JSON for a given CAPTCHA field.
	 *
	 * @param   string  $id  The ID of the CAPTCHA field.
	 *
	 * @return  string  The generated challenge URL.
	 * @since   1.0.0
	 */
	private function getChallengeUrl(string $id = 'altcha_1'): string
	{
		return Route::_(
			sprintf(
				"index.php?option=com_ajax&plugin=altcha&group=captcha&format=raw&id=%s&%s=1",
				htmlentities($id, ENT_QUOTES, 'UTF-8'),
				Session::getFormToken()
			),
			xhtml: false,
			absolute: true
		);
	}

	/**
	 * Generate a chalenge, and store it in the session.
	 *
	 * @param   string  $id  The CAPTCHA field ID
	 *
	 * @return  Challenge
	 * @throws  Exception
	 * @since   1.0.0
	 */
	private function generateChallenge(string $id = 'altcha_1'): Challenge
	{
		$keyHash       = hash('sha256', $id);
		$hashAlgorithm = Algorithm::tryFrom($this->params->get('hash', Algorithm::SHA512->name)) ?? Algorithm::SHA512;
		$maxNumber     = $this->params->get('maxnumber', 50000);
		$saltLength    = $this->params->get('saltlength', 16);
		$expires       = $this->params->get('expires', 'PT1H');

		$options = new ChallengeOptions(
			$hashAlgorithm,
			$maxNumber,
			Date::getInstance()->add(new DateInterval($expires)),
			[
				'keyHash' => $keyHash,
			],
			$saltLength
		);

		$altcha    = new AltchaApi($this->getApplication()->get('secret'));
		$challenge = $altcha->createChallenge($options);

		Factory::getContainer()
			->get(SessionInterface::class)
			->set('altcha_challenge.' . $keyHash, json_encode($challenge));

		return $challenge;
	}

	/**
	 * Processes the expiration of challenges stored in the session.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	private function processExpiration(): void
	{
		/** @var SessionInterface $session */
		$session    = Factory::getContainer()->get(SessionInterface::class);
		$challenges = $session->get('altcha_challenge');

		if (empty($challenges) || (!is_array($challenges) && !is_object($challenges)))
		{
			return;
		}

		$challenges = (array) $challenges;

		foreach ($challenges as $key => $challenge)
		{
			try
			{
				$decoded = @json_decode($challenge, flags: JSON_THROW_ON_ERROR);
			}
			catch (JsonException $e)
			{
				$decoded = null;
			}

			if (!is_object($decoded) || !isset($decoded->salt) || empty($decoded->salt))
			{
				$session->remove('altcha_challenge.' . $key);

				continue;
			}

			@parse_str($decoded->salt, $params);

			// Skip over never-expiring challenges
			if (!isset($params['expires']) || empty($params['expires']) || !is_int($params['expires']))
			{
				continue;
			}

			$date = new Date('@' . $params['expires']);

			if ($date->toUnix() < time())
			{
				$session->remove('altcha_challenge.' . $key);

				continue;
			}
		}
	}

	/**
	 * Create a string out of an array.
	 *
	 * This is used to create HTML element attributes out of an associative array.
	 *
	 * It's adapted from Joomla's \Joomla\Utilities\ArrayHelper::toString with a few changes made:
	 * - The attribute value goes through `htmlentities()` to escape double quotes.
	 * - Boolean values control whether the key appears in the list of attributes without a value
	 *
	 * @param   array    $array         The array to map.
	 * @param   string   $innerGlue     The glue (optional, defaults to '=') between the key and the value.
	 * @param   string   $outerGlue     The glue (optional, defaults to ' ') between array elements.
	 * @param   boolean  $keepOuterKey  True if an array value's key should be output verbatim.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	private function arrayToString(
		array $array, string $innerGlue = '=', string $outerGlue = ' ', bool $keepOuterKey = false
	): string
	{
		$output = [];

		foreach ($array as $key => $item)
		{
			if (\is_array($item))
			{
				if ($keepOuterKey)
				{
					$output[] = $key;
				}

				// This is value is an array, go and do it again!
				$output[] = $this->arrayToString($item, $innerGlue, $outerGlue, $keepOuterKey);
			}
			elseif (is_bool($item))
			{
				if ($item)
				{
					$output[] = $key;
				}
			}
			else
			{
				$output[] = $key . $innerGlue .
				            '"' . htmlentities($item, ENT_COMPAT | ENT_HTML5, 'UTF-8') . '"';
			}
		}

		return implode($outerGlue, $output);
	}

	/**
	 * Generates custom CSS rules based on specified parameters and returns the CSS string.
	 *
	 * @param   bool  $darkMode  Indicates whether to generate CSS for Dark Mode. Defaults to false.
	 *
	 * @return  string|null  The generated custom CSS string, or null if no CSS is enabled or no valid parameters are
	 *                       provided.
	 * @since   1.0
	 */
	private function getCSS(bool $darkMode = false): ?string
	{
		$suffix  = $darkMode ? '_dark' : '';
		$enabled = $this->params->get('custom_css' . $suffix, 0);

		if (!$enabled)
		{
			return null;
		}

		$controls = [
			'border_width',
			'border_radius',
			'maximum_width',
			'color_base',
			'color_border',
			'color_text',
			'color_border_focus',
			'color_error_text',
			'color_footer_bg',
		];

		$css = '';

		foreach ($controls as $key)
		{
			$value = $this->params->get($key . $suffix, null);
			$value = (is_string($value) ? trim($value) : null) ?: null;

			if (empty($value))
			{
				continue;
			}

			$css .= sprintf("--altcha-%s: %s;", str_replace('_', '-', $key), $value);
		}

		if (empty($css))
		{
			return null;
		}

		$css = ':root{color-scheme: light dark;' . $css . '}';

		if ($darkMode)
		{
			$css = '@media(prefers-color-scheme: dark){' . $css . '}';
		}

		return $css;
	}
}