<?php
/**
    Plugin Name: WebsiteDefender WordPress Security
    Plugin URI: http://www.websitedefender.com/websitedefender-wordpress-security-plugin/
    Description: The WebsiteDefender WordPress Security plugin is the ultimate must-have tool when it comes to WordPress security. The plugin is free and monitors your website for security weaknesses that hackers might exploit and tells you how to easily fix them.
    Version: 1.0.1
    Author: WebsiteDefender
    Author URI: http://websitedefender.com/
    License: GPLv2 or later
    Text Domain: WSDWP_SECURITY
    Domain Path: /languages
 */
//!! So we can use the "user" related functions
@require_once(ABSPATH.'wp-includes/pluggable.php');
$wsdplugin_nonce = wp_create_nonce();
//===============

require_once("inc/settings.php");
require_once("inc/functions.php");


function wsdplugin_init()
{
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
        if (isset($_GET['reset']))
        {
            $options = array('WSD-USER', 'WSD-HASH', 'WSD-KEY', 'WSD-ID', 'WSD-NAME', 'WSD-SCANTYPE', 'WSD-SURNAME',
                             'WSD-WORKING', 'WSD-AGENT-DATA', 'WSD-AGENT-NAME', 'WSD-EXPIRATION');

            foreach ($options as $option) {
                delete_option($option);
            }

	        $index = strrpos($_SERVER['REQUEST_URI'], '&reset');
	        header('Location: ' . substr($_SERVER['REQUEST_URI'], 0, $index));
            exit;
        }
    }
}


function wsdplugin_admin_init()
{
    if (is_admin() && current_user_can('administrator'))
    {
        define('wsdplugin_WSD_PLUGIN_SESSION', TRUE);
	    define('wsdplugin_WSD_PLUGIN_BASE_URL', plugin_dir_url(__FILE__));
	    define('wsdplugin_WSD_PLUGIN_BASE_PATH', plugin_dir_path(__FILE__));

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
		$iconUrl = plugin_dir_url(__FILE__) . 'img/wsd-logo-small.png';
		add_menu_page( __('WP Security'), __('WP Security'), 'edit_pages', 'wsdplugin_dashboard', 'wsdplugin_pageDashboard', $iconUrl);
		add_submenu_page(__('wsdplugin_dashboard'), __('WebsiteDefender Security Alerts'), 'WebsiteDefender Security Alerts', 'edit_pages', 'wsdplugin_alerts', 'wsdplugin_pageAlerts');
		add_submenu_page(__('wsdplugin_dashboard'), __('Strong Password Generator'), 'Strong Password Generator', 'edit_pages', 'wsdplugin_password', 'wsdplugin_pagePassword');
		add_submenu_page(__('wsdplugin_dashboard'), __('Database Tool'), 'Database Tool', 'edit_pages', 'wsdplugin_database', 'wsdplugin_pageDatabase');
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
	include 'db-tool.php';
}


add_action('init', 'wsdplugin_init');
add_action('admin_init', 'wsdplugin_admin_init');

// Display the Admin menu
add_action('admin_menu', 'wsdplugin_createAdminMenu');
