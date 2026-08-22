# AGENTS.md

> **This is the single source of truth for all AI coding agents** working in this repository.
> Agent-specific files (`CLAUDE.md`) extend this file, they do not replace it.

---

## Project Overview

**FoPost for WordPress** is the connector plugin that links a WordPress site to the hosted [FoPost](https://fopost.com) service. Its whole job is the site-token flow: the site owner generates a token, pastes it into FoPost, and FoPost then uses that token to publish content into WordPress.

| Property | Value |
|---|---|
| **Type** | WordPress plugin |
| **WordPress.org slug** | `fopost` |
| **Text Domain** | `fopost` |
| **Admin menu slug** | `fopost` |
| **PHP namespace** | `Fopost\Wp\` |
| **PHP Version** | 8.1+ (strict types required) |
| **Runtime dependencies** | None |
| **License** | GPL-2.0-or-later |

> **Brand name:** display text is always **FoPost** (capital F, capital P). Slugs, option keys, domains, and package names are lowercase `fopost`. The PHP namespace segment is `Fopost`.

---

## What This Plugin Is Not

This plugin contains **zero platform adapters**. It never talks to Twitter, Facebook, LinkedIn, Telegram, or any other network, and it never will. Social publishing lives in the separate free plugin `fopost/social-wordpress`, built on `fopost/social-core`.

Never add a dependency on `fopost/social-core` here, and never add a "send my post to X" feature. If a request implies one, it belongs in the other repo.

---

## Text Domain Rules

The text domain is **`fopost`**, matching the plugin folder name inside `wp-content/plugins/`. WordPress Plugin Check requires the match.

```php
__('Settings', 'fopost')
esc_html_e('Generate Token', 'fopost')
```

`'fopost'` is also the admin menu slug. Never use `'fopost-wp'` (the repo name) as a text domain.

---

## Directory Structure

```
fopost.php                 # Plugin header, constants, bootstrap
uninstall.php              # Data removal on delete
src/
├── Plugin.php             # Singleton, wires the admin page and REST routes
├── Activator.php          # Activation: legacy connection migration
├── Deactivator.php        # Deactivation: clears scheduled hooks
├── Uninstaller.php        # Removes options, post meta, transients
├── TokenService.php       # Site token: generate, verify, revoke, migrate
├── Settings.php           # Incoming content policy: status, author, post type
├── helpers.php            # fopost_is_connected()
├── Admin/
│   ├── SettingsPage.php   # Admin screen and admin-post handlers
│   └── views/
│       └── settings-page.php
└── Rest/
    └── RestController.php # fopost/v1 routes, X-Fopost-Token auth
```

---

## Security Invariants

Break any of these and a customer's site is exposed.

1. **The plaintext token is never stored.** Only `hash('sha256', $token)` is persisted. Generation reveals it once through a short-lived per-user transient.
2. **Token comparison is constant time** (`hash_equals`). Never `===` on a secret.
3. **Delete is scoped to content the plugin created.** `deletePost` refuses any post lacking the plugin's meta key. The legacy meta key is accepted read-only for migrated sites.
4. **Uploads are checked by bytes, not by name.** `wp_check_filetype_and_ext` runs after the write; a mismatch deletes the file.
5. **Every admin-post handler checks `manage_options` and a nonce** before touching state.
6. **The token grants no other WordPress access.** It authenticates the `fopost/v1` routes and nothing else. Never widen its reach.

---

## Option and Meta Keys

| Key | Meaning |
|---|---|
| `fopost_connection` | Token hash, hint, timestamps, and content settings |
| `fopost_db_version` | Schema marker |
| `_fopost_post` | Post meta marking content this plugin created |
| `owlstack_cloud` | **Legacy, read-only.** Copied once on activation, never written |
| `_owlstack_cloud` | **Legacy, read-only.** Accepted by delete authorization on migrated sites |

Never rename a key without a migration that reads the old value and writes the new one, leaving the old value in place.

---

## Coding Standards

- All PHP files start with `declare(strict_types=1);`
- Follow the repo's PHPCS ruleset (`phpcs.xml.dist`): WordPress security, I18n, DB, and PHP sniffs on a PSR-style codebase. Do not run the raw `--standard=WordPress` ruleset, this codebase intentionally does not follow its formatting rules
- `phpcs:ignore` only when a violation is intentional and unavoidable, always with a reason
- Option, hook, transient, and function names use the `fopost_` prefix; constants use `FOPOST_`
- Never write an email address or a `mailto:` link. Support is https://fopost.com/contact
- Never name hosting, infrastructure, AI providers, or models anywhere user-facing
- Do not use em-dashes or en-dashes in prose

---

## Git Workflow

1. Branch from `main` (`feature/<description>`)
2. Run `make lint` and `make test`
3. Conventional commits: `<type>(<scope>): <description>`, one logical change each. No trailer blocks
4. Push and open a PR against `main`

---

## Release Checklist

- [ ] `make version-bump V=x.y.z` (updates `fopost.php` and `readme.txt`)
- [ ] `make version-check` passes
- [ ] `make lint` and `make test` pass
- [ ] Tag `vx.y.z` and push. CI builds the zip and cuts the GitHub release
- [ ] WordPress.org SVN publish stays manual (`make release`)
