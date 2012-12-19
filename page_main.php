<?php
if (!defined('wsdplugin_WSD_PLUGIN_SESSION')) exit;

// Include CSS
wp_enqueue_style('wsdplugin_css_general',   wsdplugin_Utils::cssUrl('general.css'), array(), '1.0');
wp_enqueue_style('wsdplugin_css_status',    wsdplugin_Utils::cssUrl('status.css'),  array(), '1.0');

// Include jQuery UI
wp_enqueue_script('jquery-ui-widget');

// Include Custom JavaScript
wp_enqueue_script('wsdplugin_js_hashchange', wsdplugin_Utils::jsUrl('jquery.hashchange.js'), array('jquery'), '1.0');
wp_enqueue_script('wsdplugin_js_uh', wsdplugin_Utils::jsUrl('uh.js'), array('jquery', 'jquery-ui-widget'), '1.0');
wp_enqueue_script('wsdplugin_js_logger', wsdplugin_Utils::jsUrl('logger.js'), array('jquery-ui-widget'), '1.0');
wp_enqueue_script('wsdplugin_js_request', wsdplugin_Utils::jsUrl('request.js'), array('jquery'), '1.0');
wp_enqueue_script('wsdplugin_js_common', wsdplugin_Utils::jsUrl('common.js'), array('jquery'), '1.0');
wp_enqueue_script('wsdplugin_js_ui_main', wsdplugin_Utils::jsUrl('jquery.ui.main.js'), array('jquery'), '1.0');
wp_enqueue_script('wsdplugin_js_ui_status', wsdplugin_Utils::jsUrl('jquery.ui.status.js'), array('jquery-ui-widget'), '1.0');


$targetId   = get_option('WSD-ID');
$user       = get_option('WSD-USER');
$hash       = get_option('WSD-HASH');


/*
 * 1.	The plugin should be able to check through the API to confirm if a user is paying for
 *      WebsiteDefender PRO or using a free account.
 */
$optInfo = get_option('WSD-SCANTYPE');

// 1. DISPLAY THE ADVERT
?>
<div class="wrap wsdplugin_content" xmlns="http://www.w3.org/1999/html">

<div id="wrap wsdplugin_advert">
    <?php if(empty($optInfo)){ ?>
        <a href="<?php echo wsdplugin_Handler::site_url().'wp-admin/admin.php?page=wsdplugin_alerts';?>">
        <img src="//www.websitedefender.com/images/plugins/banners/not_registered.jpg" title="" alt=""/></a>
    <?php } elseif($optInfo == 'BAK') { ?>
        <img src="//www.websitedefender.com/images/plugins/banners/pro_users.jpg" title="" alt=""/>
    <?php } else if ($optInfo == 'WSDPRO') { ?>
        <a href="https://dashboard.websitedefender.com/" target="_blank">
        <img src="//www.websitedefender.com/images/plugins/banners/trial_users.jpg" title="" alt=""/></a>
    <?php } else { ?>
        <a href="http://www.websitedefender.com/websitedefender-features/" target="_blank">
        <img src="//www.websitedefender.com/images/plugins/banners/Free_users.jpg" title="" alt=""/></a>
    <?php } ?>
</div>



<?php
/*
 * SECTION #2
In this section we should list all the changes the plugin has done automatically to the
WordPress installation.

 */
wp_enqueue_style('wsdplugin_css_general',   wsdplugin_Utils::cssUrl('general.css'), array(), '1.0');

?>

