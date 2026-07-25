# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Gotchas

- **`plugins/captcha/altcha/src/Dependency/` is generated.** Mozart rewrites the `altcha-org/altcha` namespace into `Akeeba\Plugin\Captcha\Altcha\Dependency\` so that several extensions shipping the same library do not collide in the autoloader. Never hand-edit those files; regenerate them instead.
- **The ALTCHA widget assets under `media/` are copied at build time** from `node_modules/altcha/dist_external/`. They are not loaded from `node_modules` at runtime.
- **Joomla 5 versus Joomla 6.** The plugin constructor detects `JVERSION` because Joomla 5 requires a Dispatcher in the parent constructor, whereas Joomla 6 and later take configuration only.
- **The `maxnumber` field is deliberately stripped** from the AJAX challenge response, so that the proof-of-work difficulty is not leaked to the client.
- **Challenges are single-use.** They are stored in the Joomla session under `altcha_challenge.{keyHash}` to prevent replay attacks.
- There are no automated tests in this repository.

## Code Style

- Allman brace style (opening brace on its own line for classes/methods)
- Tabs for indentation
- Joomla `@since` version tags on all public methods
- `defined('_JEXEC') || die;` guard at top of every PHP file
