=== GDPR Cookie Consent ===
Contributors: webtoffee
Donate link: http://cookielawinfo.com/donate/
Tags: eu cookie law, GDPR, cookie law, eu privacy directive, privacy directive, cookies, privacy, compliance
Requires at least: 3.3.1
Tested up to: 5.8
Stable tag: 2.3.7
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

A simple way to show how your website complies with the EU Cookie Law / GDPR. Style it to match your own website.

== Description ==

NOTE: INSTALLING THIS PLUGIN ALONE DOESNT MAKE YOUR SITE GDPR COMPLIANT. SINCE EACH SITE USES DIFFERENT COOKIES, YOU MAY NEED TO BUILD OPT IN/OUT CAPABILITY TO HANDLE YOUR SITE SPECIFIC NEEDS.

Our plugin will help you to become GDPR compliant with following features. 

- Plugin will show a notice with Accept and Reject options. By default the cookie value will be set to 'null'. If the user clicks 'Accept' button the value with be changed to 'yes'. IF the user clicks on 'Reject' the value will be set to 'no'. Your developer can check this value to set a cookie

- Admin can add cookie details from the backend. The list of cookies can be displayed in your cookie policy page by using a short code


This plugin adds a subtle banner to your website either in the header or footer so you can show your compliance status regarding the new EU Cookie Law.


You can fully customise the style so it fits in with your existing website- change the colours, fonts, styles, the position on the page and even how it behaves when you click "accept".


You can choose to make the cookie bar disappear after a few seconds (completely configurable) or to accept on scroll (an option available under Italian law).


It also has a Cookie Audit module so you can easily show what cookies your site uses and display them neatly in a table on your Privacy & Cookies Policy page.


This plugin supports WPML and qTranslate so your translation needs are covered.


Inline with official ICO guidelines and current industry best practice, this plugin uses 'implied consent' to aid you in showing your compliance status. This is a deliberate design choice that does not proactively block cookies: it doesn't make your visitors take action before they can use your website, and will not interfere with blog comments and [Google] Analytics. This approach is aimed at reducing website visitor dropout and maintaining your vital analytics data.