<div class="wsdplugin_website_detail">

        <div style="margin-top: 30px; margin-bottom: 10px; width: 728px;">
            <p class="wsdplugin_special_text">
            <?php
                $wsdoptinfo = get_option('WSD-FIRST-INSTALL');
                if(empty($wsdoptinfo)){
                    // 1
                    add_option('WSD-FIRST-INSTALL', 1);
                    echo 'Hi. Thanks for downloading our WordPress security plugin! The plugin is now busy working away at improving
                    the security on your website, giving you the peace of mind to be getting on with other things.';
                }
                else {
                    // 2
                    echo 'WebsiteDefender is guarding your website, making sure it\'s secure so you can get on with other things.';
                }
            ?>
            </p>
        </div>

        <div class="wsdplugin_page_fixes" style="margin-top: 30px">
            <div class="wsdplugin_page_title">
                <h2>Keeping your website secure with these automatic plugin changes</h2>
            </div>

            <div class="wsdplugin_fixes_page" style="width: 620px; margin-top: 10px;">
                <table class="widefat">
                    <style type="text/css">
                        .wsdplugin_fixes_page .wsdplugin_status_indicator_ok
                        {
                            text-align: center;
                            vertical-align: middle;
                            width: 14px;
                            height: 17px;
                            background: transparent url('<?php echo wsdplugin_Utils::imgUrl('indicator-green.png');?>') no-repeat scroll center center;
                        }
                    </style>
                    <tr><td>Hidden the WordPress version</td><td class="wsdplugin_status_indicator_ok">&nbsp;</td></tr>
                    <tr><td>Hidden error messages</td><td class="wsdplugin_status_indicator_ok">&nbsp;</td></tr>
                    <tr><td>Switch off database error reporting</td><td class="wsdplugin_status_indicator_ok">&nbsp;</td></tr>
                    <tr><td>Removed WordPress generator meta tag</td><td class="wsdplugin_status_indicator_ok">&nbsp;</td></tr>
                    <tr><td>Removed Windows Live Writer meta tag</td><td class="wsdplugin_status_indicator_ok">&nbsp;</td></tr>
                    <tr><td>Removed Really Simple Discovery</td><td class="wsdplugin_status_indicator_ok">&nbsp;</td></tr>
                    <tr><td>Removed WordPress core update notifications for users</td><td class="wsdplugin_status_indicator_ok">&nbsp;</td></tr>
                    <tr><td>Removed plugins update notifications for users</td><td class="wsdplugin_status_indicator_ok">&nbsp;</td></tr>
                    <tr><td>Removed theme update notifications for users</td><td class="wsdplugin_status_indicator_ok">&nbsp;</td></tr>
                    <tr><td>Prevented WordPress wp-content directory listing</td><td class="wsdplugin_status_indicator_ok">&nbsp;</td></tr>
                </table>
            </div>
        </div>

        <?php if (get_option('WSD-SCANTYPE', '') == '') { ?>
        <div style="margin-top: 30px; margin-bottom: 10px;" class="wsdplugin_special_text">
            <p>
                The plugin also runs several security checks to help you secure your WordPress.
                Refer to the list of Plugin Security Alerts to improve the security of your WordPress.
                For more advanced security checks and daily malware scanning of your WordPress, navigate to the
                <a href="admin.php?page=wsdplugin_alerts">WebsiteDefender Security Alerts</a> and register for a 15 days trial
            </p>
        </div>
        <?php } ?>
    </div>



<?php
/*
 * SECTION #3
 * The above info bar should be removed from the WSD alerts node and should be presented differently in section 3 of the Dashboard node. The title for this section should be “Real Time Security Information” and should contain the following information:
•	Malware: Your website is infected with malware / Your website is clean.
•	Domain will expire in: Number of days until the domain will expire should be shown
•	Last Scan: should contain the time and date
•	Average Response time: Should be in ms
•	DNS: Up and running / DNS not responding
 */

?>
<div class="wsdplugin_page_title" style="margin-top: 30px">
    <h2>Real Time Security Information</h2>
</div>



<div class="wsdplugin_page_status wsdplugin_page_status_horizontal" style="margin-top:10px; width: 610px;">

    <div class="wsdplugin_status_box wsdplugin_status_malware">
        <span>Malware:</span>
        <span>-</span>
    </div>
    <br/>

    <div class="wsdplugin_status_box wsdplugin_status_domain">
        <span>Domain will expire in:</span>
        <span>-</span>
    </div>
    <br/>

    <div class="wsdplugin_status_box wsdplugin_status_dns">
        <span>DNS:</span>
        <span>-</span>
    </div>
    <br/>

    <div class="wsdplugin_status_box wsdplugin_status_scan">
        <span>Last Scan:</span>
        <span>-</span>
    </div>
    <br/>

    <div class="wsdplugin_status_box wsdplugin_status_time">
        <span>Average Response Time:</span>
        <span>-</span>
    </div>
</div>

    <script type="text/javascript">
        <?php echo 'WSDPLUGIN_JSRPC_URL = "', wsdplugin_JSRPC_URL, '";'; ?>
        jQuery(function($) {
            $('.wsdplugin_page_status').wsdplugin_status({
                targetId: <?php echo "'{$targetId}'";?>,
                email: <?php echo "'", str_replace('"', '\"', $user), "'";?>,
                hash: <?php echo "'", $hash, "'";?>});
        });
    </script>


    <?php
    /*
     * At the bottom we should also add the text “Why not go PRO?” which should link to the WSD dashboard.
     */
?>
<?php if($optInfo !== 'BAK') : ?>
<div style="margin-top: 40px;">
        <p style="overflow: hidden;"><a target="_blank" href="https://dashboard.websitedefender.com/" class="go-pro" title="Why not go PRO?"></a></p>
</div>
<?php endif; ?>


</div>
<!--<div style="float: none; clear: both"></div>-->