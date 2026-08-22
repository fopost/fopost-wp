# FoPost for WordPress

[![CI](https://github.com/fopost/fopost-wp/actions/workflows/ci.yml/badge.svg)](https://github.com/fopost/fopost-wp/actions/workflows/ci.yml)
[![License: GPL v2+](https://img.shields.io/badge/license-GPL--2.0--or--later-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

Connects a WordPress site to [FoPost](https://fopost.com) so content you compose there lands in WordPress.

The plugin does one job: it manages a **site token**. You generate the token here, paste it into FoPost together with your site URL, and FoPost presents it back on every request. Nothing else in WordPress is exposed.

This plugin does **not** publish to social platforms. That is a separate, free plugin (`fopost/social-wordpress`), which talks straight to the networks with your own app credentials and needs no FoPost account.

## What the token can do

The token reaches four routes and nothing else:

| Method | Route | Purpose |
|---|---|---|
| `GET` | `/wp-json/fopost/v1/site` | Read site name, URL, icon, and the configured content policy |
| `POST` | `/wp-json/fopost/v1/posts` | Create a post |
| `POST` | `/wp-json/fopost/v1/media` | Sideload a JPEG, PNG, GIF, or WebP image |
| `DELETE` | `/wp-json/fopost/v1/posts/<id>` | Trash or delete a post the token created |

Authentication is the `X-Fopost-Token` header, checked against a SHA-256 hash. There is no WordPress user session, no nonce, and no cookie involved. Deleting is limited to posts this plugin created, so the token can never touch your existing content.

## Install

**From a release zip**

1. Download `fopost-<version>.zip` from [Releases](https://github.com/fopost/fopost-wp/releases).
2. WordPress admin: Plugins, Add New, Upload Plugin.
3. Activate, then open **FoPost** in the admin menu.

**From source**

```bash
git clone https://github.com/fopost/fopost-wp.git fopost
cd fopost
composer install --no-dev --optimize-autoloader
```

Place the folder in `wp-content/plugins/fopost` and activate it.

## Connect

1. Open **FoPost** in the WordPress admin.
2. Click **Generate Token**. The plaintext token is shown once, so copy it immediately.
3. In FoPost, add a WordPress destination and paste the site URL plus the token.

Under **Incoming Content** you decide how posts arrive: honor the status FoPost asks for, force every post to draft for review, or always publish. You also pick the author and the post type.

## Upgrading from the OwlStack plugin

If the site previously ran the OwlStack-branded plugin, activation copies its stored connection across once, so the existing token keeps working and nothing needs regenerating. The old option is left in place untouched. Posts the previous plugin created stay deletable through the API.

## Development

```bash
composer install
make test     # phpunit
make lint     # phpcs
make build    # dist zip
```

Requires PHP 8.1+ and WordPress 6.4+.

## Support

Questions and bug reports: [fopost.com/contact](https://fopost.com/contact) or the [issue tracker](https://github.com/fopost/fopost-wp/issues).

## License

GPL-2.0-or-later. Copyright Porter Bridge, LLC.
