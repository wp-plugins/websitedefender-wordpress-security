=== WebsiteDefender WordPress Security ===
Contributors: WebsiteDefender
Tags: security, securityscan, chmod, permissions, admin, administration, authentication, database, dashboard, post, notification, password, plugin, posts, wsd, websitedefender, plugins, private, protection, tracking, wordpress
Requires at least: 2.8
Tested up to: 3.2.1
Stable tag: trunk
License: GPLv2 or later


Helps you secure your WordPress installation and provides detailed reporting on discovered vulnerabilities and how to fix them.


== Description ==

The WebsiteDefender WordPress Security plugin is a free and comprehensive security tool that helps you secure your WordPress installation and suggests corrective measures for: strengthening passwords, securing file permissions, security of the database, version hiding, WordPress admin protection and lots more.


= Key security features: =

* Easy backup of WordPress database for disaster recovery
* Removal of error-information on login-page
* Addition of index.php to the wp-content, wp-content/plugins, wp-content/themes and wp-content/uploads directories to prevent directory listings
* Removal of wp-version, except in admin-area
* Removal of Really Simple Discovery meta tag
* Removal of Windows Live Writer meta tag
* Removal of core update information for non-admins
* Removal of plugin-update information for non-admins
* Removal of theme-update information for non-admins (only WP 2.8 and higher)
* Hiding of wp-version in backend-dashboard for non-admins
* Removal of version in URLs from scripts and stylesheets only on frontend
* Reporting of security overview after Wordpress blog is scanned
* Reporting of file permissions following security checks
* Strong password generator tool to protect from brute force attacks
* Integrated tool to change the database prefix
* Disabling of database error reporting (if enabled)
* Disabling of PHP error reporting


For more information on the WebsiteDefender WordPress Security plug-in and other WordPress security news, visit the <a href="http://www.websitedefender.com/blog" target="_blank">WebsiteDefender Blog</a> and join our <a href="http://www.facebook.com/websitedefender" target="_blank">Facebook</a> page. Post any questions or feedback on the <a href="http://www.websitedefender.com/forums/wp-security-scan-plugin/" target="_blank">WebsiteDefender WordPress Security plug-in forum</a>.


== Requirements ==
* WordPress version 2.8 and higher (tested at 3.1)
* PHP5 (tested with PHP Interpreter >= 5.2.9)



== Installation ==
* Make a backup of your current installation
* Unpack the downloaded package
* Upload the extracted files to the /wp-content/plugins/ directory
* Activate the plugin through the 'Plugins' menu in WordPress

If you do encounter any bugs, or have comments or suggestions, please post them on the <a href="http://www.websitedefender.com/forums/wp-security-scan-plugin/" target="_blank">WebsiteDefender WordPress Security plug-in forum</a>.

For more information on the WebsiteDefender WordPress Security plug-in and other WordPress security news, visit the <a href="http://www.websitedefender.com/blog" target="_blank">WebsiteDefender Blog</a> and join our <a href="http://www.facebook.com/websitedefender" target="_blank">Facebook</a> page. Post any questions or feedback on the <a href="http://www.websitedefender.com/forums/wp-security-scan-plugin/" target="_blank">WebsiteDefender WordPress Security plug-in forum</a>.


== Other Notes ==

For more information on the WebsiteDefender WordPress Security plug-in and other WordPress security news, visit the <a href="http://www.websitedefender.com/blog" target="_blank">WebsiteDefender Blog</a> and join our <a href="http://www.facebook.com/websitedefender" target="_blank">Facebook</a> page. Post any questions or feedback on the <a href="http://www.websitedefender.com/forums/wp-security-scan-plugin/" target="_blank">WebsiteDefender WordPress Security plug-in forum</a>.


== License ==
Good news, this plugin is free for everyone! Since it's released under the GPL, you can use it free of charge on your personal or commercial blog.

For more information on the WebsiteDefender WordPress Security plug-in and other WordPress security news, visit the <a href="http://www.websitedefender.com/blog" target="_blank">WebsiteDefender Blog</a> and join our <a href="http://www.facebook.com/websitedefender" target="_blank">Facebook</a> page. Post any questions or feedback on the <a href="http://www.websitedefender.com/forums/wp-security-scan-plugin/" target="_blank">WebsiteDefender WordPress Security plug-in forum</a>.


== Frequently Asked Questions ==

= How do I make Dagon Design's sitemap generator plugin compatible? =
There is currently a small compatibility issue.  This can be temporarily
solved by opening securityscan.php and commenting out the line
`add_action("init", "acxUtil::removeWpMetaGeneratorsFrontend", 1);`


For more information on the WebsiteDefender WordPress Security plug-in and other WordPress security news, visit the <a href="http://www.websitedefender.com/blog" target="_blank">WebsiteDefender Blog</a> and join our <a href="http://www.facebook.com/websitedefender" target="_blank">Facebook</a> page. Post any questions or feedback on the <a href="http://www.websitedefender.com/forums/wp-security-scan-plugin/" target="_blank">WebsiteDefender WordPress Security plug-in forum</a>.

== Changelog ==

= 1.0.6 =
* Fixed the broken pages

= 1.0.5 =
* Removed the registration requirement
* Alert page removed following cancellation of pro features

= 0.3 =
* Update: Minor updates

= 0.2 =
* Update: Minor updates

= 0.1 =
* Feature: Removes error-information on login-page
* Feature: Adds index.php to the wp-content, wp-content/plugins, wp-content/themes and wp-content/uploads directories to prevent directory listings
* Feature: Removes the wp-version, except in admin-area
* Feature: Removes Really Simple Discovery meta tag
* Feature: Removes Windows Live Writer meta tag
* Feature: Removes core update information for non-admins
* Feature: Removes plugin-update information for non-admins
* Feature: Removes theme-update information for non-admins (only WP 2.8 and higher)
* Feature: Hides wp-version in backend-dashboard for non-admins
* Feature: Removes version on URLs from scripts and stylesheets only on frontend
* Feature: Provides various information after scanning your Wordpress blog
* Feature: Provides file permissions security checks
* Feature: Provides a strong password generator tool
* Feature: Provides database backup utility
* Feature: Provides a tool for changing the database prefix
* Feature: Turns off database error reporting (if enabled)
* Feature: Turns off PHP error reporting

For more information on the WebsiteDefender WordPress Security plug-in and other WordPress security news, visit the <a href="http://www.websitedefender.com/blog" target="_blank">WebsiteDefender Blog</a> and join our <a href="http://www.facebook.com/websitedefender" target="_blank">Facebook</a> page. Post any questions or feedback on the <a href="http://www.websitedefender.com/forums/wp-security-scan-plugin/" target="_blank">WebsiteDefender WordPress Security plug-in forum</a>.
