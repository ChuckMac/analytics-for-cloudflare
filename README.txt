=== Analytics for Cloudflare ===
Contributors: chuckmac
Donate link: https://chuckmacdev.com/
Tags: analytics, admin, dashboard
Requires at least: 3.8
Tested up to: 4.3
Stable tag: 1.0.2
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A WordPress plugin to connect your WordPress dashboard to your CloudFlare account to display some key analytics data.

== Description ==

The dashboard widget provides a quick look at some key metrics for your site.

 * Requests
 * Page Views
 * Unique Visitors
 * Bandwidth

The data can be viewed from several timeframes.

  * Last week
  * Last Month
  * Last 24 Hours

You can also get a quick look at some other metics available.

  * SSL vs Non-SSL
  * Breakdown of content types (html, jpg, png, js, etc..)
  * Breakdown of requests by country

== Installation ==

1. Upload the entire `analytics-for-cloudflare` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Enter your CloudFlare API credentials in the `Settings` -> `Analytics For Cloudflare` screen
4. Once connected, select the domain to display the statics for on the same settings page.


== Frequently Asked Questions ==


== Screenshots ==

1. A sample view of the analytics dashboard widget

== Changelog ==

= 1.1 =
* Match Wordpress-Extra coding standards.
* Fix text domain in i18n (make static instead of variable).
* Fix domains not appearing properly if you have more than 20

= 1.0.2 =
* Added: Developer hooks to filter api values

= 1.0.1 =
* Fixed: Internationalization not enabled.
* Added: Link to settings from the plugin page.

= 1.0 =
* The initial release of the plugin.

== Upgrade Notice ==

= 1.0.1 =
Additional developer hooks to filter api values.

= 1.0.1 =
Internationalization fix and other minor updates.