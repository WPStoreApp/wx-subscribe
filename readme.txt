=== WX Subscribe ===
Contributors: bestony
Donate link: https://www.ixiqin.com/exceptional/
Tags: payment, subscribe
Requires at least: 4.6
Tested up to: 7.1
Stable tag: 1.2.3
Requires PHP: 7.0.0
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add paid annual subscriptions to WordPress with PayJS. Protect full posts or selected content with the `[subscribe]` shortcode.

== Description ==

WX Subscribe adds paid reading to WordPress sites through [PayJS](https://payjs.cn/ref/MDNXMD). Site owners can protect complete posts or selected sections and grant the subscriber role after a verified payment notification.

Use `[subscribe]content[/subscribe]` to protect a section of a post. Users with the subscriber role and administrators can view protected content.

Before using the plugin, register and activate a PayJS merchant account. The plugin supports individual site owners through PayJS.

== Installation ==

1. Upload the plugin from **Plugins > Add New > Upload Plugin**, or install it from the WordPress.org directory.
2. Activate the plugin.
3. Open **WX Subscribe > Plugin Settings** and enter the PayJS merchant ID, communication key, and annual price.
4. Protect content with the `[subscribe]` shortcode or select **Require subscription** in the post editor.

== Frequently Asked Questions ==

= Does the plugin support individual site owners? =

Yes. WX Subscribe uses PayJS, which supports individual merchant accounts where available.

= Which payment gateway does the plugin use? =

The plugin uses the PayJS API for QR payments and server-to-server payment notifications.

== Screenshots ==

1. Protecting a complete post
2. Protecting selected content
3. The post subscription setting
4. The order management screen
5. The subscription settings screen

== Changelog ==

= 1.2.3 =
* Bound payment notifications to the configured amount and the stored PayJS order.
* Prevented new payments from assigning a conflicting generic `client` role.
* Fixed user profile status checks, subdirectory links, and post-save edge cases.
* Added additional payment and role security regression coverage.

= 1.2.2 =
* Fixed unauthenticated SQL injection in order cancellation and payment notification handlers.
* Added administrator capability and nonce checks to order cancellation.
* Hardened PayJS signature verification and validated callback order state.
* Escaped admin output, sanitized settings, and removed remote admin images.

= 1.2.1 =
* Fixed PHP 8 warnings when the plugin is not configured.

= 1.2 =
* Added the help center.

= 1.0 =
* Initial release.

== License ==

This plugin is licensed under GPLv2 or later.
