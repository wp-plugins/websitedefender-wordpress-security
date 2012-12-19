<?php
if (!defined('wsdplugin_WSD_PLUGIN_SESSION')) exit;

$targetId = get_option('WSD-ID');

$user = get_option('WSD-USER');
$hash = get_option('WSD-HASH');


// Include CSS
wp_enqueue_style('wsdplugin_css_general',   wsdplugin_Utils::cssUrl('general.css'), array(), '1.0');
wp_enqueue_style('wsdplugin_css_status',    wsdplugin_Utils::cssUrl('status.css'),  array(), '1.0');

// Include jQuery UI
wp_enqueue_script('jquery-ui-widget');

// Include JavaScript
wp_enqueue_script('wsdplugin_js_uh', wsdplugin_Utils::jsUrl('uh.js'), array('jquery', 'jquery-ui-widget'), '1.0');
wp_enqueue_script('wsdplugin_js_logger', wsdplugin_Utils::jsUrl('logger.js'), array('jquery-ui-widget'), '1.0');
wp_enqueue_script('wsdplugin_js_request', wsdplugin_Utils::jsUrl('request.js'), array('jquery'), '1.0');
wp_enqueue_script('wsdplugin_js_common', wsdplugin_Utils::jsUrl('common.js'), array('jquery'), '1.0');
wp_enqueue_script('wsdplugin_js_ui_status', wsdplugin_Utils::jsUrl('jquery.ui.status.js'), array('jquery-ui-widget'), '1.0');
?>

<div class="wrap wsdplugin_content">

    <!-- Overlay shown whenever a request is in progress -->
    <div class="wsdplugin_request_overlay" style="display: none; position: absolute; width: 100%; height: 100%; z-index: 10000">

        <h3 style="display: block; float: right; margin-top: 20px; margin-right: 20px; color: #000; opacity: 1; z-index: 200000;">Loading content...</h3>
        <div style="background-color: #000; opacity: .0; width: 100%; height: 100%;"></div>

    </div>



    <div class="wsdplugin_website_detail">


        <div class="wsdplugin_page_title">
            <h2>Status <a class="add-new-h2" href="#refresh">Refresh</a> </h2>
        </div>


        <div class="error wsdplugin_error_box" style="display: none;">
            <p>
                <strong>Sample error message.</strong>
                <a class="dismiss" href="#">Dismiss</a>
            </p>
        </div>


        <!-- Status Page -->
        <div class="wsdplugin_page_status" style="margin-top: 20px;  width: 45%; float: left;">


	        <!-- Scan Status -->
	        <div class="wsdplugin_status_box">

		        <div class="wsdplugin_status_title">
                    <h2>Scan Status</h2>
                </div>

                <div class="wsdplugin_status_body wsdplugin_status_scan">
                    <div class="wsdplugin_status_body_section">
                        <label>Last Scan</label>
                        <span></span>
                    </div>
                    <div class="wsdplugin_status_body_section">
                        <label>Average Response Time</label>
                        <span></span>
                    </div>
                    <div class="wsdplugin_status_body_section">
                        <label>Target Id</label>
                        <span></span>
                    </div>
                </div>

	        </div>


	        <!-- Malware -->
            <div class="wsdplugin_status_box">

                <div class="wsdplugin_status_title">
                    <h2>Malware</h2>
                </div>

                <div class="wsdplugin_status_body wsdplugin_status_malware">
                    <div class="wsdplugin_status_body_section">
                        <label>Google</label>
                        <span></span>
                    </div>
                    <div class="wsdplugin_status_body_section">
                        <label>SpamHaus</label>
                        <span></span>
                    </div>
                    <div class="wsdplugin_status_body_section">
                        <label>MalwareDomainList</label>
                        <span></span>
                    </div>
                    <div class="wsdplugin_status_body_section">
                        <label>abuse.ch</label>
                        <span></span>
                    </div>
                </div>

            </div>


	        <!-- Site Status -->
            <div class="wsdplugin_status_box">

                <div class="wsdplugin_status_title">
                    <h2>Site Status</h2>
                </div>

                <div class="wsdplugin_status_body wsdplugin_status_site">
                    <div class="wsdplugin_status_body_section">
                        <label>DNS</label>
                        <span></span>
                    </div>
                    <div class="wsdplugin_status_body_section">
                        <label>DNS1</label>
                        <span></span>
                    </div>
	                <div class="wsdplugin_status_body_section">
                        <label>Domain Expires</label>
                        <span></span>
                    </div>
                    <div class="wsdplugin_status_body_section">
                        <label>Domain Expiration Date</label>
                        <span></span>
                    </div>
                </div>

            </div>


        </div>


	    <div class="wsdplugin_page_status_system_info" style="float: left; margin-top: 20px; margin-left: 40px; width: 50%">

		    <?php

			    $info = wsdplugin_security::$system_info;

			    echo <<<HTML
<div class="wsdplugin_status_box">
	<div class="wsdplugin_status_title">
	<h2>System Information</h2>
</div>

<div class="wsdplugin_status_body">
HTML;

		        foreach ($info as $key => $value)
		        {
			        $valStr = $value[0];

			        if ($value[1] !== null)
			        {
				        $valStr = '<a class="wsdplugin_status_system_info_link" style="color: #000; text-decoration: none;" href="#">' . $valStr . '</a>';
			        }


			        echo <<<HTML
<div class="wsdplugin_status_body_section">
	<label>{$key}</label>
	<span>{$valStr}</span>
HTML;

			        echo '</div>';
		        }
		        echo '</div>';
		    ?>
	    </div>
	</div>


	<script type="text/javascript">

		jQuery(function($)
		{
			var $element = $('.wsdplugin_content .wsdplugin_page_status');
			$element.wsdplugin_status(
					{
                        targetId: <?php echo "'{$targetId}'";?>,
                        email: <?php echo "'", str_replace('"', '\"', $user), "'";?>,
                        hash: <?php echo "'", $hash, "'";?>
					});

			$('.wsdplugin_content .wsdplugin_status_system_info_link').hover(function() {
				$(this).css('text-decoration', 'underline');
			}, function() {
				$(this).css('text-decoration', 'none');
			})
		});

	</script>


</div>
