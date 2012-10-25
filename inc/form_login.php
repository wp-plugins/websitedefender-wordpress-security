<?php
function wsdplugin_render_login_form($userName, $message, $errorMessage)
{
?>

<style type="text/css">
	#wsdplugin_login_form .form-field input { width: 25em; }
</style>

<div class="wsdplugin_content" style="float: left; margin-left: 10px;">
	<div style="float: left;">
	    <h2>Login</h2>

		<?php if ($errorMessage !== '') { ?>
	    <div class="error wsdplugin_error_box" style="margin-left: 0;">
	        <p>
	            <strong><?php echo wptexturize($errorMessage);?></strong>
	        </p>
	    </div>
		<?php } else if ($message !== '') { ?>
        <div class="error wsdplugin_info_box" style="margin-left: 0;">
            <p>
                <strong><?php echo wptexturize($message);?></strong>
            </p>
        </div>
	    <?php } ?>

	    <form id="wsdplugin_login_form" name="wsdplugin_login_form" method="post" enctype="application/x-www-form-urlencoded" accept-charset="utf-8">
	        <input type="hidden" name="type" value="login"/>
	        <table class="form-table">
		        <tbody>
		            <tr class="form-field form-required">
		                <th scope="row"><label for="wsdplugin_login_username">Email <span class="description">(required)</span></label></th>
		                <td><input type="text" aria-required="true" id="wsdplugin_login_username" name="wsdplugin_login_username" value="<?php echo htmlspecialchars($userName, ENT_QUOTES);?>"></td>
		            </tr>

		            <tr class="form-field form-required">
		                <th scope="row"><label for="wsdplugin_login_password">Password <span class="description">(required)</span></label></th>
		                <td><input type="password" autocomplete="off" id="wsdplugin_login_password" name="wsdplugin_login_password"></td>
		            </tr>
                </tbody>
	        </table>
	        <p class="submit"><input type="submit" value="Login" class="button-primary"></p>
            <?php
                global $wsdplugin_nonce;
                echo '<input type="hidden" name="wsdplugin_nonce_form" value="'.$wsdplugin_nonce.'" />';
                wp_nonce_field('wsdplugin_nonce');
            ?>
	    </form>


        <script type="text/javascript">

            jQuery(function($)
            {
                $('#wsdplugin_login_form').submit(function()
                {
                    var hasErrors = false;

                    // Validate email
                    var $email = $('#wsdplugin_login_username');
                    if ($email.val() == '') {
                        $email.closest('.form-field').addClass('form-invalid');
                        hasErrors = true;
                    }
                    else {
                        $email.closest('.form-field').removeClass('form-invalid');
                    }

                    // Validate passwords
                    var $passField = $('#wsdplugin_login_password');

                    if ($passField.val() == '')
                    {
                        $passField.closest('.form-field').addClass('form-invalid');
	                    hasErrors = true;
                    }

                    return (hasErrors === false);
                });
            });

        </script>

	</div>
</div>
<?php
}
?>
