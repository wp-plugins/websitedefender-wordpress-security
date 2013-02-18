<?php
if (!defined('wsdplugin_WSD_PLUGIN_SESSION')) exit;

// Include CSS
wp_enqueue_style('wsdplugin_css_general',   wsdplugin_Utils::cssUrl('general.css'), array(), '1.0');
wp_enqueue_style('wsdplugin_css_status',    wsdplugin_Utils::cssUrl('status.css'),  array(), '1.0');
wp_enqueue_style('wsdplugin_css_alerts',    wsdplugin_Utils::cssUrl('alerts.css'),  array(), '1.0');


$targetId   = get_option('WSD-ID');
$user       = get_option('WSD-USER');
$hash       = get_option('WSD-HASH');


$expiration = get_option('WSD-EXPIRATION');
if ($expiration == -1) { $expiration = 'expired'; }
else if ($expiration !== false) { $expiration = (int)floor($expiration / 60.0 / 60.0 / 24.0); }

//TODO: Add the error box needed to display AJAX errors


// Include jQuery UI
wp_enqueue_script('jquery-ui-widget');
wp_enqueue_script('jquery-ui-position');
wp_enqueue_script('jquery-ui-mouse');

// Include Custom JavaScript
wp_enqueue_script('wsdplugin_js_logger', wsdplugin_Utils::jsUrl('logger.js'), array('jquery-ui-widget'), '1.0');
wp_enqueue_script('wsdplugin_js_request', wsdplugin_Utils::jsUrl('request.js'), array('jquery'), '1.0');
wp_enqueue_script('wsdplugin_js_common', wsdplugin_Utils::jsUrl('common.js'), array('jquery'), '1.0');
wp_enqueue_script('wsdplugin_js_ui_editionFeatures', wsdplugin_Utils::jsUrl('jquery.ui.editionFeatures.js'), array('jquery'), '1.0');
wp_enqueue_script('wsdplugin_js_jsrender', wsdplugin_Utils::jsUrl('jsrender.js'), array(), '1.0');



/*
 * 1.	The plugin should be able to check through the API to confirm if a user is paying for
 *      WebsiteDefender PRO or using a free account.
 */
$optInfo = get_option('WSD-SCANTYPE');


if ($optInfo === 'BAK' && get_option('WSD-EXPIRATION') == -1)
	$optInfo = 'WSDFREE';


