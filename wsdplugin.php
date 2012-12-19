<?php
/**
    Plugin Name: WebsiteDefender WordPress Security
    Plugin URI: http://www.websitedefender.com/websitedefender-wordpress-security-plugin/
    Description: The WebsiteDefender WordPress Security plugin is the ultimate must-have tool when it comes to WordPress security. The plugin is free and monitors your website for security weaknesses that hackers might exploit and tells you how to easily fix them.
    Version: 1.0.2
    Author: WebsiteDefender
    Author URI: http://websitedefender.com/
    License: GPLv2 or later
    Text Domain: WSDWP_SECURITY
    Domain Path: /languages
 */

require_once("inc/settings.php");
require_once("inc/functions.php");

$wsdplugin_nonce = null;

function wsdplugin_init()
{
	global $wsdplugin_nonce;
	$wsdplugin_nonce = wp_create_nonce();

	if (!is_admin())
    {
        wsdplugin_security::run_fixes();
        wsdplugin_NotificationEngine::run();
    }

    if (is_admin() && current_user_can('administrator'))
    {
        if(isset($_GET['download_agent_now']))
        {
            $agent_name = wsdplugin_Handler::get_option('WSD-AGENT-NAME', FALSE);
            $agent_data = wsdplugin_Handler::get_option('WSD-AGENT-DATA', FALSE);

           if(($agent_name !== FALSE) && ($agent_data !== FALSE))
           {
                header("Content-type: application/octet-stream");
                header("Content-Disposition: attachment;filename=".$agent_name);
                echo $agent_data;
                exit;
            }
	        echo wptexturize(
<<<TEXT
<p style="font-size: 14px;">Can't retrieve the agent. please login to the dashboard</p>
TEXT
			);
            exit;
        }

	    // Process plugin reset
        if (isset($_GET['wsdplugin_reset']))
        {
            $options = array('WSD-USER', 'WSD-HASH', 'WSD-KEY', 'WSD-ID', 'WSD-NAME', 'WSD-SCANTYPE', 'WSD-SURNAME',
                             'WSD-WORKING', 'WSD-AGENT-DATA', 'WSD-AGENT-NAME', 'WSD-EXPIRATION');

            foreach ($options as $option) {
                delete_option($option);
            }

	        $index = strrpos($_SERVER['REQUEST_URI'], '&wsdplugin_reset');
	        header('Location: ' . substr($_SERVER['REQUEST_URI'], 0, $index));
            exit;
        }
    }
}


function wsdplugin_updateMenuItem()
{
    wp_enqueue_script('jquery');
    wp_enqueue_script('wsdplugin_js_common', wsdplugin_Utils::jsUrl('common.js'), array('jquery'), '1.0');
}

function wsdplugin_admin_init()
{
    if (is_admin() && current_user_can('administrator'))
    {
        define('wsdplugin_WSD_PLUGIN_SESSION', TRUE);
	    define('wsdplugin_WSD_PLUGIN_BASE_URL', plugin_dir_url(__FILE__));
	    define('wsdplugin_WSD_PLUGIN_BASE_PATH', plugin_dir_path(__FILE__));

        wsdplugin_updateMenuItem();

        wsdplugin_security::run_checks();
		wsdplugin_NotificationEngine::run();
    }
	else
	{
		wsdplugin_security::run_fixes();
	}
}

function wsdplugin_createAdminMenu()
{
	if (current_user_can('administrator') && function_exists('add_menu_page'))
	{
		$showPages = false;

		if (defined('WP_ALLOW_MULTISITE'))
		{
			if (WP_ALLOW_MULTISITE && is_super_admin())
				$showPages = true;
		}
		else {
			$showPages = true;
		}

		$iconUrl = plugin_dir_url(__FILE__) . 'img/wsd-logo-small.png';

        if ($showPages){
            add_menu_page( __('WSD Security'), __('WSD Security'), 'edit_pages', 'wsdplugin_dashboard', 'wsdplugin_pageDashboard', $iconUrl);
            add_submenu_page('wsdplugin_dashboard', 'Dashboard', 'Dashboard', 'edit_pages', 'wsdplugin_dashboard', 'wsdplugin_pageDashboard');
        }
		else { add_menu_page( __('WSD Security'), __('WSD Security'), 'edit_pages', 'wsdplugin_dashboard', 'wsdplugin_pagePassword', $iconUrl); }

		if ($showPages) add_submenu_page(__('wsdplugin_dashboard'), __('Alerts'), 'Alerts', 'edit_pages', 'wsdplugin_alerts', 'wsdplugin_pageAlerts');
		if ($showPages) add_submenu_page(__('wsdplugin_dashboard'), __('Strong Password Generator'), 'Strong Password Generator', 'edit_pages', 'wsdplugin_password', 'wsdplugin_pagePassword');
		if ($showPages) add_submenu_page(__('wsdplugin_dashboard'), __('Database Tool'), 'Database Tool', 'edit_pages', 'wsdplugin_database', 'wsdplugin_pageDatabase');
		if ($showPages) add_submenu_page(__('wsdplugin_dashboard'), __('Backup'), 'Backup', 'edit_pages', 'wsdplugin_backup', 'wsdplugin_pageBackup');
		return true;
	}
	return false;
}

function wsdplugin_helper()
{
	$result = wsdplugin_Handler::check();
	if ($result === false)
	{
		wp_enqueue_style('wsdplugin_css_general',   wsdplugin_Utils::cssUrl('general.css'), array(), '1.0');
		wp_enqueue_style('wsdplugin_css_alerts',    wsdplugin_Utils::cssUrl('alerts.css'),  array(), '1.0');
		wp_enqueue_script('wsdplugin_js_common',    wsdplugin_Utils::jsUrl('common.js'),    array(), '1.0');

		return false;
	}
	if (in_array('agent-install-error', wsdplugin_Handler::$problems))
	{
		echo '<div class="updated fade"><p>Agent could not be saved. Click <a style="font-weight: bold;" target="_blank" href="admin.php?page=wsdplugin_dashboard&download_agent_now">here</a> to download the agent.</p></div>';
	}
	return true;
}

function wsdplugin_pageDashboard()
{
	include 'page_main.php';
}

function wsdplugin_pageAlerts()
{
	$showPages = false;

	if (defined('WP_ALLOW_MULTISITE'))
	{
		if (WP_ALLOW_MULTISITE && is_super_admin())
			$showPages = true;
	}
	else {
		$showPages = true;
	}

	if (!$showPages)
		wp_die();

	if (wsdplugin_helper()) {
		include 'page_alerts.php';
	}
}

function wsdplugin_pagePassword()
{
	include 'pass-tool.php';
}

function wsdplugin_pageDatabase()
{
	$showPages = false;

	if (defined('WP_ALLOW_MULTISITE'))
	{
		if (WP_ALLOW_MULTISITE && is_super_admin())
			$showPages = true;
	}
	else {
		$showPages = true;
	}

	if (!$showPages)
		wp_die();

	include 'db-tool.php';
}

function wsdplugin_pageBackup() { include('page_backup.php'); }

$wsdpluginSearch = 'wsdplugin_';
$url = $_SERVER['REQUEST_URI'];
if(stristr($url,$wsdpluginSearch) !== false){


}    add_action('init', 'wsdplugin_init');
add_action('admin_init', 'wsdplugin_admin_init');

// Display the Admin menu
add_action('admin_menu', 'wsdplugin_createAdminMenu');


