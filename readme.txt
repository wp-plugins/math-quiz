=== Math Quiz ===
Contributors: atitan
Donate link: 
Tags: comments, spam, captcha
Requires at least: 3.0
Tested up to: 3.5
Stable tag: 0.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Math Quiz generates dynamic math problem in the comment form to beat spam robots.

== Description ==

Math Quiz generates dynamic math problem in the comment form to beat spam robots and provides flexible form style customization.

Demo website: http://atifans.net/ (with Supercache enabled)

Features:

*   Dynamic problems prevent spam robots from posting unwanted content.
*   Quiz form inserted using AJAX for better compatibility with HTML cache plugin.
*   Form style and position can be fully customized in control panel.

Functions to add:

*   Do AJAX answer check before submitting the form.

    **Note: if your siteurl and homeurl are in different domain, the plugin may not work properly due to "Cross-origin resource sharing" mechanism.**

== Installation ==

Install this plugin is easy.

1. Upload whole directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Done!

== Frequently Asked Questions ==

= Why it always tells me that I'm failed to answer the quiz? =

Client browsers must support Cookie and JavaScript because the plugin uses Session to store quiz data and AJAX to insert the quiz form.

== Changelog ==

= 0.6 =
* Problems now come in pictured form.
* Only addition and subtraction are available.

= 0.5 =
* Fix compatibility with built-in theme, such as twentyeleven.

= 0.4 =
* Users can now refresh the quiz themselves.
* User defined quiz form was deprecated, use CSS for customization instead.
* Added new form insert method.

= 0.3 =
* More quiz types were added.
* The form could now choose to insert before or after the selected element.

= 0.2 =
* Admin panel, Custom quiz form, Problem choices were introduced.
* zh_TW translation is now available.

= 0.1 =
* Initial release.

== Upgrade Notice ==

= 0.6 =
Problems now come in pictured form.

= 0.5 =
Fix compatibility with built-in theme, such as twentyeleven.

= 0.4 =
User defined quiz form deprecated.

= 0.3 =
New quiz types are available.

= 0.2 =
Several new functions were introduced.

= 0.1 =
Initial release