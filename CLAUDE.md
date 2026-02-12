# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Joomla! 5/6 CAPTCHA plugin implementing ALTCHA — an open-source, proof-of-work, self-hosted CAPTCHA alternative. GDPR-compliant (no cookies, no third-party servers). Licensed GPL-3.0+.

**Package:** `plg_captcha_altcha`
**Namespace:** `Akeeba\Plugin\Captcha\Altcha`
**PHP requirement:** 8.2+
**Joomla compatibility:** 5.4 through 6.x

## Build System

Uses **Apache Phing** with a shared buildfiles import (`../buildfiles/phing/common.xml`).

```bash
# Full build (default target): npm install, compile CSS, package ZIP
phing

# Individual targets
phing npm-install      # Install JS deps and copy ALTCHA widget files into media/
phing compile-css      # Compile SCSS to minified CSS using sass
phing package          # Create installable ZIP in release/
phing release          # Deploy to GitHub releases
```

The `npm-install` target copies files from `node_modules/altcha/dist_external/` into `plugins/captcha/altcha/media/js/` and renames the CSS to `.scss` for compilation.

There are no automated tests in this repository.

## Architecture

### Plugin Structure

All plugin code lives under `plugins/captcha/altcha/`:

- **`src/Extension/Altcha.php`** — The entire plugin in a single class. Extends `CMSPlugin`, implements `SubscriberInterface`. Handles initialization, widget rendering, challenge generation, and answer verification.
- **`src/Dependency/`** — Mozart-renamespaced copy of the `altcha-org/altcha` PHP library (prefixed to `Akeeba\Plugin\Captcha\Altcha\Dependency\` to avoid autoloader conflicts with other extensions).
- **`services/provider.php`** — Joomla DI container service provider. Anonymous class returning the plugin instance.
- **`media/`** — Frontend assets (JS widget, CSS, `joomla.asset.json` for WebAsset Manager).
- **`language/`** — Translation files (en-GB, el-GR, nl-NL, tr-TR, de-DE).
- **`altcha.xml`** — Plugin manifest with all configurable parameters.

### How It Works

1. **Challenge flow:** `onDisplay()` renders an `<altcha-widget>` web component with a `challengeurl` pointing to `com_ajax`. The widget fetches a proof-of-work challenge via `ajaxHandler()` → `generateChallenge()`.
2. **Session storage:** Generated challenges are stored in the Joomla session under `altcha_challenge.{keyHash}` to prevent replay attacks. Each challenge is single-use.
3. **Verification:** `onCheckAnswer()` decodes the base64 solution, extracts the `keyHash` from the salt, retrieves the stored challenge from session, and calls `AltchaApi::verifySolution()`.
4. **Security:** The `maxnumber` field is stripped from the AJAX response to avoid leaking difficulty to the client.

### Joomla 5 / 6 Compatibility

The constructor detects `JVERSION` to handle the different parent class initialization between Joomla 5 (requires Dispatcher) and Joomla 6+ (config-only).

### Dependency Management

- **PHP (Composer):** `altcha-org/altcha` is the core library. Mozart (`coenjacobs/mozart`, dev dependency) rewrites its namespace to avoid conflicts when multiple extensions ship the same library.
- **JavaScript (NPM):** The `altcha` npm package provides the web component widget. Files are copied into `media/` during build — they are not loaded from `node_modules` at runtime.

## Code Style

- Allman brace style (opening brace on its own line for classes/methods)
- Tabs for indentation
- Joomla `@since` version tags on all public methods
- `defined('_JEXEC') || die;` guard at top of every PHP file
