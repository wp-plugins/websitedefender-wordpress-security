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
$_checked = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
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