For a demo, [check out the EU Cookie Law website](http://cookielawinfo.com/ "Demo the EU Cookie Law plugin"), including the table of cookies on the Privacy Policy page.


Features:

* Fully customisable to look just like your own website's style: customise the colours, styles and fonts
* Put the cookie bar in either the header or the footer
* (Optional) accept cookie policy if the user scrolls
* (Optional) automatically close the cookie bar after a delay (delay is configurable)
* (Optional) cookie bar can be permanently dismissed or accessible through a "show again" tab
* (Optional) "show again" tab is fully customisable including position shown on page and styles
* "Cookie Audit" shortcode to construct a nicely-styled 'Privacy & Cookie Policy'
* WPML compatible
* qTranslate support

[Read more about the EU Cookie Law](http://cookielawinfo.com/ "More information about the EU Cookie Law")


== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the `cookie-law-info` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to "Cookie Law Info / Cookie Law Settings" to configure the banner with your own text, colours and styles

To set up your Privacy & Cookie Policy Page:

1. Add descriptions of the cookies your site uses. Go to "Cookie Law Info / Add New".
2. Add a new page called e.g. Privacy and Cookie Policy
3. Add the [cookie_audit] shortcode to your Privacy & Cookie Policy Page

Cookie table shortcode usage:

	[cookie_audit]
	[cookie_audit style="winter"]
	[cookie_audit not_shown_message="No records found"]
	[cookie_audit style="winter" not_shown_message="Not found"]
	
	Parameters:
	
	style (optional) - choose one of several table styles included with the plugin. Styles included: simple, classic, modern, rounded, elegant, winter (styles are cAsE sensitive). Default style applied: classic.
	not_shown_message (optional) - if no cookie records are found, display this text. Default is blank (i.e. no message shown).
	

Delete header cookie usage:

	[delete_cookies]
	[delete_cookies linktext="delete cookies"]
	
	Parameters:
	
	linktext (optional) - the text shown in the link. Default is "Delete Cookies".


== Frequently Asked Questions ==

There's a lot of help available on the main plugin website. See:

http://cookielawinfo.com/faq
http://cookielawinfo.com/user-guide
http://cookielawinfo.com/support

= The cookie header isn't displaying =
First check you have installed the plugin and have activated it in the plugins panel.
To check if it is switched on or not, go to 'Settings / Cookie Law Info' and then enable the header by setting 'Display cookie bar?' to 'yes'.

= The header doesn't work on my browser =
Please report a bug on the support forum. Be sure to include the following information:

* Your URL (I will need this in order to help you!)
* WordPress version e.g. 4.2.2 (found in bottom right hand corner of dashboard)
* Browser e.g. FireFox, Chrome, IE
* Describe the problem

The more information you give, the quicker I can respond.

= What does this plugin do? =
This plugin will:

1. Add a banner to the top/bottom of all pages on your website, notifying the visitor that you have a clearly defined privacy and cookie policy.
2. Allow you to record which cookies your site uses, and:
3. Neatly display a list of these cookies (via a shortcode). You can put this list on your Privacy & Cookie Policy page, for example.

= Does this plugin block all cookies? =
No, and neither does any other WordPress plugin - be aware that despite what you may believe, it is not currently technically possible to completely block all cookies on your WordPress website without first updating all plugins that use cookies in some way. That is beyond the scope of any single plugin.
A more realistic approach for WordPress website owners is to use what is called "implied consent". This should not be a message buried on your website rather something more prominent that is clear to your visitors. This plugin can be used to highlight your compliance status and use of cookies in this way.


= Do visitors now have to accept/refuse cookies in order to use websites? =
In short, no, although there is a great deal of ambiguity around this law. What the law really gets at is privacy, not just cookies. The UK government, Google and many other major websites use "implied consent" on their own websites, which is the same technique used by this plugin. Further reading: http://cookielawinfo.com/cookie-law-101/

= Does this plugin stop cookies from being stored? =
No. Quite intentionally, this plugin will not do that. We have no way of knowing what plugins you use or how they work, so blocking/deleting cookies on a WordPress website/blog would possibly/probably break your site. Larger websites with huge budgets may well invest in such technology, but this plugin aims to help you provide a certain level of compliance without taking drastic action. If you do want blocking/deleting cookie functionality then (for now *) this is not the right plugin for you.

(*) Depending upon demand, this feature might be included in a future release.

= Does this plugin guarantee that I comply with this law? =
No.
As a generic plugin there's no way we can know anything about your specific circumstances. It can be used as part of an overall plan of action to comply, but just installing it and doing nothing more does nothing to help you. In all cases, you need to assess your own website's use of cookies and decide an appropriate course of action. If you are looking for specialist legal advice relating to your website you should always consult a lawyer.
See http://cookielawinfo.com for more information on what is required.


== Screenshots ==

1. Header (with default styles)
2. Admin panel
3. Admin panel - styling the form with colour pickers
4. Header (with custom styles)

== Changelog ==

= 2.3.7 =
* Fix: Removed extract() function from the shortcodes module to improve security.

= 2.3.6 =
* Security fixes in cookie scanner.
* Tested OK with WordPress 5.8

= 2.3.5 =
* Improvement: New filter `wt_cli_enable_settings_accept_btn` to force show the "save & accept" button on settings popup
* Improvement: Monster insights integration
* Improvement: Google Recaptcha integration
* Improvement: Added new filter * wt_cli_set_secure_cookies* to set secure cookies
* Fix: Categories are not ordered properly when the multilanguage plugin is active
* Fix: could not update script blocker status
* Fix: Plugin settings are not loaded on the front-end if the cookie banner is hidden

= 2.3.4 =
* Introduced option to abort the cookie scan.
* Improved cookie scan layout.

= 2.3.3 =
* Improvement: Bulk scanning of cookies

= 2.3.2 =
* Improved cookie scanner

= 2.3.1 =
* New shortcode `[cookie_accept_all]` for adding accept all button
* ADA compliance
* Added support for Official Facebook Pixel, Facebook for WooCommerce, Smash Balloon Instagram Feed, Custom Twitter Feeds, Custom Facebook feed & Feeds for YouTube plugins.
* Added option to set a cookie category as strictly necessary
* Added new scripts ( Hubspot, Matomo & Google Adsense) to script blocker

= 2.3.0 =
* [Bugfix] Cookie audit shortcode is not working properly

= 2.2.9 =
* Included support for CCPA ( Do not sell personal information )
* Introduced filter `wt_cli_privacy_overview_length` to change the length of the privacy overview text
* Introduced filter `wt_cli_enable_ip_masking` to mask or unmask the IP address 

= 2.2.8 =
* Reverting fix from 2.2.7 [Script blocker failed to block some scripts which contained HTML specific tags]

= 2.2.7 =
* [Bugfix] Popup overlay is not working when enabled
* [Bugfix] Script blocker failed to block a script which contains HTML tags
* [Bugfix] Console error when filter "cli_show_cookie_bar_only_on_selected_pages" is used
* Disabled indexing of cookie data
* Changed text domain

= 2.2.6 =
* Security improvements

= 2.2.5 =
* Security fix
* Improved user consent logging

= 2.2.4 =
* Security fix 
* ADA Compliance
* PHP 7.4 compatibility
* [Bugfix] Accept all not working when auto accept feature is on
* [Bugfix] Duplicate privacy overview content in the cookie settings popup
* [Bugfix]Fixed issue with the cookie bar animation
* Translation updates

= 2.2.3 =
* Introduced new cookie bar layout
* Introduced new shortcode [wt_cli_category_widget]
* Minor bug fixes

= 2.2.2 =
* Renew consent
* Improved compatibility with major caching servers and plugins
* Placeholder for Iframes


= 2.2.1 =
* Introduced Javascript helper function for controlling the user consent by categories, particularly for third-party services that need granular control, other than the automatic script blocker already provided within the plugin.
* GDPR compliance for Google Tag Manager
* Added option to Enable/Disable script blocker 
* Updated GeoIP to GeoIP2
* Improved cache control

= 2.2.0 =
* Cache overriding to tackle server-side caching issues
* Admin option to configure default state of acceptance for cookie categories
* Additional option to view cookie settings as an extendable cookie bar/widget
* Accept all cookies feature
* New advanced settings popup layout
* Language translation added for the  languages Finnish, Italian, Portuguese & Swedish
* Tested ok with WP 5.2

= 2.1.9 =
* Introduced filter "cli_extend_script_blocker" for extending automatic script blocker for custom third-party scripts
* IPV6 support for GeoIP
* Cookie policy generator
* Improved cookie scanner - updated cookie description, first found URL  
* Added option to enable Nofollow to read more link for SEO purposes
* Improved WP Rocket Compatibility
* Fixed styling issues with the audit table in responsive mode
 
= 2.1.8 =
* New customizable theme options for banner/widgets/popup
* New option 'Load On Start' added in category - to render scripts by default on page load(for non-sensitive user data)
* Close button option added. New shortcode [cookie_close]
* New filter added to disable/enable editor in privacy overview
* Word wrapping added for audit table
* Improved FB script blocker - added new scripts
* Danish translations updated
* Pinterest support added in script blocker
* Support added for Pixelyoursite plugin
* Old preview option replaced with Live preview(under theme customization)
* [Bug fix] Show cookie bar on selected pages filter - CSS and JS are blocked

= 2.1.7 =
* Improved support for Super cache, W3TC.
* Option added to control the number of URLs scanned per request.
* Bug fix: 500 error fixed(due to buffer)
* New filter(cli_audit_table_on_off) to control the visibility of cookie audit table with/without EU option.

= 2.1.6 =
* Script blocker enabled for HotJar, Google publisher tag, Youtube, Vimeo, Google maps, Addthis, Sharethis, Twitter, Soundcloud, Slideshare, Linkedin, Instagram.
* Custom menu title option added for privacy overview
* Cookie notice as widget option added
* Category specific cookie listing enabled in [cookie_audit] shortcode
* Optimized query to fix multiple calls for Cookie list
* Fixed submenu sorting issue for non-admin users
* Compatibility added for http wrapper disabled websites
* GEO IP detection enabled for multiple IPs
* Fixed issue with Innodb tables: Unable to create Mysql table(Stated prefix limitation issue of Innodb tables)


= 2.1.5 =
* Improved cookie scanning
* Cookie bar preview option(Beta) added
* New enclosing shortcode added for better control of third party content. [cookie_after_accept]..content goes here..[/cookie_after_accept]
* Basic support included for Litespeed cache, WP Super Cache, Site ground cache, Endurance Cache, WP Fastest Cache
* Bug fix: Necessary category `strictly enabled`
* Option to choose from the built-in pages for 'Read More' link.

= 2.1.4 =
* In the previous update a title with default text ‘This website uses cookies‘ would get added to the existing cookie bar. In this update this title will get removed to that it is blank by default. In case you need this to be added again you need to explicitly add suitable content for the title.
* Corrected German translations (thanks to Max B)
* Settings option made responsive
* Fixed a bug with the version update duplicate notice.

= 2.1.3 =
* Improved UI.
* Enhancements to cookie message bar - included title and option for overlay layout.

= 2.1.2 =
* Accordion conflict with jQuery UI css issue fixed.

= 2.1.1 =
* Auto Script Blocker for Google Analytics, Facebook Pixel and Google Tag Manager

= 2.1.0 =
* Auto Script Blocker.
* Scan and download cookies.
* Scan and import cookies.

= 2.0.1 =
* Import Export Cookielaws.

= 1.5.5 =
* GDPR compliance updates.

= 1.5.4 =
* Tested OK with WordPress 4.9.5
* GDPR compliance updates.

= 1.5.3 =
* Bug fix: Buttons now handle apostrophes correctly
* Bug fix: Added <tr> to table head for [cookie_audit] table for W3 Validator (thanks to davidebabylonia for finding and suggesting the solution)

= 1.5.2 =
* Minor bug fix: adds version number to cli-admin.css

= 1.5.1 =
* Bug fix: HTML5 validation fix for shortcode links (thanks to davidebabylonia)
* Added JavaScript version number for greater compatibility

= 1.5 =
* Major update: the cookie bar is now inserted into the page via wp_footer rather than using jQuery (for better performance and greater browser compatibility)
* Update: if the cookie bar is in the header, there is now an option to fix the bar to the header using position:fixed

= 1.4.3 =
* jQuery 'reload' bug fix on accept

= 1.4.1 =
* Bug fix: fixed browser compatibility issue in cookielawinfo.js

= 1.4 =
* New feature: accept policy & close cookie bar on scroll (an option available under Italian law)
* New feature: if cookie bar is set to header it is fixed to the top of the screen (using CSS "position:fixed")

= 1.3.2 =
* Bug fix: changed filename from wpml.xml to wpml-config.xml

= 1.3.1 =
* Fixing header/SVN tagging issue

= 1.3 =
* Adding WPML support (wpml.xml)

= 1.2.2 =
* Bug fix: removed extra '{' from cli-tables.css
* Bug fix: fixed cookie_button shortcode text bug
* Modified help pages, text and contact information

= 1.2.1 =
* Added plugin settings page link to plugins.php
* Bug fix: custom posts bug affecting some other plugins

= 1.2 =
* Removed 3rd party JavaScript "jQuery.cookie" which can cause issues with certain versions of Apache server
* Added native JavaScript cookie getter/setter
* Removed JavaScript debug routine
* Replaced JavaScript 'eval' with JSON.parse() for improved security: requires IE8+ (all other browsers fine though- who would have thought?)
* Improved JavaScript performance and compatibility by removing global variables and running as inline function

= 1.1 =
* New feature: auto-hide cookie bar after (configurable) delay
* New feature: added responsive design to cookie audit table (thanks to Mark Wiltshire)
* Upgrade: now using WP3.5 color picker
* Bug fix: Cookie Audit table now shows maximum of 50 posts (was 10, which was a bit restrictive)
* Bug fix: Cookie Law Info now only visible to admins
* Bug fix: fixed typo on Dashboard help section for the [delete_cookies] shortcode
* Bug fix: fixed "invalid header" bug
* Performance enhancement: removed jQueryUI from admin panel and added custom (slimline) code
* Performance enhancement: compressed CSS a bit
* Performance enhancement: cookie audit CSS is now only downloaded on the page on which it is needed

= 1.0.3 =
* Fixed bug where JavaScript generated an http 404 error.

= 0.9 =
* Improved design & appearance
* Cookie Law Info bar can be shown in header or footer
* Customise fonts
* New animations on page load / close header
* Option to switch off "show again tab" - or position it anywhere horizontally in the header or footer. Additionally the styling has been improved and you are now able to customise the message.
* New button styles: better styling and effects, greater control
* Customise your message using HTML and 5 shortcodes with quick-start default options
* "Cookie Audit" module - document the cookies your site uses then display them in your privacy policy via a shortcode
* Enhanced dashboard
* Enhanced help section
* Refactored codebase, improved jQuery performance
* Bugs fixed: no more slashes in Message Box

= 0.8.3 =
* First public release.

== Upgrade Notice ==

= 2.3.7 =
* Fix: Removed extract() function from the shortcodes module to improve security.