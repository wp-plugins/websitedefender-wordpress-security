<?php
/*
 * Displays the Options page
 *
 * @package ACX
 * @since v0.5
 */
?>
<?php
    //@@ require a valid request
if (!defined('ACX_PLUGIN_NAME')) { exit; }
    //@@ Only load in the plug-in pages
if (!ACX_SHOULD_LOAD) { exit; }
?>
<?php

    if (function_exists('wp_create_nonce')){
        $wsdwpsopt_nonce = wp_create_nonce();
    }
    else {$wsdwpsopt_nonce = '';}


$_checked = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    if (function_exists('check_admin_referer')) {
        check_admin_referer('_wsdwps_opt_wpnonce');
        $_nonce = $_POST['_wsdwps_opt_wpnonce'];
        if (empty($_nonce) || ($_nonce <> $wsdwpsopt_nonce)){
            wp_die("Invalid request!");
        }
    }
    if($_POST['show_rss_widget'] == 'on'){
        update_option('WSD-RSS-WGT-DISPLAY', 'yes');
        $_checked = true;
    }
    else {
        update_option('WSD-RSS-WGT-DISPLAY', 'no');
        $_checked = false;
    }
}
//# 10/04/2011
$wsdRssWidgetVisible = get_option('WSD-RSS-WGT-DISPLAY');
if (empty($wsdRssWidgetVisible) || $wsdRssWidgetVisible=='yes') {
    add_option('WSD-RSS-WGT-DISPLAY', 'yes');
    $_checked = true;
}
else {
    if (strtolower($wsdRssWidgetVisible) == 'no') {
        $_checked = false;
    }
}
?>
<div class="acx-section-box">

    <form id="plugin_options_form" method="post">
	<?php if (function_exists('wp_nonce_field')) {
        echo '<input type="hidden" name="_wsdwps_opt_wpnonce" value="'.$wsdwpsopt_nonce.'" />';
        wp_nonce_field('_wsdwps_opt_wpnonce');
        }
        ?>
        <div>
            <input type="checkbox" name="show_rss_widget" id="show_rss_widget" <?php echo ($_checked ? 'checked="checked"' : '');?> />
            <label for="show_rss_widget"><?php echo __("Show the WebsiteDefender News dashboard widget");?></label>
        </div>

        <div>
            <p style="margin-top: 25px">
                <input type="submit" class="button-primary" value="<?php echo __('Update');?>"/>
            </p>
        </div>
    </form>

</div>
