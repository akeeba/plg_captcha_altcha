# Translation flow

The canonical translation language is en-GB. All other languages translate from en-GB.

**Why:** set up for issue #11 (machine translation into multiple languages). The glossaries keep
terminology consistent across translation runs.

**How to apply:** when asked to add or update a translation, always consult and update the appropriate
`build/glossaries/<lang>.md` file first, and always update `altcha.xml` when adding new language files.
Process large files in ~10–12 KiB chunks if hitting token limits.

## Steps

1. Before translating a language, check for an existing glossary in `build/glossaries/<lang>.md`.
   Create it if missing.
2. Translate both `plg_captcha_altcha.ini` and `plg_captcha_altcha.sys.ini` into
   `plugins/captcha/altcha/language/<lang>/`.
3. Update `plugins/captcha/altcha/altcha.xml` to add `<language tag="...">` entries for each new file.
4. Update the glossary with any new terms encountered during translation.

## File format

Joomla INI format — `KEY="Value"`. Escape double quotes as `\"` inside values. HTML is allowed in
values.

## Glossaries

`build/glossaries/<lang>.md` — Markdown tables mapping English terms to the target language.

## Languages currently supported

- en-GB (canonical)
- el-GR (Greek)
- nl-NL (Dutch)
- tr-TR (Turkish)
- de-DE (German)
- es-ES (Spanish) — added 2026-06-17
- fr-FR (French) — added 2026-06-17
- it-IT (Italian) — added 2026-06-17
- pt-PT (Portuguese Portugal) — added 2026-06-17
