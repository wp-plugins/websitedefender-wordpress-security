<?php
/*
 * Displays info about the current instance's status
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

<div id="acx-wrapper" class="summary-page">

    <div id="acx-header">
        <h2><?php echo ACX_PLUGIN_NICE_NAME;?></h2>
    </div>

    <div id="acx-page-content">

        <p style="margin-left: 15px;">
            <?php echo __("This page displays various information after scanning your WordPress website"); ?>:
        </p>

        <div class="metabox-holder">
            <div style="width:49%; float: left;" class="postbox">
                <h3 class="hndle"><span><?php echo __('Wordpress Scan Report');?></span></h3>
                <div class="inside acx-section-box">
                    <?php
                        echo acxUtil::loadTemplate('box-scan-results-wp');
                    ?>
                </div>
            </div>
            <div style="width:49%; float: right;" class="postbox">
                <h3 class="hndle"><span><?php echo __('System Information Report');?></span></h3>
                <div class="inside acx-section-box">
                    <?php
                        echo acxUtil::loadTemplate('box-sys-info');
                    ?>
                </div>
            </div>
        </div>

        <div class="metabox-holder">
            <div style="width:99.8%;" class="inner-sidebar1 postbox">
                <h3 class="hndle"><span><?php echo __('File Scan Report');?></span></h3>
                <div class="inside">
                    <?php
                        echo acxUtil::loadTemplate('box-scan-results-file');
                    ?>
                </div>
            </div>
        </div>

    <?php
        // DISPLAY THE GLOSSARY
        echo acxUtil::loadTemplate('box-scan-reports-glossary');
    ?>

    </div><?php /*[ End #acx-page-content ]*/ ?>
</div><?php /*[ End #acx-wraper ]*/ ?>