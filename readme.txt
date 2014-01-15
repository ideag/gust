=== Gust ===
Contributors: ideag
Donate link: http://wordofpress.com/gust
Tags: ghost, admin panel, markdown, editor, dashboard, admin
Requires at least: 3.0.1
Tested up to: 3.8
Stable tag: 0.3.3
License: MIT
License URI: http://wordofpress.com/gust/license

Gust is a port of Ghost admin panel for WordPress.

== Description ==

At the begining, Ghost was supposed to be a fork of WordPress. Then there was a talk of a plugin, that would give a next-generation admin panel for WordPress. But in the process it became a new blogging platform, built on Node.js. This plugin is an attempt to bring the nice and clean admin panel of Ghost back to the WordPress ecosystem. 

Current features:

*   Posts/Pages Markdown editor
*   Media upload integration
*   Post tags support
*   **NEW** Post category support
*   **NEW** Post scheduling support

Requirements:

*   PHP **5.3** or greater

== Installation ==

1. Upload `gust` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Make sure pretty permalinks are enabled
1. Go to yourdomain.com/gust

== Frequently Asked Questions ==

= Empty post list with Disqus commenting system =

Please check 'Output Javascript in footer' option in Disqus plugin options screen to fix.

= Why I cannot add/remove categories = 

Category manager is comming in v0.4

= Why do you require PHP 5.3? =

One of the libraries Gust uses, Flight, requires it.

= Are you affiliated with Ghost =

No, I am not. This is an unofficial port, hence the different name.

= Where is the X feature =

This is an early release, so some features, like category/featured images support, are not present, yet. They will be, later on, if the plugin gets enough interest.

== Changelog ==

### 0.3.3
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