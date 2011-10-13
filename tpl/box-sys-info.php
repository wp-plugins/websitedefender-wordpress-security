<?php
/*
 * Displays the System Information box
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
<ul class="acx-common-list">
    <?php echo acxUtil::getSystemInfoScanReport(); ?>
</ul>