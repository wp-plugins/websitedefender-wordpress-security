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
<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<div id="acx-wrapper" class="dashboard-page">

    <div id="acx-header">
        <h2><?php echo ACX_PLUGIN_NICE_NAME;?></h2>
    </div>

    <div id="acx-page-content">

    <p style="margin-left: 15px;">
        <?php echo __("This page displays various information after scanning your WordPress website"); ?>:
    </p>

    <div class="metabox-holder">

        <div style="width:99.8%;" class="postbox">
            <h3 class="hndle"><span><?php echo __('Wordpress Scan Report');?></span></h3>
            <div class="inside acx-section-box">
                <?php
                echo acxUtil::loadTemplate('box-scan-results-wp');
                ?>
            </div>
        </div>
        <div style="width:99.8%;" class="inner-sidebar1 postbox">
            <h3 class="hndle"><span><?php echo __('File Scan Report');?></span></h3>
            <div class="inside">
                <?php
                echo acxUtil::loadTemplate('box-scan-results-file');
                ?>
            </div>
        </div>
    </div>


    </div><?php /*[ End #acx-page-content ]*/ ?>
</div><?php /*[ End #acx-wraper ]*/ ?>