<?php
/*
 * Displays the WP scan results info
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
        <p><?php echo acxUtil::getCurrentVersionInfo();?></p>
    </li>
    <li>
        <p><?php echo acxUtil::getDatabasePrefixInfo();?></p>
    </li>
    <li>
        <p><?php echo acxUtil::getWpVersionStatusInfo();?></p>
    </li>
    <li>
        <p><?php echo acxUtil::getDbErrorStatusInfo();?></p>
    </li>
    <li>
        <p><?php echo acxUtil::getPhpErrorStatusInfo();?></p>
    </li>
    <li>
        <p><?php echo acxUtil::getPhpStartupErrorStatusInfo();?></p>
    </li>
    <li>
        <p><?php echo acxUtil::getAdminUsernameInfo();?></p>
    </li>
    <li>
        <p><?php echo acxUtil::getWpAdminHtaccessInfo();?></p>
    </li>
    <li>
        <p><?php echo acxUtil::getDatabaseUserAccessRightsInfo();?></p>
    </li>
    <li>
        <p><?php echo acxUtil::getWpContentIndexInfo();?></p>
    </li>
    <li>
        <p><?php echo acxUtil::getWpContentPluginsIndexInfo();?></p>
    </li>
    <li>
        <p><?php echo acxUtil::getWpContentThemesIndexInfo();?></p>
    </li>
    <li>
        <p><?php echo acxUtil::getWpContentUploadsIndexInfo();?></p>
    </li>
    <li>
        <p><?php echo acxUtil::getWpReadmeFileInfo();?></p>
    </li>

</ul>
