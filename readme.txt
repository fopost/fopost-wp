=== FoPost ===
Contributors: alihesari
Donate link: https://fopost.com
Tags: publishing, content syndication, social media, scheduling, remote publishing
Requires at least: 6.4
Tested up to: 7.1
Stable tag: 0.1.1
Requires PHP: 8.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Connect this site to FoPost so posts you compose there are published into WordPress.

== Description ==

[FoPost](https://fopost.com) is a hosted dashboard for planning and publishing content. This plugin is the connector that lets FoPost deliver posts into your WordPress site.

It does one job: it manages a site token. You generate the token in the WordPress admin, paste it into FoPost along with your site URL, and FoPost presents it back on every request. Nothing else in WordPress is exposed.

**What the token can do**

The token reaches four REST routes and nothing else:

* Read site name, URL, icon, and the content policy you configured
* Create a post
* Sideload a JPEG, PNG, GIF, or WebP image
* Trash or delete a post this plugin created

Authentication is a single request header checked against a stored hash. No WordPress user session, no cookie, and no nonce is involved. Deletion is limited to content this plugin created, so the token can never touch posts you wrote yourself.

**You stay in control of how content arrives**

* **Post status**: honor what FoPost requests, force everything to draft for review, or always publish immediately
* **Post author**: every incoming post is attributed to the user you pick
* **Post type**: deliver into posts, pages, or any public custom post type

**Not a social publisher**

This plugin does not post to social networks. If that is what you want, install the separate free FoPost Social plugin, which talks straight to the networks using your own app credentials and needs no FoPost account.

== Installation ==

1. Upload the plugin to `wp-content/plugins/fopost`, or install it through Plugins, Add New.
2. Activate the plugin.
3. Go to **Settings > FoPost**.
4. Click **Generate Token** and copy the token. It is shown once and never again.
5. In FoPost, add a WordPress destination and paste your site URL together with the token.

== Frequently Asked Questions ==

= Do I need a FoPost account? =

Yes. This plugin is the connector for the hosted FoPost service. Without an account there is nothing to pair with.

= What happens if I revoke the token? =

FoPost can no longer publish to the site. Existing posts are untouched. Generate a new token and save it in FoPost to reconnect.

= Can the token be used to log in or read my data? =

No. It authenticates four specific routes and grants no other WordPress access. It cannot read posts, users, settings, or anything else.

= Can FoPost delete my existing posts? =

No. Deletion is refused for any post the plugin did not create.

= I used the OwlStack plugin before. Do I need to reconnect? =

No. On activation the existing connection is copied over once, so the token you already saved keeps working.

= Does this publish to Twitter, Facebook, or LinkedIn? =

Not this plugin. Social publishing lives in the separate free FoPost Social plugin.

== Third-Party Services ==

This plugin connects your site to FoPost, a hosted publishing service at
[fopost.com](https://fopost.com). You need a FoPost account for the plugin to do
anything.

The plugin does not send your content anywhere on its own. It receives requests
from FoPost and creates or updates posts on this site. To authorise those
requests it stores a key you generate in FoPost and verifies the signature on
every incoming request.

What FoPost receives is what you compose there; what this site receives is the
resulting post. Service terms: [Terms of Service](https://fopost.com/terms-of-service)
&middot; [Privacy Policy](https://fopost.com/privacy-policy) &middot;
[Data Processing Addendum](https://fopost.com/dpa).

== Changelog ==

= 0.1.1 =
* Fixed the "Start a free trial" button on the settings page, which pointed at a page that does not exist.

= 0.1.0 =
* First release: site token generation and revocation, incoming content policy (status, author, post type), and the FoPost REST routes for creating posts, uploading images, and deleting content the plugin created.
* Connections from the previously OwlStack-branded plugin are carried over on activation.

== Upgrade Notice ==

= 0.1.1 =
Fixes the broken sign-up link on the settings page.

= 0.1.0 =
First release.
