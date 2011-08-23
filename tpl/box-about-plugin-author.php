<?php
/*
 * Displays info about the plug-in author
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

<p><?php echo __('A secure website, free from malware, where your customers can feel safe is vital to your online success.
    Unfortunately, the number of web hacking attacks has risen dramatically. Website security is an absolute must. 
    If you do not protect your website, hackers can gain access to your website, modify your web content, install malware 
    and have your site banned from Google. They could modify scripts and gain access to your customer data and their credit card details…');?></p>

<p><?php echo __('WebsiteDefender is an online service that monitors your website for hacker activity, audits the security 
    of your web site and gives you easy to understand solutions to keep your website safe. With WebsiteDefender you can:');?></p>

<ul class="acx-common-list">
    <li>Detect Malware present on your website</li>
    <li>Audit your web site for security issues</li>
    <li>Avoid getting blacklisted by Google</li>
    <li>Keep your web site content &amp; data safe</li>
    <li>Get alerted to suspicious hacker activity</li>
</ul>

<p><?php echo sprintf(__('All via an easy-to-understand web based dashboard which gives step by step solutions!
    Sign up for your FREE account <a href="admin.php?page=%s">here</a>.'), ACX_PREFIX);?></p>
