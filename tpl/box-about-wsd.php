<?php
/*
 * Displays info about WSD
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
<div class="acx-section-box">

    <p class="wsd-inside" style="margin-top: 0px;">
    <?php echo __('WebsiteDefender.com is built on web application scanning technology from <a href="http://www.acunetix.com/" target="_blank">Acunetix</a>, a pioneer in website security. 
        <a href="http://www.websitedefender.com" target="_blank">WebsiteDefender</a> requires no installation, no learning curve and no maintenance. Above all, there is no impact on site performance!
        WebsiteDefender regularly scans and monitors your WordPress website/blog effortlessly, efficiently, easily and is available for Free! Start scanning your WordPress website/blog against malware and hackers, absolutely free!'); ?>
    </p>

    <p class="wsd-inside">
        <?php /*echo __('WebsiteDefender is an online service that protects your website from any hacker activity by monitoring and auditing
         the security of your website, giving you easy to understand solutions to keep your website safe, always! WebsiteDefender\'s enhanced
         WordPress Security Checks allow it to optimise any threats on a blog or site powered by WordPress.');*/ ?>
    </p>
    
    <p class="wsd-inside">
        <?php echo '<strong>'.__('With WebsiteDefender you can:').'</strong>'; ?>
    </p>
    
    <ul class="acx-common-list">
        <li><span class="acx-icon-alert-success"><?php echo __('Detect malware present on your website');?></span></li>
        <li><span class="acx-icon-alert-success"><?php echo __('Audit your website for security issues');?></span></li>
        <li><span class="acx-icon-alert-success"><?php echo __('Avoid getting blacklisted by Google');?></span></li>
        <li><span class="acx-icon-alert-success"><?php echo __('Keep your website content and data safe');?></span></li>
        <li><span class="acx-icon-alert-success"><?php echo __('Get alerted to suspicious hacker activity');?></span></li>
    </ul>
    <p class="wsd-inside">
        <?php echo __('WebsiteDefender.com does all this and more via an easy-to-understand web-based <a href="https://dashboard.websitedefender.com/" target="_blank">dashboard</a>, which gives step by step solutions
         on how to make sure your website stays secure!');?>
    </p>

    <p style="text-align:center; margin: 0 0 0 0;" class="acx-info-box acx-notice-success acx-info-box-noicon">
        <?php
            echo '<span class="acx-icon-alert-info">';
                echo ACX_PLUGIN_NICE_NAME.' '.__('plugin must remain active for security features to persist!');
            echo '</span>';
        ?>
    </p>

</div>