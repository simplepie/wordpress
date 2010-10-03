=== SimplePie Plugin for WordPress ===
Contributors: skyzyx, gsnedders
Donate link: http://simplepie.org/wiki/plugins/wordpress/simplepie_plugin_for_wordpress
Tags: rss, atom, feed, feeds, syndication, simplepie, magpie, sidebar, sideblog
Requires at least: 2.0
Tested up to: 2.5
Stable tag: 2.2.1

A fast and easy way to add RSS and Atom feeds to your WordPress blog.

== Description ==

= About this Plugin =
This is the official plugin from the SimplePie team. It relies on the <a href="http://wordpress.org/extend/plugins/simplepie-core/">SimplePie Core</a> plugin, and includes several features:

* A configuration pane under the Options tab in the WordPress software.
* "Multifeeds" support (merging and sorting multiple feeds together).
* MUCH better control over the plugin's output.
* Simple, easy-to-use tags for nearly every piece of data that SimplePie can output
* Support for multiple templates
* Global configuration of default values for several configuration options
* Ability to override the defaults for any given feed -- including giving a feed it's own output template.
* Ability to post-process feed data (e.g. stripping out all content except for images).
* Support for internationalized domain names.
* Support for short descriptions is configurable.
* Support for PHP 4.x and 5.x.
* And much more!

= What's new in this version? =
The major changes in version 2.2 are reliance on the "SimplePie Core" WordPress plugin, cache location fixes, updates for WordPress 2.5, much more noticeable error messages when dependencies are missing, and support for a variety of new SimplePie 1.1 features such as setting a per-feed item limit when using Multifeeds.

The major new feature in version 2.1 was support for "post-processing", which allows you to write small PHP scripts to alter the output from a feed. Popular uses include stripping out everything from a feed item except for images, and adding a target attribute to links to make them open in new windows. We've also added better support for error handling and messages, and made the plugin more aware of it's location, enabling a simpler install process.

== Installation ==

= Upgrading from an older version? =
**From 2.2.x**

1. Backup any custom templates and post-processing rules you might have.
2. Replace the old plugin folder with the new one.
3. Re-add your custom templates.

**From 2.0.x through 2.1.x**

1. Backup any custom templates and post-processing rules you might have.
2. Replace the old plugin folder with the new one.
3. Re-add your custom templates.
4. Make sure that you go into the Options panel, and click "update options" to ensure that new data is entered into the database.

**From 1.x**

1. Delete all traces of the previous version of the plugin (specifically deleting simplepie_wordpress.php).
2. Wherever you've called ''SimplePieWP()'', you'll likely end up deleting the options you've already set, or converting them to the updated array syntax for setting per-feed options.  These new options are discussed below.

== Frequently Asked Questions ==

To get more information about this plugin specifically, check out the <a href="http://simplepie.org/wiki/plugins/wordpress/simplepie_plugin_for_wordpress">SimplePie Plugin for WordPress</a> page at the SimplePie documentation wiki.

To learn more about the core SimplePie library (made available in WordPress as the <a href="http://wordpress.org/extend/plugins/simplepie-core/">SimplePie Core</a> plugin), check out the <a href="http://simplepie.org/wiki/faq/start">SimplePie FAQ</a>.

== Usage ==

Details about the usage of this plugin (including troubleshooting issues) are maintained at the <a href="http://simplepie.org/wiki/plugins/wordpress/simplepie_plugin_for_wordpress">SimplePie Plugin for WordPress</a> page at the SimplePie documentation wiki.

== Brief Version History ==

* 2.2: Added support for setting your preferred cache location, improvements to error handling, more noticeable error messages when problems arise, support for more Media RSS data, support for new SimplePie 1.1 methods, support for WordPress 2.5, and stopped bundling the SimplePie API in favor of relying on the SimplePie Core extension.
* 2.1: Added support for feed post-processing, better error handling, and fixed issues with installing in the wrong location.
* 2.0: Complete re-write from scratch. Now a full-fledged WordPress plugin complete with control panel.
* 1.2: Added support for the 'showtitle' and 'alttitle' keywords.
* 1.1: Better error handling, and support for the 'error' keyword.
* 1.0: First release.

== Related ==

Besides this plugin for WordPress, the SimplePie team also develops the following feed-related stuff:

* **<a href="http://simplepie.org">SimplePie</a>** -- This is the core PHP library that powers everything we do. Super-fast, easy-to-use RSS and Atom parsing in PHP.
* **<a href="http://live.simplepie.org">SimplePie Live! (Beta)</a>** -- For the AJAX/JavaScript developers out there, this is a service that provides an AJAX-friendly API for feeds. A must-have for AJAX developers wishing to implement feeds.
* **<a href="http://mobile.simplereader.com">SimpleReader Mobile</a>** -- An online news feed reader designed for mobile devices (iPhone, iPod touch, Blackberry, Palm, PSP, Windows Mobile, Opera Mini, etc.). Perfect for keeping up with feeds on the go.
