<?php
/*
    Plugin Name: WebsiteDefender WordPress Security
    Plugin URI: http://www.websitedefender.com/websitedefender-wordpress-security
    Description: WebsiteDefender WordPress security plug-in helps you secure your WordPress installation and suggests corrective actions for: Passwords, File permissions, Database security, Version hiding, WordPress admin protection/security and much more!
    Version: 1.0.6
    Author: WebsiteDefender
    Author URI: http://websitedefender.com/
    License: GPLv2 or later
    Text Domain: WSDWP_SECURITY
    Domain Path: /languages
*/

/*  Copyright 2011  WebsiteDefender.com  (email : support@websitedefender.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Gets the plug-in's name
 */
    define('ACX_PLUGIN_NAME', basename(realpath(dirname(__FILE__))));

/**
 * Sets the plug-in nice name
 */
define('ACX_PLUGIN_NICE_NAME', 'WebsiteDefender WordPress Security');



/*
 * LOAD REQUIRED FILES
 */
require('php/acx-globals.php');
require('php/acxUtil.php');
require('php/acx-functions.php');

/*
 * TRIGGER ACTIONS
 */
//!! So we can use the "user" related functions
require_once(ABSPATH.'wp-includes/pluggable.php');

//!! HIGH PRIORITY TASKS
//============================================
//@@
add_action('init', array('acxUtil','disableErrorReporting'), 1);
//@@
add_action('init', array('acxUtil','hideWpVersionFrontend'), 1);
//@@
add_action('init', array('acxUtil','removeWpMetaGeneratorsFrontend'), 1);   //comment out this line to make ddsitemapgen work
//##==


//@@
acxUtil::removeErrorNotificationsFrontEnd();
//@@
acxUtil::removeReallySimpleDiscovery();
//@@
acxUtil::removeWindowsLiveWriter();
//@@
if (!is_admin())
{
	add_filter('script_loader_src', array('acxUtil','removeWpVersionFromLinks'));
	add_filter('style_loader_src', array('acxUtil','removeWpVersionFromLinks'));
}


//@@ Load textdomain
add_action( 'init', array('acxUtil','loadTextDomain'));

//@@ Load resources
add_action('admin_init',array('acxUtil','loadResources'));

//@@
add_action('in_admin_footer',array('acxUtil','addPluginInfoFooter'));


//@@
acxUtil::hideWpVersionBackend();
//@@
acxUtil::removeCoreUpdateNotification();
//@@
acxUtil::removePluginUpdateNotifications();
//@@
acxUtil::removeThemeUpdateNotifications();
//@@
acxUtil::preventWpContentDirectoryListing();


//@@ Hook into the 'wp_dashboard_setup' action to create the dashboard widget
add_action('wp_dashboard_setup', array('acxUtil','addDashboardWidget'));

//## Display the Admin menu
add_action('admin_menu', "_acx_createAdminMenu");

//## Display the "Settings" menu on plug-in page
add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), 'acx_admin_plugin_actions', -10);

