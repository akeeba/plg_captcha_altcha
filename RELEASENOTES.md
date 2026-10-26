A Joomla! plugin to implement [ALTCHA](https://altcha.org), the Open Source, proof-of-work, self-hosted CAPTCHA alternative.

This plugin implements the open-source, self-hosted, GDPR-compliant ALTCHA system. It does **NOT** require using a third-party server or paying for subscription fees.

🇪🇺 Made in the European Union

### Requirements

* Joomla 5.4 to 6.2.
* PHP 8.2 to 8.6.

### Usage

Download and install the extension's ZIP file.

Go to System, Manage, Plugins and enable “CAPTCHA - ALTCHA” plugin.

Go to System, Global Configuration and select this plugin as your Default CAPTCHA. If you have any extensions which were using a different CAPTCHA plugin explicitly configured in them, remember to edit their options and choose the “CAPTCHA - ALTCHA” plugin instead.

### Changelog

* Joomla! 6.2 is now a supported version
* Cannot save plugin options: "RegexRule has invalid regex" error (regression in 2.1.3)
* Plugin no longer loads if the site is moved to an unsupported PHP or Joomla! version after installation
