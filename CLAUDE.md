# CLAUDE.md

> **Read and follow ALL rules in `AGENTS.md` first.** This file contains Claude-specific instructions only.
> Do NOT treat this file as standalone, `AGENTS.md` is the source of truth.

---

## Downstream Packages

These repos wrap this SDK and must be updated in lockstep:

- `fopost-woocommerce` — a WooCommerce add-on that depends on this plugin

**Whenever you change this SDK's public surface — a renamed method, a changed parameter,
a new or removed resource, a new error type, a bumped minimum language version — you must
open a matching PR in every repo listed above in the same session.** They are separate
git repos, checked out as siblings at `../fopost-<child>`. A parent release that silently
breaks a child is only discovered by the user who upgrades first.

Also bump the child's dependency constraint on this package and note the change in its
CHANGELOG when this package is released.

---

## Context Loading Priority

1. `AGENTS.md`: mandatory rules (scope, text domain, security invariants, key names, git workflow)
2. `fopost.php`: plugin header (verify Text Domain is `fopost`)
3. `src/TokenService.php` and `src/Rest/RestController.php`: the two files that carry every security invariant
4. The specific files related to the current task

---

## Critical Reminders

**This plugin is the FoPost connector, nothing else.** No platform adapters, no social delivery, no dependency on `fopost/social-core`. A request for social publishing belongs in the `fopost-social-wp` repo.

**Text domain = `'fopost'`.** If you see `'fopost-wp'` as the second argument to `__()`, `_e()`, `esc_html__()`, `esc_html_e()`, or `_n()`, that is a bug. Fix it.

**Never touch the legacy keys as writable state.** `owlstack_cloud` and `_owlstack_cloud` exist for one reason: sites upgrading from the previous plugin. They are read-only back-compat and are the only place the old brand name may appear.

---

## Tool Usage

- **Prefer grep and find** to locate code, never guess file locations or class names
- **Run PHPCS after every change**: `php -d xdebug.mode=off vendor/bin/phpcs`
- **Run the I18n check specifically**: `php -d xdebug.mode=off vendor/bin/phpcs --sniffs=WordPress.WP.I18n`
- **Run the tests**: `vendor/bin/phpunit`
- **Use `git diff`** to verify changes before committing
