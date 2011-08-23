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

<div id="acx-wrapper" class="database-page">

    <div id="acx-header">
        <h2><?php echo ACX_PLUGIN_NICE_NAME;?></h2>
    </div>
    
    <div id="acx-page-content">
        
        <?php /*[ DATABASE BACKUP ]*/ ?>
        <div class="metabox-holder">

            <?php
            /*
             * DATABASE BACKUP TOOL
             * ================================================================
             */
            ?>
            <div id="bckdb" style="float:left; width:49%;" class="inner-sidebar1 postbox">
                <h3 class="hndle"><span><?php echo __('Backup Database');?></span></h3>
                <div class="inside">
                    <?php
                        echo acxUtil::loadTemplate('box-database-backup');
                    ?>
                </div>
            </div>

            <?php /*[ DATABASE BACKUPS ]*/ ?>
            <div style="float:right;width:49%;" class="inner-sidebar1 postbox">
                <h3 class="hndle"><span><?php echo __('Database Backup Files');?></span></h3>
                <div class="inside">
                    <?php
                        echo acxUtil::loadTemplate('box-available-backups');
                    ?>
                </div>
            </div>
        </div>
        

        <div class="metabox-holder" style="width:99.8%; padding-top: 0;">
            <?php
            /*
             * CHANGE DATABASE PREFIX TOOL
             * ================================================================
             */
            ?>
            <div id="cdtp" class="postbox">
                <h3 class="hndle"><span><?php echo __('Change Database Prefix');?></span></h3>
                <div class="inside">
                    <?php
                        echo acxUtil::loadTemplate('box-database-change-prefix');
                    ?>
                </div>
            </div>
        </div>
        
        
    </div><?php /*[ End #acx-page-content ]*/ ?>
</div><?php /*[ End #acx-wraper ]*/ ?>