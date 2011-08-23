<?php
/*
 * Displays the WSD login form
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

<h4><?php echo __('Login here if you already have a WSD account');?>:</h4>
<form action="" method="post" id="sw_wsd_login_form" name="sw_wsd_login_form">
    <div>
        <div class="wsd-login-section">
            <label for="wsd_login_form_email"><?php echo __('Email');?>:</label>
            <input type="text" name="wsd_login_form_email" id="wsd_login_form_email" value="<?php echo get_option("admin_email"); ?>" />
        </div>
        <div class="wsd-login-section">
            <label for="wsd_login_form_password"><?php echo __('Password');?>:</label>
            <input type="password" name="wsd_login_form_password" id="wsd_login_form_password" />
        </div>
        <input type="submit" name="wsd-login" id="wsd-login" value="<?php echo __('Login');?>">
    </div>
</form>
