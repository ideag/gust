=== Gust ===
Contributors: ideag
Donate link: http://wordofpress.com/gust
Tags: ghost, admin panel, markdown, editor, dashboard, admin
Requires at least: 3.0.1
Tested up to: 4.2
Stable tag: 0.4.1
License: MIT
License URI: http://wordofpress.com/gust/license

Gust is a port of Ghost admin panel for WordPress.

== Description ==

At the begining, Ghost was supposed to be a fork of WordPress. Then there was a talk of a plugin, that would give a next-generation admin panel for WordPress. But in the process it became a new blogging platform, built on Node.js. This plugin is an attempt to bring the nice and clean admin panel of Ghost back to the WordPress ecosystem.

Current features:

*   **NEW** PHP 5.2 Compatability
*   **NEW** Autosave support
*   **NEW** Featured image (post-thumbnail) support
*   **NEW** Custom fields (post-meta) support
*   **NEW** Experimental custom post types support (have to be enabled via Options page)
*   **NEW** Add new category dialog
*   **NEW** Tighter integration with WordPress (Admin Bar links, Edit links, etc.)
*   Posts/Pages Markdown editor
*   Media upload integration
*   Post tags support
*   Post category support
*   Post scheduling support

== Installation ==

1. Upload `gust` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Make sure pretty permalinks are enabled
1. Go to yourdomain.com/gust

== Frequently Asked Questions ==

= Empty post list with Disqus commenting system =

Please check 'Output Javascript in footer' option in Disqus plugin options screen to fix.

= Why I cannot add/remove categories =

You can add categories since v0.4. Taxonomy manager is coming in next release

= Why do you require PHP 5.3? =

PHP 5.3 is not required anymore. Since v0.4.0 Gust has the same requirements as WordPress itself.

= Are you affiliated with Ghost =

No, I am not. This is an unofficial port, hence the different name.

= Where is the X feature =

This is an early release, so some features are not present, yet. They will be, later on, if the plugin gets enough interest.

== Changelog ==

= 0.4.0 =
* Major code rewrite
* Flight routing library replaced with custom version of Dispatch to allow for PHP 5.2 support
* Options page
* Experimental custom post type (CPT) support
* Featured images support
* Custom fields support
* Add new category dialog
* Tighter integration with WordPress - links in Admin Bar, Edit post links to gust etc.
* Autosaves
* Updated jQuery, moment.js and other javascript libraries
* Tested on WordPress 3.9
* A LOT of bug fixes

= 0.3.3 =
* several bugfixes
* some fixes for better debuging

= 0.3.2 =
* Bugfix - low level php notices failing on some setups
* Bugfix - contributors no longer can publish a post (thanks to [glaxxon]{https://github.com/glaxxon} for reporting)

= 0.3.1 =
* Bugfix for subdirectory setup (e.g. `domain.com/blog`)
* Bugfix for permalinks with front word (e.g. `archives/%post_id%`)
* Bugfix 'Add new' button in post list view now is relative to the post type.

= 0.3 =
* Post category support
* Scheduling future posts support
* Fixed an encoding bug that caused non-latin characters to be garbled in markdown on some systems (thanks to [Povilas Korop]{https://twitter.com/povilask007} for reporting & debuging efforts)
* Fixed an API error on PHP 5.3, caused by unsupported `json_encode()` options (thanks to [mkjonesuk]{https://github.com/mkjonesuk} for reporting & debuging efforts)
* Plugin now fails gracefully (deactvates and shows a WordPress notice) if PHP <5.3 is detected.

= 0.2.1 =
Bugfix - converted to static methods in Gust class

= 0.2 =
Post tags support
Tested compatibility width WordPress 3.8

= 0.1.1 =
Small bug fixes, updated license information

= 0.1 =
Initial release

== Upgrade Notice ==

No upgrade notices

== Screenshots ==
1. Login screen
2. List of posts/pages
3. Post editor screen
4. **New** - post tagging interface
