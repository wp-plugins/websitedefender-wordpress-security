<?php
/*
 * GLOBALS
 */

/**
 * Sets the plug-in's domain
 */
	define('ACX_TEXT_DOMAIN','WSDWP_SECURITY');

/**
 * Sets the plug-in's prefix
 */
    define('ACX_PREFIX', 'acx_');

/**
 * Sets the System path to the plug-in directory
 */
    define('ACX_PLUGIN_DIR', realpath(dirname(__FILE__).'/../').'/');

/**
 * Sets the HTTP path to the plug-in's directory
 */
    define('ACX_PLUGIN_PATH', trailingslashit(WP_PLUGIN_URL).ACX_PLUGIN_NAME.'/');

//@@ Check to see if this is a request for the plug-in's pages
$_acx_shouldLoad = false;
$_acx_qs = (empty($_SERVER['QUERY_STRING']) ? '' : $_SERVER['QUERY_STRING']);
if (preg_match("/".ACX_PREFIX."/",$_acx_qs))
{
    $_acx_shouldLoad = true;
}

/**
 * Sets whether or not this is a request for one of the plug-in's pages
 */
    define('ACX_SHOULD_LOAD', $_acx_shouldLoad);

/**
 * Set the path to the WebsiteDefender.com feed
 */
    define('ACX_BLOG_FEED','http://www.websitedefender.com/feed/');

/**
* Sets the list of files to check for permissions
* @type array
*/
$_acx_base_path  = trailingslashit(ABSPATH);
$_acx_wpAdmin    = $_acx_base_path.'wp-admin';
$_acx_wpContent  = $_acx_base_path.'wp-content';
$_acx_wpIncludes = $_acx_base_path.'wp-includes';

$acxFileList = array(
//@@ Directories
	'root directory' => array( 'filePath' => $_acx_base_path, 'suggestedPermissions' => '0755'),
	'wp-admin' => array( 'filePath' => $_acx_wpAdmin, 'suggestedPermissions' => '0755'),
	'wp-content' => array( 'filePath' => $_acx_wpContent, 'suggestedPermissions' => '0755'),
	'wp-includes' => array( 'filePath' => $_acx_wpIncludes, 'suggestedPermissions' => '0755'),

//@@ Files
	'.htaccess' => array( 'filePath' => $_acx_base_path.'.htaccess', 'suggestedPermissions' => '0640'),
    'readme.html' => array( 'filePath' => $_acx_base_path.'readme.html', 'suggestedPermissions' => '0400'),
	'wp-config.php' => array( 'filePath' => $_acx_base_path.'wp-config.php', 'suggestedPermissions' => '0644'),
	'wp-admin/index.php' => array( 'filePath' => $_acx_wpAdmin.'/index.php', 'suggestedPermissions' => '0644'),
	'wp-admin/.htaccess' => array( 'filePath' => $_acx_wpAdmin.'/.htaccess', 'suggestedPermissions' => '0640'),
);




//@@ Clean up
	unset($_acx_shouldLoad, $_acx_qs, $_acx_base_path, $_acx_wpAdmin, $_acx_wpContent, $_acx_wpIncludes);

/*[ End of file: acx-globals.php ]*/