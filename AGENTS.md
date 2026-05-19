# D2U Address - Agent Notes

Rules only. Short. Actionable.

## Core Rules

- Namespace: `FriendsOfRedaxo\D2UAddress`
- Legacy namespace for backward compatibility: `D2U_Address`
- PHP classes: 4 spaces. Module files: tabs
- Comments only in English
- Frontend labels via `Sprog\Wildcard::get()`, backend labels via `rex_i18n::msg()` with keys from `lang/`

## When Changing

- Keep backend translation keys in sync across all files under `lang/`
- For changes under `modules/20/*`: check or update changelog in `pages/help.changelog.php`
- Raise revision in `lib/Module.php` only once per release
- If target version in changelog already has `-DEV`: do not raise again in same phase
- Use real umlauts in changelog files, AGENTS.md, and README.md

## Maintenance

- Keep only recurring pitfalls, fixed conventions, and agent-relevant workflows here
