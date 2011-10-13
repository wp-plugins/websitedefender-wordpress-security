<?php
/*
 * Displays the Database Backup Tool
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

<div class="">
    <blockquote>
        <p><?php echo __('Your WordPress database contains every post, every comment and every link you have on your blog. If your database gets erased or corrupted, you stand to lose everything you have written. There are many reasons why this could happen and not all are things you can control. But what you can do is <strong>back up your data</strong>.'); ?></p>
        <p style="text-align: center;"><?php echo __('<strong>Please backup your database before using this tool!</strong>');?></p>
    </blockquote>
</div>

<?php
/*
 * Check if the backups directory is writable
 *======================================================
 */
$wsd_bckDirPath = ACX_PLUGIN_DIR.'backups/';
if (is_dir($wsd_bckDirPath) && is_writable($wsd_bckDirPath)) :
?>


<?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        if (isset($_POST['wsd_db_backup']))
        {

            if ('' <> ($fname = acx_backupDatabase())) {
                echo '<p class="acx-info-box">';
					echo '<span>',__('Database successfully backed up!'),'</span>';
					echo '<br/><span>',__('Download backup file'),': </span>';
					echo '<a href="',ACX_PLUGIN_PATH.'backups/',$fname,'" style="color:#000">',$fname,'</a>';
                echo '</p>';
            }
            else {
                echo '<p class="acx-info-box">';
					echo __('The database could not be backed up!');
					echo '<br/>',__("A posible error might be that you didn't set up writing permissions for the backups directory!");
                echo '</p>';
            }
        }
    }
?>
<div class="acx-section-box">
    <form action="#bckdb" method="post">
        <input type="hidden" name="wsd_db_backup"/>
        <input type="submit" class="button-primary" name="backupDatabaseButton" value="<?php echo __('Backup now!');?>"/>
    </form>
</div>

<?php else : //!! The directory is not writable. Display the info message

	echo '<p class="acx-info-box">';
		printf(__('<strong>Important</strong>: The <code title="%s">backups</code> directory <strong>MUST</strong> be writable in order to use this feature!')
            ,ACX_PLUGIN_DIR.'backups');
	echo '</p>';
endif; ?>
