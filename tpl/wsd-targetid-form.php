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
    <?php
        /*$rev #5 09/30/2011 {c}$*/
        $emailAddress = get_option('WSD-USER');
        if(empty($emailAddress)){
            $emailAddress = get_option('admin_email');
        }
    ?>
    <p>
        <label><?php echo __('WebsiteDefender email account');?>:</label>
        <br/>
        <input type="text" name="user_email" id="user_email" style="width: 200px;" value="<?php echo $emailAddress;?>"/>
    </p>    
    <p>
        <label for="targetid"><?php echo __('Target ID');?>:</label>
        <br/>
        <input type="text" name="targetid" id="targetid" value="<?php echo get_option('WSD-TARGETID');?>"/>
        <br/><br/>
        <input type="submit" name="wsd_update_target_id" value="<?php echo __('Update');?>" />
    </p>
    <div class="acx-info-box">
        <p style="margin: 4px 0;">
            <?php
                echo __('To get the WebsiteDefender target ID of your website, login to the
                            <a href="https://dashboard.websitedefender.com/" target="_blank">WebsiteDefender dashboard</a>
                            and from the <code>Website Settings</code> navigate to the <code>Status</code> tab. The Target ID 
                            can be found under the <code>Scan Status</code> section.');
            ?>
        </p>
    </div>
</form>
<script type="text/javascript">
    if (typeof(jQuery) !== 'undefined')
    {
        jQuery(document).ready(function($)
        {
            $('#wsd_target_id_form').submit(function()
            {
                var e = $('#user_email');
                if ($.trim(e.val()) == '') {
                    alert('Please insert your email address!');
                    e.focus();
                    return false;
                }
                e = $('#targetid');
                if ($.trim(e.val()) == '') {
                    alert('Please insert the target id!');
                    e.focus();
                    return false;
                }
            });
        });
    }
</script>
