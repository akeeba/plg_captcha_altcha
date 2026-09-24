A Joomla! plugin to implement [ALTCHA](https://altcha.org), the Open Source, proof-of-work, self-hosted CAPTCHA alternative.

This plugin implements the open-source, self-hosted, GDPR-compliant ALTCHA system. It does **NOT** require using a third-party server or paying for subscription fees.

🇪🇺 Made in the European Union

### Requirements

* Joomla 5.4 to 6.1.
* PHP 8.2 to 8.6.

### Usage

Download and install the extension's ZIP file.

Go to System, Manage, Plugins and enable “CAPTCHA - ALTCHA” plugin.

Go to System, Global Configuration and select this plugin as your Default CAPTCHA. If you have any extensions which were using a different CAPTCHA plugin explicitly configured in them, remember to edit their options and choose the “CAPTCHA - ALTCHA” plugin instead.

### Changelog

* Validate and sanitise custom CSS colour/dimension parameters, both in the form and before output
* Only disclose raw AJAX challenge exception details when JDEBUG is on; show a generic message otherwise
* Update bundled ALTCHA PHP library from 1.3.1 to 1.3.3