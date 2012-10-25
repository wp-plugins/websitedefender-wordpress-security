<?php

function wsdplugin_render_newuser_form($userName, $name, $surname, $message, $errorMessage)
{
	wp_enqueue_script('wsdplugin_js_common', wsdplugin_Utils::jsUrl('common.js'), array(), '1.0');
?>

<style>
	#wsdplugin_newuser_form .form-field input { width: 25em; }
</style>

<div style="margin: 30px 15px 20px 15px; padding: 0 0.6em; font-weight: bold;">
	<p style="margin: 0.5em 0;padding: 2px;">Do you want to have the best WordPress security? Of course you do! Simply fill in your details below
		so that you can start your 15 day WebsiteDefender trial. Join the community of thousands of WordPress users that
		are already enjoying the added benefits of daily security scans and malware checks!</p>
</div>

<div class="wsdplugin_content" style="float: left; width: 550px;">

    <div class="icon32" id="icon-users"><br></div>
	<h2>Register</h2>

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

<form id="wsdplugin_newuser_form" name="wsdplugin_newuser_form" method="post" enctype="application/x-www-form-urlencoded" accept-charset="utf-8">
    <input type="hidden" name="type" value="new"/>
	<table class="form-table">
        <tbody>
	        <tr class="form-field form-required">
	            <th scope="row"><label for="wsdplugin_newuser_username">Email <span class="description">(required)</span></label></th>
	            <td><input type="text" aria-required="true" id="wsdplugin_newuser_username" name="wsdplugin_newuser_username" value="<?php echo htmlspecialchars($userName, ENT_QUOTES);?>"></td>
	        </tr>

	        <tr class="form-field form-required">
	            <th scope="row"><label for="wsdplugin_newuser_name">First Name <span class="description">(required)</span></label></th>
	            <td><input type="text" id="wsdplugin_newuser_name" name="wsdplugin_newuser_name" value="<?php echo htmlspecialchars($name, ENT_QUOTES);?>"></td>
	        </tr>

	        <tr class="form-field form-required">
	            <th scope="row"><label for="wsdplugin_newuser_surname">Last Name <span class="description">(required)</span></label></th>
	            <td><input type="text" id="wsdplugin_newuser_surname" name="wsdplugin_newuser_surname" value="<?php echo htmlspecialchars($surname, ENT_QUOTES);?>"></td>
	        </tr>

	        <tr class="form-field form-required">
	            <th scope="row"><label for="wsdplugin_newuser_password1">Password <span class="description">(twice, required)</span></label></th>
	            <td><input type="password" autocomplete="off" id="wsdplugin_newuser_password1" name="wsdplugin_newuser_password1">
	                <br>
	                <input type="password" autocomplete="off" id="wsdplugin_newuser_password2" name="wsdplugin_newuser_password2">
	                <br>
	                <div id="pass-strength-result" style="display: block;">Strength indicator</div>
	                <p class="clear description indicator-hint">Weak passwords are one of the biggest reasons hackers are successful. Make your password at least seven characters long and throw in some upper and lower-case letters and some of these characters ! " ? $ % ^ & ).</p>
	            </td>
	        </tr>
        </tbody>
	</table>

    <p class="submit"><input type="submit" value="Register" class="button-primary"></p>
    <?php
    global $wsdplugin_nonce;
    echo '<input type="hidden" name="wsdplugin_nonce_form" value="'.$wsdplugin_nonce.'" />';
    wp_nonce_field('wsdplugin_nonce');
    ?>
</form>


<script type="text/javascript">

	jQuery(function($)
	{
		$('#wsdplugin_newuser_form').submit(function()
		{
			var hasErrors = false;

			// Validate email
			var $email = $('#wsdplugin_newuser_username');
			if ($email.val() == '') {
				$email.closest('.form-field').addClass('form-invalid');
				hasErrors = true;
			}
			else {
                $email.closest('.form-field').removeClass('form-invalid');
			}

			// Validate first name
			var $firstNameField = $('#wsdplugin_newuser_name');
			if ($firstNameField.val() == '') {
				$firstNameField.closest('.form-field').addClass('form-invalid');
                hasErrors = true;
			}
			else {
                $firstNameField.closest('.form-field').removeClass('form-invalid');
			}

			// Validate last name
            var $lastNameField = $('#wsdplugin_newuser_surname');
            if ($lastNameField.val() == '') {
                $lastNameField.closest('.form-field').addClass('form-invalid');
                hasErrors = true;
            }
            else {
                $lastNameField.closest('.form-field').removeClass('form-invalid');
            }

			// Validate passwords
			var $pass1Field = $('#wsdplugin_newuser_password1');
            var $pass2Field = $('#wsdplugin_newuser_password2');

			if ($pass1Field.val() == '' || $pass1Field.val() !== $pass2Field.val())
			{
				$pass1Field.closest('.form-field').addClass('form-invalid');
                hasErrors = true;
			}
			else {
                $pass1Field.closest('.form-field').removeClass('form-invalid');
			}

			return (hasErrors === false);
		});

		// Password strength
        jQuery("#wsdplugin_newuser_password1").bind("keyup", function(){
            var pass1 = $("#wsdplugin_newuser_password1").val();
            var pass2 = $("#wsdplugin_newuser_password2").val();
            var username = $("#wsdplugin_newuser_name").val();
            wsdplugin_updatePassStrength(pass1, username, pass2);
        });
        jQuery("#wsdplugin_newuser_password2").bind("keyup", function(){
            var pass1 = $("#wsdplugin_newuser_password1").val();
            var pass2 = $("#wsdplugin_newuser_password2").val();
            var username = $("#wsdplugin_newuser_name").val();
            wsdplugin_updatePassStrength(pass1, username, pass2);
        });
	});

</script>
</div>

<?php
}
?>
