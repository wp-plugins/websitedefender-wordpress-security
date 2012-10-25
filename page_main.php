<?php
if (!defined('wsdplugin_WSD_PLUGIN_SESSION')) exit;


// Include CSS
wp_enqueue_style('wsdplugin_css_general',   wsdplugin_Utils::cssUrl('general.css'), array(), '1.0');
wp_enqueue_style('wsdplugin_css_status',    wsdplugin_Utils::cssUrl('status.css'),  array(), '1.0');
wp_enqueue_style('wsdplugin_css_alerts',    wsdplugin_Utils::cssUrl('alerts.css'),  array(), '1.0');

// Include JavaScript
wp_enqueue_script('jquery-ui-widget');
wp_enqueue_script('wsdplugin_js_logger', wsdplugin_Utils::jsUrl('logger.js'), array('jquery-ui-widget'), '1.0');
wp_enqueue_script('wsdplugin_js_ui_local_alerts', wsdplugin_Utils::jsUrl('jquery.ui.local-alerts.js'), array(), '1.0');


function wsdplugin_render_msg($checks)
{
	$result = null;
	foreach ($checks as $check)
	{
		$value = array_key_exists($check, wsdplugin_security::$fixes)
			? wsdplugin_security::$fixes[$check]
			: null;

		if ($value === null)
		{
			if ($result === null) $result = "Disabled";
			else if($result === "On") $result = "Partial";
		}
		else if ($value === true)
		{
			if ($result === null) $result = "On";
			else if ($result === "Disabled") $result = "Partial";
			else if ($result === "Off") $result = "Partial";
		}
		else if($value === false)
		{
			if ($result === null) $result = "Off";
			else if ($result === "On") $result = "Partial";
		}
	}
	echo $result;
}


function wsdplugin_render_alert($type, $data)
{
	$result = array(
		'title' 	=> $type,
		'time'      => date('j M Y H:i', $data[0]),
		'severity'  => $data[1]
	);

	switch ($type)
	{
		case 'core-update';
			$result['title'] = __('An updated version of WordPress is available');
			$result['detail'] = <<<HTML
<table class="alertDetailsTable">
<tr>
	<td class="alertDetailsTableTitle" colspan="2">Update details</td></tr>
<tr>
	<td width="190px" class="alertDetailsTableTdName">WordPress installed version</td><td class="alertDetailsTableTdValue">%version%</td></tr>
<tr>
	<td class="alertDetailsTableTdName">WordPress latest version</td><td class="alertDetailsTableTdValue"><b>%latest_version%</b></td></tr>
</table>
HTML;
			global $wp_version;
			$result['detail'] = str_replace(array('%version%', '%latest_version%'), array($wp_version, $data[2]), $result['detail']);
			break;
		case 'user-admin-found':
			$result['title'] = __('WordPress admin user exists');
			$result['detail'] = __('It is recommended to rename the default WordPress admin account. Find out why you need to rename it and how to rename it from <a target="_blank" href="http://www.websitedefender.com/wordpress-security/default-wordpress-administrator-account/">here</a>');
			break;
		case 'table-prefix':
			$result['title'] = __('Default WordPress table prefix in use');
			$result['detail'] = __('Rename the default WordPress table prefix to protect your WordPress from zero day vulnerabilities. You can use our <a href="admin.php?page=wsdplugin_database">database tool</a> to automatically rename the table prefix. You can also change it manually as suggested <a target="_blank" href="http://www.websitedefender.com/wordpress-security/wordpress-database-security-tables-prefix/">here</a>');
			break;
		case 'no-index-wp-content':
			$result['title'] = __('Directory listing is enabled');
			$result['detail'] = sprintf(__('If directory listing is enabled in your WordPress wp-content directory a malicious user can gather information about your WordPress to craft an attack against your website. <br /><strong>Path: </strong> %s'), htmlentities(WP_CONTENT_DIR));
			break;
		case 'no-index-plugins':
			$result['title'] = 'Default document not found in plugins directory';
			$result['detail'] = sprintf(__('If directory listing is enabled in your WordPress wp-content directory a malicious user can gather information about your WordPress to craft an attack against your website. <br /><strong>Path:</strong> %s'), htmlentities(WP_CONTENT_DIR . '/plugins/'));
			break;
		case 'no-index-themes':
			$result['title'] = __('Default document not found in themes directory');
			$result['detail'] = sprintf(__('If directory listing is enabled in your WordPress wp-content directory a malicious user can gather information about your WordPress to craft an attack against your website. <br /><strong>Path: </strong> %s'), htmlentities(WP_CONTENT_DIR . '/themes/'));
			break;
		case 'no-index-uploads':
			$result['title'] = __('Default document not found in uploads directory');
			$result['detail'] = sprintf(__('<strong>Path: %s</strong> '), htmlentities(WP_CONTENT_DIR . '/uploads/'));
			break;
		case 'no-htaccess-wp-admin':
			$result['title'] = __('No htaccess file defined in the wp-admin directory');
			$result['detail'] = __('You can use an htaccess file to protect your WordPress wp-admin directory. Learn more by reading our article <a target="_blank" href="http://www.websitedefender.com/wordpress-security/htaccess-files-wordpress-security/">htaccess Files and WordPress Security</a>');
			break;
		case 'readme-in-root':
			$result['title'] = __('Readme file found in the root directory');
			$result['detail'] = __('Delete the file readme.txt from your WordPress root directory. If this file is not deleted a malicious user can use such information to craft an attack against your WordPress.');
			break;
		case 'access-rights':
			$result['title'] = __('User has too many rights for database: ') . implode(', ', $data[2]);
			$result['detail'] = __('The MySQL user used to access the WordPress MySQL database has too many rights and this could lead to security issues. You can restrict such access to read and write only.');
			break;
	}
	return $result;
}

