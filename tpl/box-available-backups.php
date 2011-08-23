<?php
/*
 * Displays the list of all available backup files
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
 * DISPLAY AVAILABLE DOWNLOADS
 *======================================================
 */
?>
<?php
$files = acx_getAvailableBackupFiles();

    if (empty($files))
    {
        echo '<p>',__("You don't have any backup files yet!"),'</p>';
    }
    else {
        echo '<div class="acx-section-box">';
            echo '<ul id="bck-list" class="acx-common-list">';
            foreach($files as $fileName) {
                echo '<li>';
                    echo '<a href="',ACX_PLUGIN_PATH.'backups/',$fileName,'" title="',__('Click to download'),'">',$fileName,'</a>';
                echo '</li>';
            }
            echo '</ul>';
        echo '</div>';
    }
?>