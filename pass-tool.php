<?php
if (!defined('wsdplugin_WSD_PLUGIN_SESSION')) exit;

wp_enqueue_style('wsdplugin_css_general',   wsdplugin_Utils::cssUrl('general.css'), array(), '1.0');


function wsdplugin_make_password($password_length)
{
	list($usec, $sec) = explode(' ', microtime());
	$seed = (float)$sec + ((float)$usec * 100000);

	srand($seed);
	$alfa = "!@123!@4567!@890qwer!@tyuiopa@!sdfghjkl@!zxcvbn@!mQWERTYUIO@!PASDFGH@!JKLZXCVBNM!@";
	$token = "";
	for($i = 0; $i < $password_length; $i ++) {
		$token .= $alfa[rand(0, strlen($alfa)-1)];
	}
	return $token;
}

?>
<style type="text/css">
    p.indicator { height: 4px; width: 150px; }
	p.indicator-1 { background: #f00;}
    p.indicator-2 { background: #990000; }
    p.indicator-3 { background: #990099; }
    p.indicator-4 { background: #000099; }
    p.indicator-5 { background: #0000ff; }
    p.indicator-6 { background: #ffffff; }
</style>
<script type="text/javascript">
    function wsdplugin_testPassword(passwd){
        var description = new Array();
        description[0] = '<p class="indicator indicator-1"></p> <p><strong>Weakest</strong></p>';
        description[1] = '<p class="indicator indicator-2"></p> <p><strong>Weak</strong></p>';
        description[2] = '<p class="indicator indicator-3"></p> <p><strong>Improving</strong></p>';
        description[3] = '<p class="indicator indicator-4"></p> <p><strong>Strong</strong></p>';
        description[4] = '<p class="indicator indicator-5"></p> <p><strong>Strongest</strong></p>';
        description[5] = '<p class="indicator indicator-6"></p> <p><strong>Begin Typing</strong></p>';

        var base = 0
        var combos = 0
        if (passwd.match(/[a-z]/))base = (base+26);
        if (passwd.match(/[A-Z]/))base = (base+26);
        if (passwd.match(/\d+/))base = (base+10);
        if (passwd.match(/[>!"#$%&'()*+,-./:;<=>?@[\]^_`{|}~]/))base = (base+33);

        combos=Math.pow(base,passwd.length);

        if(combos == 1)strVerdict = description[5];
        else if(combos > 1 && combos < 1000000)strVerdict = description[0];
        else if (combos >= 1000000 && combos < 1000000000000)strVerdict = description[1];
        else if (combos >= 1000000000000 && combos < 1000000000000000000)strVerdict = description[2];
        else if (combos >= 1000000000000000000 && combos < 1000000000000000000000000)strVerdict = description[3];
        else strVerdict = description[4];

        document.getElementById("Words").innerHTML= (strVerdict);
    }
</script>

<div class="wrap wsdplugin_content">

    <div class="wsdplugin_page_title">
        <h2>Strong Password Generator</h2>
    </div>

    <div style="margin-top: 20px;">
        <span><?php echo __("Start typing a password and the Password Generator will indicate if it is a strong password or not.");?></span>
        <table style="margin-top: 10px">
            <tr valign="top">
                <td>
                    <form onsubmit="javascript:return false;">
						<?php echo __('Type password:');?> <input type="password" size="30" maxlength="50"
                                                                  onkeyup="wsdplugin_testPassword(this.value);"
                                                                  value="" />
                        <br />
                        <span style="color:#808080; margin-left: 92px"><?php echo __('6 characters minimum');?></span>
                    </form>
                </td>
                <td style="padding-left: 6px;">
                    <span><?php echo __('Password Strength:');?></span>

                    <div id="Words">
                        <p class="indicator"></p>
                        <p><strong><?php echo __('Begin Typing');?></strong></p>
                    </div>
                </td>
            </tr>
        </table>

        <br />
        <br />
        <hr class="line" size="2" color="#EBEBEB" />
        <br /><span><?php echo __('Example of a strong password:');?></span> <span
            style="color:#f00;"><?php echo wsdplugin_make_password(15);?></span>
    </div>
</div>