?>
<div class="wrap wsdplugin_content" xmlns="http://www.w3.org/1999/html">


<div class="wsdplugin_website_detail">


	<div style="margin-top: 30px; margin-bottom: 10px;">
		<p>Hi. Thanks for downloading our WordPress security plugin! The plugin is now busy working away at improving
			the security on your website, giving you the peace of mind to be getting on with other things.</p>
	</div>

	<div class="wsdplugin_page_fixes" style="margin-top: 10px">
        <div class="wsdplugin_page_title">
            <div class="icon32 wsdplugin_status_page_ico"><br></div>
            <h2>Keeping your website secure with these automatic plugin changes <a class="add-new-h2" href="admin.php?page=wsdplugin_dashboard">Refresh</a> </h2>
        </div>

        <div class="wsdplugin_fixes_page" style="width: 620px;">
            <table class="widefat">
                <tr><td>Hides WordPress version</td><td><?php wsdplugin_render_msg(array('hideWpVersionBackend','hideWpVersion'));?></td></tr>
                <tr><td>Hides error messages</td><td><?php wsdplugin_render_msg(array('removeErrorNotificationsFrontEnd'));?></td></tr>
                <tr><td>Sets database errors to Off</td><td><?php wsdplugin_render_msg(array('disableErrorReporting'));?></td></tr>
                <tr><td>Removes WP generator Meta Tag</td><td><?php wsdplugin_render_msg(array('removeWpMetaGenerators'));?></td></tr>
                <tr><td>Remove Windows Live write meta tags</td><td><?php wsdplugin_render_msg(array('removeWindowsLiveWriter'));?></td></tr>
                <tr><td>Removes Really Simple Discovery</td><td><?php wsdplugin_render_msg(array('removeReallySimpleDiscovery'));?></td></tr>
                <tr><td>Removes core updates notifications to users</td><td><?php wsdplugin_render_msg(array('removeCoreUpdateNotification'));?></td></tr>
                <tr><td>Removes plugin updates notifications to users</td><td><?php wsdplugin_render_msg(array('removePluginUpdateNotifications'));?></td></tr>
                <tr><td>Removes theme updates notifications</td><td><?php wsdplugin_render_msg(array('removeThemeUpdateNotifications'));?></td></tr>
                <tr><td>Prevent wp-content directory listing</td><td><?php wsdplugin_render_msg(array('preventWpContentDirectoryListing')) ?></td></tr>
                <tr><td style="padding-top: 5px;">Scan of WordPress installation (Alerts below)</td><td style="padding-top: 5px;">On</td></tr>
            </table>
        </div>
	</div>

	<?php if (get_option('WSD-SCANTYPE', '') == '') { ?>
	<div style="margin-top: 30px; margin-bottom: 10px;">
		<p>
            The plugin also runs several security checks to help you secure your WordPress.
			Refer to the list of Plugin Security Alerts to improve the security of your WordPress.
			For more advanced security checks and daily malware scanning of your WordPress, navigate to the
            <a href="admin.php?page=wsdplugin_alerts">WebsiteDefender Security Alerts</a> and register for a 15 days trial
		</p>
	</div>
	<?php } ?>
	<div class="wsdplugin_page_local_alerts" style="margin-top: 10px">
        <div class="wsdplugin_page_title">
            <div class="icon32 wsdplugin_status_page_ico"><br></div>
            <h2>Some plugin security points you'll need to take a look at <a class="add-new-h2" href="admin.php?page=wsdplugin_dashboard">Refresh</a> </h2>
        </div>

		<div class="wsdplugin_alert_section_body">
	        <table class="widefat">
	            <tbody></tbody>
	        </table>
        </div>
	</div>

</div>

	<script type="text/javascript">

			<?php
				$alerts = array();
				foreach (wsdplugin_security::$alerts as $key => $value)
				{
					foreach ($value as $alert)
						$alerts[] = wsdplugin_render_alert($key, $alert);
				}
				$alerts = json_encode($alerts);
			?>
        jQuery(function($) {
            <?php echo 'WSDPLUGIN_JSRPC_URL = "', wsdplugin_JSRPC_URL, '";'; ?>
			$('.wsdplugin_page_local_alerts').wsdplugin_local_alerts({alerts: <?php echo $alerts;?>});
		});

	</script>

</div>