// 1. DISPLAY THE BANNER
?>
<div id="wsdplugin_w_edition_features" class="wrap wsdplugin_content" xmlns="http://www.w3.org/1999/html">

	<div id="wrap wsdplugin_advert">
		<?php if(empty($optInfo)){ ?>
		<a href="<?php echo wsdplugin_Handler::site_url().'wp-admin/admin.php?page=wsdplugin_alerts';?>">
			<img src="<?php echo wsdplugin_PLUGIN_PATH ;?>img/banners/free.jpg" title="" alt=""/></a>
		<?php } elseif($optInfo == 'BAK') { ?>
		<img src="<?php echo wsdplugin_PLUGIN_PATH ;?>img/banners/pro.jpg" title="" alt=""/>
		<?php } else if ($optInfo == 'WSDPRO') { ?>
		<a href="https://dashboard.websitedefender.com/" target="_blank">
			<img src="<?php echo wsdplugin_PLUGIN_PATH ;?>img/banners/trial-<?php echo $expiration; ?>-days.jpg" title="" alt=""/></a>
		<?php } else { ?>
		<a href="http://www.websitedefender.com/websitedefender-features/" target="_blank">
			<img src="<?php echo wsdplugin_PLUGIN_PATH ;?>img/banners/free.jpg" title="" alt=""/></a>
		<?php } ?>
	</div>



	<?php
	/*
	 * SECTION #2
	In this section we should list all the changes the plugin has done automatically to the
	WordPress installation.
	 */
	wp_enqueue_style('wsdplugin_css_general',   wsdplugin_Utils::cssUrl('general.css'), array(), '1.0');

	$wsdoptinfo = get_option('WSD-FIRST-INSTALL');
	if(empty($wsdoptinfo)){
		// 1
		add_option('WSD-FIRST-INSTALL', 1);
	}
	?>

	<style type="text/css">
		.wsdplugin_alert_indicator {
			padding-right: 50px !important;
		}
		.wsdplugin_hint_small {
			font-size: 11px;
		}
		.wsdplugin_features_box table td {
			padding-top: 5px;
			padding-bottom: 5px;
			font-size: 13px;
		}
		.wsdplugin_features_box table .alternate {
			background-color: #FFFFFF;
		}
		.wsdplugin_features_box table tr + td
		{
			border-right: 1px solid #FFFFFF;
		}
		.wsdplugin_alert_indicator {
			border-left: 1px solid #DFDFDF !important;
		}
	</style>


	<script id="wsdplugin_free_edition_features_tpl" type="text/x-jsrender">
		<div class="wsdplugin_page_title">
			<h2>Free Edition Features</h2>
			<table class="widefat" style="border: none; max-width: 728px;">
				<tbody>
					<tr class="alternate">
						<td>Hide the WordPress version</td>
						<td class="wsdplugin_alert_indicator wsdplugin_alert_indicator_info"></td>
					</tr>
					<tr>
						<td>Disable error messages</td>
						<td class="wsdplugin_alert_indicator wsdplugin_alert_indicator_info"></td>
					</tr>
					<tr class="alternate">
						<td>Switch off database error reporting</td>
						<td class="wsdplugin_alert_indicator wsdplugin_alert_indicator_info"></td>
					</tr>
					<tr>
						<td>Remove WordPress generator META tag</td>
						<td class="wsdplugin_alert_indicator wsdplugin_alert_indicator_info"></td>
					</tr>
					<tr class="alternate">
						<td>Remove Windows Live Writer META tag</td>
						<td class="wsdplugin_alert_indicator wsdplugin_alert_indicator_info"></td>
					</tr>
					<tr>
						<td>Remove Really Simple Discovery</td>
						<td class="wsdplugin_alert_indicator wsdplugin_alert_indicator_info"></td>
					</tr>
					<tr class="alternate">
						<td>WordPress Core Update Notifications sent only to Admins</td>
						<td class="wsdplugin_alert_indicator wsdplugin_alert_indicator_info"></td>
					</tr>
					<tr>
						<td>Plugin update notifications sent only to Admins</td>
						<td class="wsdplugin_alert_indicator wsdplugin_alert_indicator_info"></td>
					</tr>
					<tr class="alternate">
						<td>Theme update notifications sent only to Admins</td>
						<td class="wsdplugin_alert_indicator wsdplugin_alert_indicator_info"></td>
					</tr>
					<tr>
						<td>Prevent WordPress wp-content Directory Listing</td>
						<td class="wsdplugin_alert_indicator wsdplugin_alert_indicator_info"></td>
					</tr>
					<tr class="alternate">
						<td>
							Check DNS Configuration - <small class="wsdplugin_hint_small">
							{{if u == 0}}DNS not being checked. <a href="admin.php?page=wsdplugin_alerts">Sign Up</a>
							{{else}}
								{{if !dns}}Not available yet.
								{{else dns && dns.status}}Up and running - Last check on <strong>{{>dns.date}}</strong>
								{{else dns && dns.date}}DNS not responding - Last check on <strong>{{>dns.date}}</strong>
								{{/if}}
							{{/if}}
							</small>
						</td>
						<td class="wsdplugin_alert_indicator {{if u == 0}}wsdplugin_alert_indicator_critical{{else}}wsdplugin_alert_indicator_info{{/if}}"></td>
					</tr>
					<tr>
						<td>Check Domain Expiration Date - <small class="wsdplugin_hint_small">
							{{if u == 0}}Domain Expiry Date not monitored. <a href="admin.php?page=wsdplugin_alerts">Sign Up</a>
							{{else}}
								{{if dnsexp && dnsexp.expired}}Expired
								{{else dnsexp && dnsexp.date}}Domain Expires on <strong>{{>dnsexp.date}}</strong>
								{{else}}Not available yet
								{{/if}}
							{{/if}}
							</small>
						</td>
						<td class="wsdplugin_alert_indicator {{if u == 0}}wsdplugin_alert_indicator_critical{{else}}wsdplugin_alert_indicator_info{{/if}}"></td>
					</tr>
				</tbody>
			</table>
		</div>
	</script>

	<script id="wsdplugin_pro_edition_features_tpl" type="text/x-jsrender">
		<div class="wsdplugin_page_title" style="margin-top: 30px;">
			<h2>Pro Edition Features
				{{if u == 0}} - <small class="wsdplugin_hint_small">These features will be enabled once you subscribe to WebsiteDefender Pro. <a href="admin.php?page=wsdplugin_wgp">Why Go?</a></small>
				{{else trial}} - <small class="wsdplugin_hint_small">These features will stop functioning after the Trial period. <a href="admin.php?page=wsdplugin_wgp">Why Go Pro?</a></small>
				{{/if}}
			</h2>
			<table class="widefat" style="border: none; max-width: 728px;">
				<tbody>
					<tr class="alternate">
						<td>Malware Scan - <small class="wsdplugin_hint_small">
							{{if u != 2}}Your Website might be infected. <a href="admin.php?page=wsdplugin_alerts">Go Pro</a>
							{{else lms && lms.date == '-'}}Not scanned yet.
							{{else lms && lms.infected}}Your website is infected with malware&nbsp; - Last check on <strong>{{>lms.date}}</strong>
							{{else}}Your website is clean&nbsp; - Last check on <strong>{{>lms.date}}</strong>
							{{/if}}</small>
						</td>
						<td class="wsdplugin_alert_indicator {{if u != 2}}wsdplugin_alert_indicator_critical{{else}}wsdplugin_alert_indicator_info{{/if}}"></td>
					</tr>
					<tr>
						<td>Hacker Detection - <small class="wsdplugin_hint_small">
							{{if u != 2}}Hackers will not be detected. <a href="admin.php?page=wsdplugin_alerts">Go Pro</a>
							{{else lhdc && lhdc.date == '-'}}Not scanned yet.
							{{else}}Last scan on <strong>{{>lhdc.date}}</strong>
							{{/if}}</small>
						</td>
						<td class="wsdplugin_alert_indicator {{if u != 2}}wsdplugin_alert_indicator_critical{{else}}wsdplugin_alert_indicator_info{{/if}}"></td>
					</tr>
					<tr>
						<td>Complete Security scan - <small class="wsdplugin_hint_small">
							{{if u != 2}}Plugin vulnerabilities will not be detected. <a href="admin.php?page=wsdplugin_alerts">Go Pro</a>
							{{else lcss && lcss.date == '-'}}Not scanned yet.
							{{else}}Last scan on <strong>{{>lcss.date}}</strong>
							{{/if}}</small>
						</td>
						<td class="wsdplugin_alert_indicator {{if u != 2}}wsdplugin_alert_indicator_critical{{else}}wsdplugin_alert_indicator_info{{/if}}"></td>
					</tr>
					<tr class="alternate">
						<td>One Click Malware Removal{{if u != 2}}<small class="wsdplugin_hint_small"> - You cannot recover from malware. <a href="admin.php?page=wsdplugin_alerts">Go Pro</a>{{/if}}</small></td>
						<td class="wsdplugin_alert_indicator {{if u != 2}}wsdplugin_alert_indicator_critical{{else}}wsdplugin_alert_indicator_info{{/if}}"></td>
					</tr>
					<tr>
						<td>Admin Account Surveillance - <small class="wsdplugin_hint_small">
							{{if u != 2}}New Admins not detected. <a href="admin.php?page=wsdplugin_alerts">Go Pro</a>
							{{else laass && laass.date == '-'}}Not scanned yet.
							{{else}}Last scan on <strong>{{>laass.date}}
							{{/if}}</strong></small></td>
						<td class="wsdplugin_alert_indicator {{if u != 2}}wsdplugin_alert_indicator_critical{{else}}wsdplugin_alert_indicator_info{{/if}}"></td>
					</tr>
				</tbody>
			</table>
		</div>
	</script>


	<div class="wsdplugin_website_detail">
		<div class="error wsdplugin_error_box" style="display: none">
			<p>
				<strong>Sample error message.</strong>
				<a class="dismiss" href="#">Dismiss</a>
			</p>
		</div>

		<div class="wsdplugin_features_box" data-jsrender-template="wsdplugin_free_edition_features_tpl"></div>
		<div style="clear:both;"></div>
		<div class="wsdplugin_features_box" data-jsrender-template="wsdplugin_pro_edition_features_tpl"></div>
	</div>

		<?php if (get_option('WSD-SCANTYPE', '') == '') { ?>
		<div style="margin-top: 30px; margin-bottom: 10px;" class="wsdplugin_special_text">
			<p>
				You are currently using the limited FREE Edition. You need to <a href="admin.php?page=wsdplugin_alerts">Sign Up</a> to enable all the features in the FREE Edition.
				Signing up will also enable the evaluation fo the PRO Edition features.
			</p>
		</div>
		<?php } ?>
</div>


<script type="text/javascript">

	jQuery(function($)
	{
		<?php echo 'WSDPLUGIN_JSRPC_URL = "', wsdplugin_JSRPC_URL, '";'; ?>
		$('#wsdplugin_w_edition_features').wsdplugin_editionFeatures(
		{
			targetId: <?php echo "'{$targetId}'";?>,
			user: <?php echo "'", str_replace('"', '\"', $user), "'";?>,
			hash: <?php echo "'", $hash, "'";?>
		});
	});
</script>