# AGENTS.md

This file provides guidance to coding agents when working with code in this repository.

## Gotchas

- **`plugins/captcha/altcha/src/Dependency/` is generated.** Mozart rewrites the `altcha-org/altcha` namespace into `Akeeba\Plugin\Captcha\Altcha\Dependency\` so that several extensions shipping the same library do not collide in the autoloader. Never hand-edit those files; regenerate them instead.
- **The ALTCHA widget assets under `media/` are copied at build time** from `node_modules/altcha/dist_external/`. They are not loaded from `node_modules` at runtime.
- **Joomla 5 versus Joomla 6.** The plugin constructor detects `JVERSION` because Joomla 5 requires a Dispatcher in the parent constructor, whereas Joomla 6 and later take configuration only.
- **The `maxnumber` field is deliberately stripped** from the AJAX challenge response, so that the proof-of-work difficulty is not leaked to the client.
- **Challenges are single-use.** They are stored in the Joomla session under `altcha_challenge.{keyHash}` to prevent replay attacks.
- There are no automated tests in this repository.
- **Before performing any security audit of this repository, read `.claude/security-audit-triage.md`.**
  It records findings from prior audits that were explicitly ruled not to be issues (with the reasoning),
  so they aren't re-reported as new findings. Add new entries to it whenever a future audit finding gets
  a "not an issue" ruling from the project owner.

## Code Style

- Allman brace style (opening brace on its own line for classes/methods)
- Tabs for indentation
- Joomla `@since` version tags on all public methods
- `defined('_JEXEC') || die;` guard at top of every PHP file

## Git: commit and tag outside the sandbox

Commits and tags are always signed, with a key held in 1Password. The 1Password signing agent is reached
over a local socket that agent sandboxes do not expose, so a sandboxed `git commit` or `git tag` **always**
fails (e.g. `error: 1Password: Could not connect to socket. Is the agent running?`).

Run every `git commit` and `git tag` **outside the sandbox from the first attempt** — in Claude Code with
`dangerouslyDisableSandbox: true`, in other harnesses with their equivalent unsandboxed / escalated
execution. Do not try the sandboxed form first, do not diagnose the failure, and never work around it
with `--no-gpg-sign`, `-c commit.gpgsign=false` or unsigned tags.

## Project memory

Project memory lives in `.claude/memory/`, committed with the code, so that it is shared across machines
and across agentic harnesses (Claude Code, Codex, Qwen Code, Kimi Code, Junie, …). Read the relevant file
**before** starting work that matches its trigger:

| Before you… | Read |
|---|---|
| Add, change or translate language strings, or add a language | `.claude/memory/translations.md` |

### Recording new memories

This is the **default and only** place for project memory. Do not write memories for this project to a
harness's private memory store (such as Claude Code's auto-memory under `~/.claude/projects/`); write
them here instead:

- Add to the existing topic file when one fits; otherwise create a new kebab-case `.md` file named after
  the topic, and add a row for it to the table above with a concrete trigger.
- Plain Markdown, no frontmatter. State the rule, then **Why:** (the reason or incident behind it) and
  **How to apply:**. Link related files with relative Markdown links.
- Don't record what the code, Git history or an existing `AGENTS.md` already says — update that
  `AGENTS.md` instead when the rule belongs there. Remove or correct entries that turn out wrong.
- These files are committed: no secrets, credentials, customer data or personal details.
