<?php
/*
 * Displays the Password Strength Tool
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

<script src="<?php echo ACX_PLUGIN_PATH.'res/js/acx-pwd-tool.js';?>" type="text/javascript"></script>

<div class="acx-section-box">
<span><?php echo __("Start typing a password and the Password Generator will indicate if it is a strong password or not.");?></span>
<table id="wsd_pwdtool">
        <tr valign="top">
            <td>
                <form name="commandForm">
                    Type password: <input type="password" size="30" maxlength="50" name="password" onkeyup="testPassword(this.value);" value="" />
                    <br/>
                    <span style="color:#808080">6 characters minimum</span>
                </form>
            </td>
            <td style="padding-left: 6px;">
                <span>Password Strength:</span>
                <div id="Words">
                    <p class="indicator"></p>
                    <p><strong>Begin Typing</strong></p>
                </div>
            </td>
        </tr>
    </table>

    <br/>
    <br/><hr class="line" size="2" color="#EBEBEB" />
    <br/><span>Example of a strong password:</span> <span style="color:#f00;"><?php echo make_password(15);?></span>
</div>
