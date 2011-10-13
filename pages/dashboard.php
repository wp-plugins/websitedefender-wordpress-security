<?php
/*
 * Displays the plug-in's dashboard page
 * 
 * @package ACX
 * @since v0.1
 */
?>
<?php
    //@@ require a valid request
if (!defined('ACX_PLUGIN_NAME')) { exit; }
    //@@ Only load in the plug-in pages
if (!ACX_SHOULD_LOAD) { exit; }
?>
<div id="acx-wrapper" class="dashboard-page">
        
    <div id="acx-header">
        <h2><?php echo ACX_PLUGIN_NICE_NAME;?></h2>
    </div>
    
    <div id="acx-page-content">
        
        <div class="metabox-holder" style="float: left; width:49%;">
            <div class="inner-sidebar1 postbox" style="padding-bottom: 10px;">
                <h3 class="hndle"><span><?php echo __('WebsiteDefender');?></span></h3>
                <div class="inside" style="padding: 5px 5px;">
                    <?php
                        global $acxWsd;

                        echo $acxWsd->render_main();
                    ?>
                </div>
            </div>
        </div>

    
        <div class="metabox-holder" style="float: right; width:49%;">
            <div class="inner-sidebar1 postbox">
                <h3 class="hndle"><span><?php echo __('About WebsiteDefender');?></span></h3>
                <div class="inside">
                    <?php
                        echo acxUtil::loadTemplate('box-about-wsd');
                    ?>
                </div>
            </div>
        </div>
    
    </div><?php /*[ End #acx-page-content ]*/ ?>
</div><?php /*[ End #acx-wraper ]*/ ?>