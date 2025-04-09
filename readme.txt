=== VMC Simple Cache ===
Contributors: vmcsoft
Tags: cache, performance, optimization, speed
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.2
Stable tag: 1.1.0
License: MIT
License URI: https://opensource.org/licenses/MIT

A lightweight, file-based caching solution for WordPress that improves your site's performance by caching pages and posts.

== Description ==

VMC Simple Cache is a performance optimization plugin that caches your WordPress pages and posts to improve load times. It's designed to be simple, efficient, and easy to configure.

= Key Features =

* File-based caching system
* Cache front page, blog page, and individual posts
* Cache archive pages (categories, tags, authors, dates, taxonomies)
* Cache regular WordPress pages
* Configurable cache TTL (Time To Live)
* Debug mode for troubleshooting
* Automatic cache clearing on post updates
* Admin interface for easy management
* Cache disabled for logged-in users
* Secure cache directory protection

= Why Choose VMC Simple Cache? =

* Lightweight and efficient
* Easy to configure
* No database queries
* Automatic cache management
* Built-in debug tools
* Secure by design
* Comprehensive caching options

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/vmc-simple-cache` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the Settings > VMC Simple Cache screen to configure the plugin.

== Frequently Asked Questions ==

= Does this plugin require any special configuration? =

No, the plugin works out of the box. However, you can configure:
* Front page caching
* Blog page caching
* Archive page caching
* Regular page caching
* Cache TTL (Time To Live)
* Debug mode

= What is Cache TTL? =

TTL (Time To Live) determines how long cached content remains valid. You can set it from 1 minute to 24 hours. The default is 1 hour.

= Is the cache secure? =

Yes, the cache directory is protected with .htaccess rules, and caching is automatically disabled for logged-in users.

= Does this plugin work with other caching plugins? =

While it can work alongside other caching plugins, we recommend using only one caching solution at a time to avoid conflicts.

= What types of archive pages are cached? =

When archive caching is enabled, the plugin caches:
* Category archives
* Tag archives
* Author archives
* Date archives (year, month, day)
* Custom taxonomy archives
* Custom post type archives

= How does page caching work? =

Page caching stores the HTML output of your WordPress pages. When a visitor requests a page, the cached version is served instead of generating the page from scratch. This significantly improves load times.

== Screenshots ==

1. Settings page showing cache configuration options
2. Debug mode showing cache information in page source
3. Cache management interface
4. Cache status display

== Changelog ==

= 1.1.0 =
* Added archive page caching (categories, tags, authors, dates, taxonomies)
* Added regular page caching
* Improved cache key generation
* Enhanced cache clearing for related content
* Updated documentation

= 1.0.0 =
* Initial release
* File-based caching system
* Front page and blog page caching
* Configurable TTL
* Debug mode
* Admin interface
* Cache management tools

== Upgrade Notice ==

= 1.1.0 =
Added support for archive pages and regular pages caching. This version includes significant improvements to the caching system.

= 1.0.0 =
Initial release of VMC Simple Cache.

== Privacy Policy ==

This plugin does not collect any personal data. It only creates cache files to improve your site's performance.

== Credits ==

Developed by VMCSoft - https://vmcsoft.com 