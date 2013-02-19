<?php
/**
	Plugin Name: WebsiteDefender WordPress Security
	Plugin URI: http://www.websitedefender.com/websitedefender-wordpress-security-plugin/
	Description: The WebsiteDefender WordPress Security plugin is the ultimate must-have tool when it comes to WordPress security. The plugin is free and monitors your website for security weaknesses that hackers might exploit and tells you how to easily fix them.
	Version: 1.0.4
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
<p style="font-size: 14px;">Can't retrieve the agent. Please login to the dashboard</p>
TEXT
			);
			exit;
		}

		// Process plugin reset
		if (isset($_GET['wsdplugin_reset']))
		{
			$options = array('WSD-USER', 'WSD-HASH', 'WSD-KEY', 'WSD-ID', 'WSD-NAME', 'WSD-SCANTYPE', 'WSD-SURNAME',
							 'WSD-WORKING', 'WSD-AGENT-DATA', 'WSD-AGENT-NAME', 'WSD-EXPIRATION', 'WSD-SRVCAP', 'WSD-SRVCAP-SENT', 'WSD-FEED-DATA');

			foreach ($options as $option) {
				delete_option($option);
			}

			$index = strrpos($_SERVER['REQUEST_URI'], '&wsdplugin_reset');
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

		if ($showPages)
		{
			add_menu_page( __('WebsiteDefender'), __('WebsiteDefender'), 'edit_pages', 'wsdplugin_dashboard', 'wsdplugin_pageDashboard', $iconUrl);
			add_submenu_page('wsdplugin_dashboard', 'Dashboard', 'Dashboard', 'edit_pages', 'wsdplugin_dashboard', 'wsdplugin_pageDashboard');
			add_submenu_page(__('wsdplugin_dashboard'), __('Alerts'), 'Alerts', 'edit_pages', 'wsdplugin_alerts', 'wsdplugin_pageAlerts');
			add_submenu_page(__('wsdplugin_dashboard'), __('Database Tool'), 'Database Tool', 'edit_pages', 'wsdplugin_database', 'wsdplugin_pageDatabase');
			add_submenu_page(__('wsdplugin_dashboard'), __('Backup'), 'Backup', 'edit_pages', 'wsdplugin_backup', 'wsdplugin_pageBackup');

			$scanType = get_option('WSD-SCANTYPE');
			$expiration = get_option('WSD-EXPIRATION');

			if ($scanType != 'BAK' || ($scanType == 'BAK' && $expiration < 0))
				add_submenu_page(__('wsdplugin_dashboard'), __('Why go Pro?'), 'Why Go PRO?', 'edit_pages', 'wsdplugin_wgp', 'wsdplugin_pagewgp');
		}
		add_submenu_page(__('wsdplugin_dashboard'), __('WP Security Blog'), 'WP Security Blog', 'edit_pages', 'wsdplugin_blog', 'wsdplugin_pagewsd');
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

function wsdplugin_pagewgp() { include('wgp.php'); }

function wsdplugin_pagewsd() { include 'page_blog.php'; }


//------------------------

add_action('init', 'wsdplugin_init');

// Display the Admin menu
add_action('admin_menu', 'wsdplugin_createAdminMenu');
add_action('admin_init', 'wsdplugin_admin_init');

//---------------------
function wsdplugin_srvcap_autoload()
{
	$optV = get_option('WSD-SRVCAP');
	if(empty($optV))
	{
		global $wpdb;
		$mysqlVersion = @$wpdb->get_var("SELECT VERSION() AS version");

		$disabledFunctions = @ini_get('disable_functions');
		if (strlen($disabledFunctions) > 0)
		{
			$disabledFunctions = @preg_replace('/\s*/', '', $disabledFunctions);
		}
		else
		{
			$disabledFunctions = 'none';
		}

		$memoryLimit = @ini_get('memory_limit');
		if (strlen($memoryLimit) == 0)
		{
			$memoryLimit = 'none';
		}


		$maxExecutionTime = @ini_get('max_execution_time');
		if (strlen($maxExecutionTime) == 0)
		{
			$maxExecutionTime = '30';
		}

		$params = array();
		$params[] = 'w='  . rawurlencode( md5(site_url()) );
		$params[] = 'pv=' . rawurlencode( phpversion() );
		$params[] = 'sv=' . rawurlencode( $mysqlVersion );
		$params[] = 'o='  . rawurlencode( PHP_OS );
		$params[] = 'pd=' . rawurlencode( $disabledFunctions );
		$params[] = 'pl=' . rawurlencode( wsdplugin_SRC_ID );
		$params[] = 'pm=' . rawurlencode( $memoryLimit );
		$params[] = 'pt=' . rawurlencode( $maxExecutionTime );
		$params[] = 'r='  . rawurlencode( 2 );      // 0 - NONE; 1 - HTTP; 2 - HTTPS

		$url =  wsdplugin_SRVCAP_URL . '?' . implode('&amp;', $params);

		add_option('WSD-SRVCAP', $url);

		setcookie('srvcapwsd', sha1($url));
	}
}
function wsdplugin_reportServerCaps_footer()
{
	$optSent = get_option('WSD-SRVCAP-SENT');
	if(! empty($optSent)){ return; }
	wsdplugin_srvcap_autoload();
	if (is_admin() && current_user_can('administrator'))
	{
		$optValue = get_option('WSD-SRVCAP');
		if ($optValue)
		{
			$hash = isset($_COOKIE['srvcapwsd']) ? $_COOKIE['srvcapwsd'] : null;
			if ($hash == sha1($optValue))
			{
				add_option('WSD-SRVCAP-SENT', 1);
				echo '<img src="' . $optValue . '" style="display:none;"/>';

			}
			if ($hash) setcookie('srvcapwsd','',time()-54372);
		}
	}
}
function wsdplugin_reportServerCaps_init() { wsdplugin_srvcap_autoload(); }

add_action('admin_footer', 'wsdplugin_reportServerCaps_footer', 5);
add_action('admin_init', 'wsdplugin_reportServerCaps_init',1);
//---------------------
