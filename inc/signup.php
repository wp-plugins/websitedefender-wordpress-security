<?php

function wsdplugin_render_signup_form($type = 'register', $userName = '', $name = '', $surname = '', $message = '', $errorMessage = '')
{
	wp_enqueue_script('wsdplugin_js_common', wsdplugin_Utils::jsUrl('common.js'), array(), '1.0');

	$expiration = get_option('WSD-EXPIRATION');

	if ($expiration == -1)
		$expiration = 'expired';
	else if ($expiration !== false)
		$expiration = (int)floor($expiration / 60.0 / 60.0 / 24.0);

	$optInfo = get_option('WSD-SCANTYPE');
	if ($optInfo === 'BAK' && get_option('WSD-EXPIRATION') == -1)
		$optInfo = 'WSDFREE';
?>
<style type="text/css">#wsdplugin_newuser_form .form-field input, #wsdplugin_login_form .form-field input { width: 25em; }</style>



<div class="wsdplugin_content" style="margin: 20px; width: 550px;">

<div id="wrap wsdplugin_advert">
	<?php if(empty($optInfo)){ ?>
	<a href="<?php echo wsdplugin_Handler::site_url().'wp-admin/admin.php?page=wsdplugin_alerts';?>">
		<img src="<?php echo wsdplugin_PLUGIN_PATH ;?>img/banners/free.jpg" title="" alt=""/></a>
	<?php } elseif($optInfo == 'BAK') { ?>
	<img src="<?php echo wsdplugin_PLUGIN_PATH ;?>img/banners/pro.jpg" title="" alt=""/>
	<?php } else if ($optInfo == 'WSDPRO') { ?>
	<a href="https://dashboard.websitedefender.com/" target="_blank">
		<img src="<?php echo wsdplugin_PLUGIN_PATH ;?>img/banners/trial-<?php echo $expiration; ?>-days.jpg" title="" alt=""/></a>
	<?php } else { ?>
	<a href="http://www.websitedefender.com/websitedefender-features/" target="_blank">
		<img src="<?php echo wsdplugin_PLUGIN_PATH ;?>img/banners/free.jpg" title="" alt=""/></a>
	<?php } ?>
</div>


<div class="wsdplugin_signup_links" style="clear: both; margin: 20px 0 30px 0">
	<a href="#action-register">Register</a> |
	<a href="#action-login">Login</a>
</div>

	<div class="icon32" id="icon-users"><br></div>
	<h2 id="wsdplugin_signup_title" style="padding-top: 13px;">Login</h2>

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

		<form style="display:none;" id="wsdplugin_login_form" name="wsdplugin_login_form" method="post" enctype="application/x-www-form-urlencoded" accept-charset="utf-8">
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
				<tr class="form-field form-required" style="display: none;">
					<th scope="row"></th>
					<td style="padding-left: 5px;" class="wsdplugin_recaptcha-container"></td>
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


		<form style="display:none;" id="wsdplugin_newuser_form" name="wsdplugin_newuser_form" method="post" enctype="application/x-www-form-urlencoded" accept-charset="utf-8">
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

			<tr class="form-field form-required">
				<th scope="row"></th>
				<td style="padding-left: 5px;" class="wsdplugin_recaptcha-container">
					<?php
					require_once 'recaptchalib.php';
					echo wsdplugin_recaptcha_get_html(RECAPTCHA_PUBLIC_KEY, null, true);
					?>
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


	<!-- Register -->
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

				// Validate captcha
				var $responseField = $('#recaptcha_response_field');
				if ($responseField.val() == '')
				{
					$responseField.closest('.form-field').addClass('form-invalid');
				}
				else
				{
					$responseField.closest('.form-field').removeClass('form-invalid');
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


	<!-- Login -->
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
				else
				{
					$passField.closest('.form-field').removeClass('form-invalid');
				}


				// Validate captcha
				var $responseField = $('#recaptcha_response_field');
				if ($responseField.val() == '')
				{
					$responseField.closest('.form-field').addClass('form-invalid');
					hasErrors = true;
				}
				else
				{
					$responseField.closest('.form-field').removeClass('form-invalid');
				}

				return (hasErrors === false);
			});
		});

	</script>

</div>





<script type="text/javascript">

	jQuery(function ($) {
		$('a[href="#action-login"]').click(function () {
			switchView('login');
			return false;
		});
		$('a[href="#action-register"]').click(function () {
			switchView('register');
			return false;
		});

		function switchView(type, first)
		{
			// Reset form
			if (first !== true)
			{
				$('.wsdplugin_content .wsdplugin_error_box').html('').hide();
				$('.wsdplugin_content .wsdplugin_info_box').html('').hide();
			}

			if (type == 'register') {
				$('#wsdplugin_signup_title').text('Register');
				$('#wsdplugin_login_form').hide();
				$('#wsdplugin_newuser_form').show();
				$('#wsdplugin_newuser_form .form-field').removeClass('form-invalid');
				$('#wsdplugin_newuser_username,#wsdplugin_newuser_name,#wsdplugin_newuser_surname,#wsdplugin_newuser_password1,#wsdplugin_newuser_password2').val('');
				$('.wsdplugin_content .wsdplugin_signup_title').text('Register');

				if ( $('#recaptcha_widget_div').closest('form').attr('id') !== 'wsdplugin_newuser_form' )
				{
					$('#wsdplugin_login_form .wsdplugin_recaptcha-container').closest('.form-field').hide();
					$('#wsdplugin_newuser_form .wsdplugin_recaptcha-container').append( $('#recaptcha_widget_div').remove()).closest('.form-field').show();
				}
			}
			else {
				$('#wsdplugin_signup_title').text('Login');
				$('#wsdplugin_login_form').show();
				$('#wsdplugin_newuser_form').hide();
				$('#wsdplugin_login_form .form-field').removeClass('form-invalid');
				$('#wsdplugin_login_username,#wsdplugin_login_password').val('');
				$('.wsdplugin_content .wsdplugin_signup_title').text('Login');

				if ( $('#recaptcha_widget_div').closest('form').attr('id') !== 'wsdplugin_login_form' )
				{
					$('#wsdplugin_newuser_form .wsdplugin_recaptcha-container').closest('.form-field').hide();
					$('#wsdplugin_login_form .wsdplugin_recaptcha-container').append( $('#recaptcha_widget_div').remove()).closest('.form-field').show();
				}
			}

			Recaptcha.reload();
		}
		var viewType = "<?php echo $type; ?>";
		switchView(viewType, true);
	});


</script>

<?php
}
?>