<?php
/*
 * Displays info about the plug-in & author
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

<div id="acx-wrapper" class="about-page">
    <?php
        $acx_aboutPluginTitle  = sprintf(__('%s - About'),ACX_PLUGIN_NICE_NAME);
    ?>

    <div id="acx-header">
        <h2><?php echo $acx_aboutPluginTitle;?></h2>
    </div>

    <div id="acx-page-content">

        <div class="metabox-holder" style="float:left; width:49%;">

            <div style="" class="inner-sidebar1 postbox">
                <h3 class="hndle"><span><?php echo $acx_aboutPluginTitle;?></span></h3>
                <div class="inside acx-section-box">
                    <?php
                        echo acxUtil::loadTemplate('box-about-plugin');
                    ?>
                </div>
            </div>
        </div>

        <div class="metabox-holder" style="float:right;width:49%;">
            <div style="" class="inner-sidebar1 postbox">
                <h3 class="hndle"><span><?php echo __('Get involved!');?></span></h3>
                <div class="inside acx-section-box">
                    <?php
                        echo acxUtil::loadTemplate('box-get-involved');
                    ?>
                </div>
            </div>
        </div>

        <div style="clear:both"></div>

    </div><?php /*[ End #acx-page-content ]*/ ?>
</div><?php /*[ End #acx-wraper ]*/ ?>