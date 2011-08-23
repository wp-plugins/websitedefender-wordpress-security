<?php
/*
 * Displays the WSD target ID forms
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

<form action="" method="post" id="wsd_target_id_form" name="wsd_target_id_form">
	<label for="targetid"><?php echo __('Target ID');?>:</label>
	<input type="text" name="targetid" id="targetid"/>
	<input type="submit" name="wsd_update_target_id" value="<?php echo __('Update');?>" />
</form>
<script type="text/javascript">
    if (typeof(jQuery) !== 'undefined')
    {
        jQuery(document).ready(function($)
        {
            $('#wsd_target_id_form').submit(function()
            {
                var e = $('#targetid');
                if ($.trim(e.val()) == '') {
                    alert('Please insert the target id!');
                    e.focus();
                    return false;
                }
            });
        });
    }
</script>
<p>
    <?php
        echo __('Target ID can be found on WebsiteDefender 
            <a href="https://dashboard.websitedefender.com/" target="_blank">dashboard</a> under the <code>website status</code> section.');
    ?>
</p>