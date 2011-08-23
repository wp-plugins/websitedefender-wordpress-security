<?php
/*
 * Displays the WSD register form
 *
 * @requires array data: $acxWsd, $error, $recaptcha_publickey
 * @uses wsd_recaptcha_get_html()
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

<h4><?php echo __('Sign up for your FREE account here to use all the WebsiteDefender.com advanced features');?>:</h4>

	<?php if($error !== '') {$acxWsd->render_error($error);} ?>

<form action="#em" method="post" id="sw_wsd_new_user_form" name="sw_wsd_new_user_form">
    <div id="em" class="wsd-new-user-section">
        <label for="wsd_new_user_email"><?php echo __('Email');?>:</label>
        <input type="text" name="wsd_new_user_email" id="wsd_new_user_email" value="<?php echo get_option("admin_email"); ?>" />
    </div>
    <div class="wsd-new-user-section">
        <label for="wsd_new_user_name"><?php echo __('Name');?>:</label>
        <input type="text" name="wsd_new_user_name" id="wsd_new_user_name" value="<?php echo isset($_POST['wsd_new_user_name']) ? $_POST['wsd_new_user_name'] : '' ?>" />
    </div>
    <div class="wsd-new-user-section">
        <label for="wsd_new_user_surname"><?php echo __('Surname');?>:</label>
        <input type="text" name="wsd_new_user_surname" id="wsd_new_user_surname" value="<?php echo isset($_POST['wsd_new_user_surname']) ? $_POST['wsd_new_user_surname']: '' ?>" />
    </div>
    <div class="wsd-new-user-section">
        <label for="wsd_new_user_password"><?php echo __('Password');?>:</label>
        <input type="password" name="wsd_new_user_password" id="wsd_new_user_password"/>
        <label class="password-meter" style="background-color: rgb(238, 0, 0); display: none;"><?php echo __('Too Short');?></label>
    </div>
    <div class="wsd-new-user-section">
        <label for="wsd_new_user_password_re"><?php echo __('Retype Password');?>:</label>
        <input type="password" name="wsd_new_user_password_re" id="wsd_new_user_password_re"/>
    </div>
    <div class="wsd-new-user-section">
		<?php
	        echo wsd_recaptcha_get_html($recaptcha_publickey, null, true);
        ?>
    </div>
    <input type="submit" name="wsd-new-user" id="wsd-new-user" value="<?php echo __('Register');?>">
</form>
