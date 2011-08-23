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

<ul class="acx-common-list">
    <li>
        <span><a href="http://www.websitedefender.com/forums/" target="_blank"><?php echo __('WebsiteDefender forums');?></a></span>
    </li>
    <li>
        <span><a href="http://www.websitedefender.com/blog/" target="_blank"><?php echo __('WebsiteDefender blog');?></a></span>
    </li>
    <li>
        <span><a href="http://twitter.com/#!/websitedefender" target="_blank"><?php echo __('WebsiteDefender on Twitter');?></a></span>
    </li>
    <li>
        <span><a href="http://www.facebook.com/WebsiteDefender" target="_blank"><?php echo __('WebsiteDefender on Facebook');?></a></span>
    </li>
</ul>
