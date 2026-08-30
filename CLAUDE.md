# CLAUDE.md

> **Read and follow ALL rules in `AGENTS.md` first.** This file contains Claude-specific instructions only.
> Do NOT treat this file as standalone, `AGENTS.md` is the source of truth.

---

## Downstream Packages

None. This plugin is an **inbound** connector: it verifies a site token FoPost presents on
the `fopost/v1` REST routes. It holds no FoPost API key, makes no outbound API call, and
exposes no action, filter or client accessor for another plugin to reuse.

`fopost-woocommerce` is therefore **not** a child of this plugin, despite the name. It talks
to the API itself through the `fopost/sdk` Composer package, so its parent is `fopost-php`.
Do not add a dependency between the two plugins without first giving this one an outbound
client and a public accessor worth sharing.

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
