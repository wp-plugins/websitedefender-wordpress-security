<?php
/*
 * Displays info about the plug-in
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

<p>
    <?php echo __('WSD Security plug-in beefs up the security of your WordPress installation by removing error information on login pages, adds index.php to plugin directories, hides the WordPress version and much more.'); ?>
</p>
<div class="acx-section-box">
<ul class="acx-common-list">
    <li><span class="acx-icon-alert-success"><?php echo __('Removes error-information on login-page.');?></span></li>
    <li><span class="acx-icon-alert-success"><?php echo __('Adds index.php to the wp-content, wp-content/plugins, wp-content/themes and wp-content/uploads directories to prevent directory listings.');?></span></li>
    <li><span class="acx-icon-alert-success"><?php echo __('Removes the wp-version, except in admin-area.');?></span></li>
    <li><span class="acx-icon-alert-success"><?php echo __('Removes Really Simple Discovery meta tag.');?></span></li>
    <li><span class="acx-icon-alert-success"><?php echo __('Removes Windows Live Writer meta tag.');?></span></li>
    <li><span class="acx-icon-alert-success"><?php echo __('Removes core update information for non-admins.');?></span></li>
    <li><span class="acx-icon-alert-success"><?php echo __('Removes plugin-update information for non-admins.');?></span></li>
    <li><span class="acx-icon-alert-success"><?php echo __('Removes theme-update information for non-admins (only WP 2.8 and higher).');?></span></li>
    <li><span class="acx-icon-alert-success"><?php echo __('Hides wp-version in backend-dashboard for non-admins.');?></span></li>
    <li><span class="acx-icon-alert-success"><?php echo __('Removes version on URLs from scripts and stylesheets only on frontend.');?></span></li>
    <li><span class="acx-icon-alert-success"><?php echo __('Provides various information after scanning your Wordpress blog.');?></span></li>
    <li><span class="acx-icon-alert-success"><?php echo __('Provides file permissions security checks.');?></span></li>
    <li><span class="acx-icon-alert-success"><?php echo __('Provides a tool for changing the database prefix.');?></span></li>
    <li><span class="acx-icon-alert-success"><?php echo __('Turns off database error reporting (if enabled).');?></span></li>
    <li><span class="acx-icon-alert-success"><?php echo __('Turns off PHP error reporting.');?></span></li>
</ul>
</div>