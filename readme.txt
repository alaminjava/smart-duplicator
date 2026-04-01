=== Smart Duplicator ===
Contributors: alamin-hossain
Tags: duplicate post, clone post, copy page, duplicate page, post duplicator
Requires at least: 5.5
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Duplicate any post, page, or custom post type instantly — with full control over meta, taxonomies, and status.

== Description ==

**Smart Duplicator** is a clean, fast, zero-bloat plugin that lets you duplicate WordPress content with a single click.

= Key Features =

* One-click "Duplicate" row action on every post list
* Bulk duplicate — select many posts and duplicate at once
* Copies custom fields (post meta), categories, tags, taxonomies, and featured image
* Configurable: choose the new post's status, title suffix, and what to copy
* REST API endpoint for headless/programmatic use
* Developer-friendly: filters and action hooks
* No upsells. No tracking. GPL licensed.

== Installation ==

1. Upload the `smart-duplicator` folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **Settings → Smart Duplicator** to configure.

== Frequently Asked Questions ==

= Which post types are supported? =
All public post types by default, including Posts, Pages, and custom post types. You can restrict to specific types in Settings.

= Does it copy custom fields? =
Yes. All post meta is copied by default. You can disable this in Settings, or use the `smart_duplicator_skip_meta_keys` filter to exclude specific keys.

= Does it work with Gutenberg / Block Editor? =
Yes. Block content is copied exactly as-is.

= Is there a REST API? =
Yes. POST to `/wp-json/smart-duplicator/v1/duplicate/{post_id}` with authentication.

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
First stable release.
