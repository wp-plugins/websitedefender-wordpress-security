<?php
require_once "wsd_md5_encrypt.php";


class wsdplugin_Utils
{
	static function getRandomString($length = 10)
	{
		$length = 10;
		$characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$string = "";
		for ($p = 0; $p < $length; $p++)
			$string .= $characters[mt_rand(0, strlen($characters))];
		return $string;
	}

	static function parse_url($url)
	{
		$result = parse_url($url);
		if($result === null)
			throw new Exception(__("Invalid URL."));
		if(!array_key_exists("port", $result))
			$result["port"] = 80;
		if(!array_key_exists("scheme", $result))
			$result["scheme"] = "http";
		if(!array_key_exists("query", $result))
			$result["query"] = "";
		if(array_key_exists("host", $result))
		{
			if(!array_key_exists("path", $result))
				$result["path"] = "";
		}
		else
		{
			if(array_key_exists("path", $result))
			{
				$dirs = explode("/", $result["path"], 2);
				$result["host"] = $dirs[0];
				if(count($dirs)>1) {
					$result["path"] = "/".$dirs[1];
				}
				else {$result["path"] = "/";}
			}
			else throw new Exception(__("Invalid URL [no host]."));
		}

		if($result["host"] == "")
			throw new Exception(__("Invalid URL [no host]."));

		$scheme = array_key_exists("scheme", $result) ? $result["scheme"] : "http";

		if((strcasecmp($scheme,"http") != 0) && (strcasecmp($scheme,"https") != 0))
			throw new Exception(__("Invalid URL [unsuported scheme]."));

		if(strcasecmp($scheme,"https")==0)
			$result["port"] = 443;

		$userPass = "";
		if(array_key_exists("user", $result) && array_key_exists("pass", $result)){
			$userPass = $result["user"].":".$result["pass"]."@";
		}

		$port = "";
		if(array_key_exists("port", $result)) {$port = ":".$result["port"];}

		$result["all"] = $scheme."://".$userPass.$result["host"].$port;

		return $result;
	}

	static function has_nonASCII($data)
	{
		return preg_match("/^[\x20-\x7f]*$/D",$data) == 0;
	}

	//$action_on_bad_chars "//TRANSLIT" "//IGNORE"
	static function to_utf8($data, $strict = TRUE, $action_on_bad_chars = '')
	{
		//if(mb_check_encoding($string, 'UTF-8')) return $data;
		$default_charset = ini_get('default_charset');
		if($default_charset === "") return utf8_encode($data);
		if(function_exists("iconv"))
		{
			if($strict)
			{
				$result = ivconv($default_charset, "UTF-8", $data);
				if(($result !== FALSE) && (strlen($data) == strlen($result)))return $result;
				throw new Exception(__("UTF8 conversion error"), 1);
			}
			$result = ivconv($default_charset, "UTF-8".$action_on_bad_chars, $data);
			if($result) return $result;
			throw new Exception(__("UTF8 conversion error"), 1);
		}
		throw new Exception(__("UTF8 conversion error"), 1);
	}

	static function cssUrl($fileName = null)
	{
		$fileUrl = wsdplugin_WSD_PLUGIN_BASE_URL;

		if (strlen($fileName) > 0)
		{
			$fileUrl .= 'css/' . $fileName;
		}
		return $fileUrl;
	}
	static function jsUrl($fileName = null)
	{
		$fileUrl = wsdplugin_WSD_PLUGIN_BASE_URL;

		if (strlen($fileName) > 0)
		{
			$fileUrl .= 'js/' . $fileName;
		}
		return $fileUrl;
	}
	static function imgUrl($fileName = null)
	{
		$fileUrl = wsdplugin_WSD_PLUGIN_BASE_URL;

		if (strlen($fileName) > 0)
		{
			$fileUrl .= 'img/' . $fileName;
		}
		return $fileUrl;
	}
}

class wsdplugin_Handler
{
    static $settings_cache = array();
    static $problems = array();

	static function site_url()
	{
		$url = self::get_option('siteurl', '');
		return rtrim($url, '/').'/';
	}

	static function delete_option($option)
	{
	    if(array_key_exists($option, self::$settings_cache))
	       unset(self::$settings_cache[$option]);
		delete_option($option);
	}

	static function set_option($option, $value)
	{
	    if(array_key_exists($option, self::$settings_cache))
        {
            if(self::$settings_cache[$option] == $value)
                return;
            self::$settings_cache[$option] = $value;
        }
		delete_option($option);
		add_option($option, $value);
	}

	static function get_option($option, $default = FALSE)
	{
	    if(array_key_exists($option, self::$settings_cache))
	       return self::$settings_cache[$option];
		return get_option($option, $default);
	}

	static function render_error($error, $reason = NULL)
	{
		$markup = <<<HTML

<style>

/*
 * INFO / ERROR boxes
 */
.wsdplugin_message
{
	margin: 5px 0 15px;
	padding: 0 0.6em;
	border-radius: 3px;
	border: 1px solid;
}
.wsdplugin_message.wsdplugin_message_warning
{
	background-color: #FFFFE0;
	border-color: #E6DB55;
}
.wsdplugin_message.wsdplugin_message_error
{
	background-color: #FCC2C2;
	border-color: #CC0000;
}
.wsdplugin_message.wsdplugin_message_success
{
	background-color: #FFFFE0;
	border-color: #E6DB55;
	color: #006600;
}
.wsdplugin_message p
{
	margin: 0.5em 0;
	padding: 2px;
}

</style>

<div class="wsdplugin_content">

<div class="wsdplugin_message wsdplugin_message_error" style="padding: 4px 10px;">

{$error}

<div style="height: 30px;"></div>
If the problem persists you may click <a href="admin.php?page=wsdplugin_dashboard&reset">here</a> to reset the plugin or
<a href="{$_SERVER['REQUEST_URI']}">here</a> to refresh the page. You can also contact <a href="mailto:support@websitedefender.com">WebsiteDefender Support</a>.

</div>

</div>


HTML;

		wp_die($markup, 'Error', array('back_link' => false));
	}

	static function render_new_user_form($username = '', $name = '', $surname = '', $message = '', $errorMessage = '')
	{
		if (strlen($username) == 0)
			$username = self::get_option('admin_email', '');

		require 'form_register.php';
		wsdplugin_render_newuser_form($username, $name, $surname, $message, $errorMessage);
	}

	static function render_login_form($username = '', $message = '', $errorMessage = '')
	{
		require 'form_login.php';
		wsdplugin_render_login_form($username, $message, $errorMessage);
	}

	static function render_captcha($message = NULL, $errorMessage = NULL)
	{
		wp_enqueue_style('wsdplugin_css_general',   wsdplugin_Utils::cssUrl('general.css'), array(), '1.0');
		wp_enqueue_style('dashboard');

		if (strlen($message) > 0)
		{
			echo '
<div class="wsdplugin_message wsdplugin_message_warning" style="margin-right: 15px;">
    <p>
        <strong>' . wptexturize($message) . '</strong>
    </p>
</div>';
		}

		if (strlen($errorMessage) > 0)
		{
			echo '
<div class="error wsdplugin_error_box" style="margin-right: 15px;">
    <p>
        <strong>' . wptexturize($errorMessage) . '</strong>
    </p>
</div>';
		}

		require_once('recaptchalib.php');
		echo '<form method="post" style="text-align: center; margin: 0px auto; margin-top: 30px; width: 330px;">';
		echo wsdplugin_recaptcha_get_html(RECAPTCHA_PUBLIC_KEY, NULL, TRUE);
		echo '<div style="text-align: left;">';
		echo '<input type="submit" class="button button-primary" value="Submit" style="margin-top: 10px;;" />';
		echo '</div>';
		echo '</form>';
	}

    static function agentExists($agentname = NULL)
    {
        if($agentname == NULL)
            $agentname = self::get_option("WSD-AGENT-NAME", '');
        if(is_file(ABSPATH.$agentname)) return TRUE;
        if(array_key_exists("DOCUMENT_ROOT", $_SERVER) && is_file($_SERVER["DOCUMENT_ROOT"].'/'.$agentname)) return TRUE;
        return FALSE;
    }


	static function validateInput($data, $rule, $failOnEmpty = True, $rule_value = 512)
	{
		if(empty($data) && ($data !== FALSE) && ($data !== 0))
		{
			if($failOnEmpty) return "empty";
			return True;
		}
		switch($rule)
		{
			case "bool":
				if(($data === True)||($data === False))
					break;
				return "not boolean";
			case "email":
				if(!is_string($data)) return "not string";
				if(strlen($data)>256) return "too long";
				if(filter_var($data, FILTER_VALIDATE_EMAIL))
					break;
				return "invalid email";
				break;
			case "ascii":
				if(!is_string($data)) return "not string";
				if(strlen($data)>$rule_value) return "too long";
				if(preg_match("/^[\x20-\x7f]*$/D",$data) == 0) return "contains illegal characters";
				break;
			case "number":
				if(is_string($data) && (strlen($data)>$rule_value)) return "too long";
				if(!is_numeric($data)) return "not a number";
				break;
			case "str":
				if(!is_string($data)) return "not string";
				if(strlen($data)>$rule_value) return "too long";
				break;
			case "int":
				if(is_string($data) && (strlen($data)>$rule_value)) return "too long";
				if(!is_numeric($data)) return "not a number";
				if((int)($data + 0) != $data) return "not integer";
				break;
			case "ip":
				if(!filter_var($data, FILTER_VALIDATE_IP))
					return "not ip";
				break;
			case "md5":
				if(!is_string($data)) return "not string";
				if(strlen($data)!=32) return "bad hash length";
				if(preg_match("/^([a-f0-9])*$/iD",$data) == 0) return "bad hash";
				break;
			case "sha1":
				if(!is_string($data)) return "not string";
				if(strlen($data)!=40) return "bad hash length";
				if(preg_match("/^([a-f0-9])*$/iD",$data) == 0) return "bad hash";
				break;
			case "targetid":
				if(!is_string($data)) return "not string";
				if(strlen($data)!=33) return "bad length ";
				if(preg_match("/^t([a-f0-9])*$/iD",$data) == 0) return "bad targetid";
				break;
			default:
				return "invalid-rule";
		}
		return True;
	}

	static function check()
	{
		$website = self::site_url();
		if($website === '/')
		{
			self::render_error(__("The wordpress url record can't be empty."));
			return FALSE;
		}

		//let's see if there are things coming from POST
		$recaptcha_challenge = isset($_POST['recaptcha_challenge_field']) ? $_POST['recaptcha_challenge_field'] : FALSE;
		$recaptcha_response  = isset($_POST['recaptcha_response_field']) ? $_POST['recaptcha_response_field'] : FALSE;

		if (isset($_POST['type']))
		{
			if ($_POST['type'] == 'new')
			{
                global $wsdplugin_nonce;
                check_admin_referer('wsdplugin_nonce');
                $_nonce = $_POST['wsdplugin_nonce_form'];
                if (empty($_nonce) || ($_nonce != $wsdplugin_nonce)){
                    wp_die("Invalid request!");
                }

				$username  = $_POST['wsdplugin_newuser_username'];
				$password1 = $_POST['wsdplugin_newuser_password1'];
				$password2 = $_POST['wsdplugin_newuser_password2'];
				$name      = $_POST['wsdplugin_newuser_name'];
				$surname   = $_POST['wsdplugin_newuser_surname'];

				if (self::validateInput($username, 'email') !== true)
				{
					self::render_new_user_form($username, $name, $surname, '', __('The email is not valid.'));
					self::render_login_form('', __("If you've already got a WebsiteDefender account, you can log in here."));
					return false;
				}

				if (self::validateInput($name, 'str') !== true)
				{
					self::render_new_user_form($username, $name, $surname, '', __('The name is not valid.'));
					self::render_login_form('', __("If you've already got a WebsiteDefender account, you can log in here."));
					return false;
				}
				if (self::validateInput($surname, 'str') !== true)
				{
					self::render_new_user_form($username, $name, $surname, '', 'The surname is not valid.');
					self::render_login_form('', __("If you've already got a WebsiteDefender account, you can log in here."));
					return false;
				}

				if (($password1 != $password2) || wsdplugin_Utils::has_nonASCII($password1))
				{
					self::render_new_user_form($username, $name, $surname, '', __('The passwords do not match or contain invalid characters.'));
					self::render_login_form('', __("If you've already got a WebsiteDefender account, you can log in here."));
					return false;
				}

				self::set_option('WSD-USER',    $username);
				self::set_option('WSD-NAME',    $name);
				self::set_option('WSD-SURNAME', $surname);

				self::set_option('WSD-HASH', md5($password1));
				self::set_option('WSD-NEW',  TRUE);
				self::delete_option('WSD-KEY');
			}
			if ($_POST['type'] == 'login')
			{
                global $wsdplugin_nonce;
                check_admin_referer('wsdplugin_nonce');
                $_nonce = $_POST['wsdplugin_nonce_form'];
                if (empty($_nonce) || ($_nonce != $wsdplugin_nonce)){
                    wp_die("Invalid request!");
                }

				$username = $_POST["wsdplugin_login_username"];
				$password = $_POST["wsdplugin_login_password"];

				if (self::validateInput($username, 'email') !== true)
				{
					self::render_login_form($username, '', 'The email is not valid.');
					return false;
				}

				self::set_option('WSD-USER', $username);
				self::set_option('WSD-HASH', md5($password));
				self::delete_option('WSD-KEY');
				self::delete_option('WSD-NEW');
			}
		}

		//take some automated decisions based on what we have available
		$username = self::get_option('WSD-USER', '');
		if($username === '')
			$username = self::get_option('admin_email', '');

		if($username == '')
		{
			self::render_new_user_form($username);
			self::render_login_form('', __("If you've already got a WebsiteDefender account, you can log in here."));
			return FALSE;
		}

		$hash    = self::get_option('WSD-HASH', FALSE);
		$wsd_key = self::get_option('WSD-KEY', FALSE);
		$id      = self::get_option('WSD-ID', FALSE);

		if(($hash == FALSE) && ($wsd_key == FALSE))
		{
			self::render_new_user_form($username);
			self::render_login_form('', __("If you've already got a WebsiteDefender account, you can log in here."));
			return FALSE;
		}

		try
		{
			$request = array("email"   => $username, "website" => $website);

			if($recaptcha_challenge !== FALSE)
				$request["captcha"] = array($recaptcha_challenge, $recaptcha_response, $_SERVER["REMOTE_ADDR"]);

			if($wsd_key == FALSE)
			{
				$request["hash"] = $hash;
				if(self::get_option('WSD-NEW', FALSE))
				{
					$request["source"]  = wsdplugin_SRC_ID;
					$request["name"]    = wsdplugin_Utils::to_utf8(self::get_option('WSD-NAME', FALSE), FALSE, '//TRANSLIT');
					$request["surname"] = wsdplugin_Utils::to_utf8(self::get_option('WSD-SURNAME', FALSE), FALSE, '//TRANSLIT');
					$request["new"]     = TRUE;
				}
			}

			if($id)
			{
				$request["id"] = self::get_option('WSD-ID', FALSE);
                $agentname = self::get_option("WSD-AGENT-NAME", '');
                if($agentname !== '')
                {
                    //check if agent exists
                    if(! wsdplugin_Handler::agentExists($agentname))
                    {
                        //check if we have the agent data
                        $agentdata = get_option("WSD-AGENT-DATA", FALSE);
                        if($agentdata !== FALSE)
                        {
                            //if we already retrieved the data then also try to save it
                            $result = @file_put_contents(ABSPATH.$agentname , $agentdata);

	                        if ($result === false)
		                        self::$problems[] = 'agent-install-error';
                        }
                        else $agentname = '';
                    }
                }
				$request["aid"] = md5($agentname);
			}

			$result = wsdplugin_RPC::execute('process', $request, $wsd_key);
			$result = $result['body'];

			if(array_key_exists('WSD-KEY', $result))
			{
				self::set_option('WSD-KEY', $result['WSD-KEY']);
				self::delete_option('WSD-NEW');
			}

			if(array_key_exists('error', $result))
			{
				self::delete_option('WSD-WORKING');

				$error = $result['error'];
				if($error['code'] == "invalid-request")
				{
					self::render_error(__("An error happened during the process [code:{$error["message"]}]. If the problem persists, please contact <a href=\"mailto:support@websitedefender.com\">WebsiteDefender Support</a>."));
				}
				else if($error['code'] == "invalid-api-version")
				{
					self::render_error(__("This version of plugin is not up to date. Please update your plugin and try again."));
				}
				else if($error['code'] == 'email-taken')
				{
					self::render_new_user_form($username, '','', '', __("Email is already taken. Please specify a different email."));
					self::render_login_form($username, sprintf(__("If you own %s please log in with that account"), $username));
				}
				else if($error['code'] == "service-down")
				{
					self::render_error(__("The server is in maintenance mode [code:{$error["message"]}]. Please try again later. If the problem persists, please contact <a href=\"mailto:support@websitedefender.com\">WebsiteDefender Support</a>."));
				}
				else if($error['code'] == "invalid-url")
				{
					self::render_error("Invalid website url", $error["message"]);
				}
				else if ($error['code'] == "invalid-email")
				{
					if (isset($_POST['type']))
					{
						if ($_POST['type'] === 'login')
						{
							self::render_login_form($username, '', __('Invalid email provided'));
						}
						else if ($_POST['type'] === 'new')
						{
							self::render_new_user_form($username, '', '', '', __('Invalid email provided'));
						}
					}
                    else self::render_error(__("Invalid email provided"), $error["message"]);
				}
				else if($error['code'] == "captcha")
				{
					self::render_captcha(__('Please enter the Captcha and click Submit.'));
				}
				else if($error['code'] == "re-captcha")
				{
					self::render_captcha('', __("Incorrect Captcha entry. Please try again."));
				}
				else if ($error['code'] == "invalid-name")
				{
					self::render_new_user_form($username, '', '', '', __('Invalid name provided'));
					self::render_login_form($username);
				}
				else if ($error['code'] == "invalid-surname")
				{
					self::render_new_user_form($username, '','', '', __("Invalid surname provided"));
					self::render_login_form($username, "");
				}
				else if($error['code'] == "bad-login")
				{
					self::delete_option("WSD-HASH");
					self::render_new_user_form('', '', '', __('If the website is not registered already with WebsiteDefender, create a new account.'));
					self::render_login_form($username, '', __("Invalid login information"));
				}
				else if($error['code'] == "bad-key")
				{
					self::delete_option("WSD-KEY");
					self::render_error('Autentification error. Please refresh the page or contact <a href="mailto:support@websitedefender.com">WebsiteDefender Support</a> if the problem persists.');
				}
				else if($error['code'] == "bad-account")
				{
					if ($error['message'] == 'disabled')
					{
						self::render_error('This account has been disabled. please contact <a href="mailto:support@websitedefender.com">WebsiteDefender Support</a> for more information.');
					}
					else if($error['message'] == 'not-verified')
					{
						self::render_error('This account is valid however was not verified. Click on the verification link received by email, or contact <a href="mailto:support@websitedefender.com">WebsiteDefender Support</a> for further instruction.');
					}
					else if($error['message'] == 'email-taken')
					{
						self::render_new_user_form($username, '','', '', 'Email already in use. Please choose another email.');
						self::render_login_form($username, "If you own {$username} please log in with that account");
					}
					else if($error['message'] == "licente-expired")
					{
						self::delete_option('WSD-ID');

						self::render_new_user_form($username, '','', '', 'The licence for this account is expired. Login to the <a target="_blank" href="https://dashboard.websitedefender.com">WebsiteDefender dashboard</a> to extend your licence.');
                        self::render_login_form('');
					}
					else if($error['message'] == "upgrade-licente")
					{
						self::delete_option('WSD-ID');
						self::render_new_user_form($username, '','', '', 'The maximum number of websites for this account has been reached. Login to the <a target="_blank" href="https://dashboard.websitedefender.com">WebsiteDefender dashboard</a> to increase the number of monitored websites.');
                        self::render_login_form('');
					}
					else if($error['message'] == "upgrade-account")
					{
						self::delete_option('WSD-ID');
						self::render_new_user_form($username, '','', '', 'The maximum number of websites for this account has been reached. Login to the <a target="_blank" href="https://dashboard.websitedefender.com">WebsiteDefender dashboard</a> to upgrade your WebsiteDefender account.');
                        self::render_login_form('');
					}
					else if($error['message'] == "max-targets")
					{
					    self::delete_option('WSD-ID');
						self::render_new_user_form($username, '','', '', 'The maximum number of websites for this account has been reached. To monitor more websites with WebsiteDefender create a new account or use a different account.');
						self::render_login_form('');
					}
					return FALSE;
				}
				else if($error['code'] == "invalid-id")
				{
					self::render_error("The target id registered with this plugin is invalid (please re-install/purge the plugin).");
				}
				else if($error['code'] == "url-exists")
				{
					self::render_login_form($username, null, 'This website is registered under a different account.<br/>Login with that account or please contact the <a href="mailto:support@websitedefender.com">WebsiteDefender Support</a> team.');
				}
				else
				{
					self::render_error($error);
				}
				return FALSE;
			}
			$result = $result['result'];
		}
		catch (Exception $e)
		{
			self::render_error($e->getMessage());
			return FALSE;
		}

		if(is_array($result))
		{
			self::set_option('WSD-WORKING', TRUE);

			if(array_key_exists('id', $result))
				self::set_option('WSD-ID', $result['id']);

			if(array_key_exists('licence', $result))
			{
				self::set_option('WSD-SCANTYPE', $result['licence'][0]);
				self::set_option('WSD-EXPIRATION', $result['licence'][1]);
			}

			if(array_key_exists('agent', $result))
			{
				$r = @file_put_contents(ABSPATH.$result['agent'][0] , $result['agent'][1]);
				if($r === FALSE)
				{
					self::$problems[] = "agent-install-error";
					self::set_option("WSD-AGENT-DATA", $result['agent'][1]);
				}
				self::set_option("WSD-AGENT-NAME", $result['agent'][0]);
			}
			return TRUE;
		}
        self::render_error("An error happened during the process [invalid-server-response]. If the problem persists, please conntact the support.");
        return FALSE;
	}
}

class wsdplugin_RPC
{
	static function execute($method, $params, $secret = NULL, $timeout = 10)
	{
		$id = rand(1, 100);
		$body = json_encode(array("jsonrpc"  => '2.0',
								  "id"       => $id,
								  "method"   => $method,
								  "params"   => $params));

		$headers = array("Content-type" => "application/json",
						 "Connection"   => "Close",
						 "WSD-api"      => wsdplugin_API_VERSION);

		if($secret)
			$headers['WSD-key'] = hash_hmac('sha1', sha1($body), $secret);

		if(function_exists("wp_remote_post"))
		{
			$args = array('method'      => 'POST',
						  'timeout'     => $timeout,
						  'redirection' => 2,
						  'httpversion' => '1.1',
						  'blocking'    => TRUE,
						  'headers'     => $headers,
						  'body'        => $body,
						  'cookies'     => array());

			$response = wp_remote_post(wsdplugin_SERVICE_URL, $args);

			if(is_wp_error($response))
				throw new Exception(__("Can't connect to server.")."[WSD-RPC-C1]");

			if($response['response']['code'] != 200)
				throw new Exception(__("Problems reaching the server.")."[HTTP-{$response['response']['code']}]");
		}
		else
		{
			$response = self::httprequest("POST", wsdplugin_SERVICE_URL, $body, $headers, $timeout, array());
		}

		$decoded = @json_decode($response["body"], TRUE);
		if ($decoded == null)
			throw new Exception(__("Invalid JSON response."."[WSD-RPC]"));

		//$result["cookies"]
		$result["body"] = $decoded;

		return $result;
	}

	const HTTP_STATUS       = 0;
	const HTTP_HEADERS      = 1;
	const HTTP_BODY         = 2;
	const HTTP_CHUNK_HEADER = 3;
	const HTTP_CHUNK_BODY   = 4;

	static function httprequest($verb, $url, $body = "", $headers = array(), $timeout = 10, $cookies, $canfallbacktononssl = FALSE)
	{
		$e = error_reporting(0);

		$result = array();
		$result["cookies"] = array();
		$result["body"] = "";
		$result["length"] = null;

		$now = time();
		$url = wsdplugin_Utils::parse_url($url);

		if ($url["error"] !== null)
		{
			return $url;
		}

		$scheme = $url["scheme"] == "https" ? "ssl://" : "";

		$fp = fsockopen($scheme . $url["host"], $url["port"], $errno, $errstr, $timeout);

		if (!$fp)
		{
			if(($scheme == "ssl://") && $canfallbacktononssl)
			{
				//fall back to normal request
				$fp = fsockopen($url["host"], 80, $errno, $errstr, $timeout);
				if (!$fp)
				{
					error_reporting($e);
					throw new Exception(__("Can't connect to server.")."[WSD-RPC-C1]");
				}
			}
			else
			{
				error_reporting($e);
				throw new Exception(__("Can't connect to server.")."[WSD-RPC-C2]");
			}
		}

		$query = ($url["query"] != '') ? '?'.$url["query"] : '';
		$out = $verb . " " . $url["path"] . $url["query"] . " HTTP/1.1\r\n";
		$out .= "Host: " . $url["host"] . "\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "Accept-Encoding: identity\r\n";
		if ($verb == "POST")
		{
			$out .= "Content-Length: " . strlen($body) . "\r\n";
		}
		foreach ($cookies as $cookie)
		{

		}
		foreach ($headers as $name => $value)
		{
			$out .= $name . ": " . $value . "\r\n";
		}
		$out .= "\r\n";
		if ($verb == "POST")
		{
			$out .= $body;
		}
		fwrite($fp, $out);
		fflush($fp);

		$status = self::HTTP_STATUS;
		$chunked = false;
		$lastChunk = "";
		$chunkLength = 0;

		while (!feof($fp))
		{
			$remaining = $timeout - (time() - $now);
			if ($remaining < 0)
			{
				error_reporting($e);
				throw new Exception(__("Request timed out.")."[WSD-RPC-1]");
			}

			stream_set_timeout($fp, $remaining + 1);
			$data = fgets($fp, 4096);
			$info = stream_get_meta_data($fp);

			if ($info["timed_out"])
			{
				error_reporting($e);
				throw new Exception(__("Request timed out.")."[WSD-RPC-2]");
			}

			if ($status == self::HTTP_STATUS)
			{
				//TODO: check status for 200, error on rest, eventually work arround 302 303
				$resultStatus = trim($data);
				$status = self::HTTP_HEADERS;
				continue;
			}

			if ($status == self::HTTP_HEADERS)
			{
				if ($data == "\r\n")
				{
					if ($chunked)
					{
						$status = self::HTTP_CHUNK_HEADER;
					}
					else
					{
						$status = self::HTTP_BODY;
					}
					continue;
				}

				$data = trim($data);
				$separator = strpos($data, ": ");

				if (($separator === false) || ($separator == 0) || ($separator >= (strlen($data) - 2)))
				{
					error_reporting($e);
					throw new Exception(__("Invalid HTTP response header.")."[WSD-RPC]");
				}

				$name = substr($data, 0, $separator);
				$value = substr($data, $separator + 2);
				if (strcasecmp("Set-Cookie", $name) == 0)
				{
					$result["cookies"][] = $value;
					continue;
				}
				if (strcasecmp("Content-Length", $name) == 0)
				{
					$result["length"] = $value + 0;
					continue;
				}
				if ((strcasecmp("Transfer-Encoding", $name) == 0) && (strpos($value, 'chunked') !== false))
				{
					$chunked = true;
					continue;
				}
				continue;
			}

			if ($status == self::HTTP_CHUNK_HEADER)
			{
				$data = trim($data);
				$sc = strpos($data, ';');
				if ($sc !== false)
				{
					$data = substr($data, 0, $sc);
				}
				$chunkLength = hexdec($data);
				if ($chunkLength == 0)
				{
					break;
				}
				$lastChunk = "";
				$status = self::HTTP_CHUNK_BODY;
				continue;
			}

			if ($status == self::HTTP_CHUNK_BODY)
			{
				$lastChunk .= $data;
				if (strlen($lastChunk) >= $chunkLength)
				{
					$result["body"] .= substr($lastChunk, 0, $chunkLength);
					$status = self::HTTP_CHUNK_HEADER;
				}
				continue;
			}

			if ($status == self::HTTP_BODY)
			{
				$result["body"] .= $data;
				if (($result["length"] !== null) && (strlen($result["body"]) >= $result["length"]))
				{
					break;
				}
				continue;
			}
		}
		fclose($fp);

		if (($result["length"] !== null) && (strlen($result["body"]) != $result["length"]))
		{
			error_reporting($e);
			throw new Exception(__("Invalid HTTP body length.")."[WSD-RPC]");
		}

		error_reporting($e);

		return $result;
	}
}

class wsdplugin_NotificationEngine
{
	static private function hasToRun($key, $interval)
	{
		$result = wsdplugin_Handler::get_option("WSD-RCACHE-{$key}", TRUE);
		if($result === TRUE) return TRUE;
		$result = $result + 0;
		if(($result + $interval) < time()) return TRUE;
		return FALSE;
	}
	static private function updateStatus($key)
	{
		wsdplugin_Handler::set_option("WSD-RCACHE-{$key}", time());
	}

	static function run()
	{
		if(!self::hasToRun("TEST-SESSION", 1800))
			return;
        //lock the session
        self::updateStatus("TEST-SESSION");

		if(wsdplugin_Handler::get_option('WSD-WORKING', FALSE) === FALSE)
			return;

		$methods = get_class_methods('wsdplugin_NotificationEngine');
		foreach($methods as $method)
		{
			$m = explode('_', $method);
			if((count($m) == 3) && ($m[0] === 'test'))
			{
				if(self::hasToRun($m[1], $m[2] + 0))
				{
					try
					{
						if(call_user_func('wsdplugin_NotificationEngine::'.$method) === TRUE)
							self::updateStatus($m[1]);
					}
					catch(Exception $e)
					{
						//var_dump($e);
					}
				}
			}
		}
	}

	static private function test_detectWP_60()
	{
		global $wpdb, $wp_version;

		$key = wsdplugin_Handler::get_option('WSD-KEY', FALSE);
		$data = array(
					"id"    => wsdplugin_Handler::get_option('WSD-ID', FALSE),
					"email" => wsdplugin_Handler::get_option('WSD-USER', FALSE),
					"type"  => "wpstatus",
					"data"  => wsd_md5_encrypt(serialize(array(
											 "wp_root"           =>ABSPATH,
											 "wp_version"        =>$wp_version,

											 "wp_db_table_prefix"=>$wpdb->base_prefix,
											 "wp_db_user"        =>$wpdb->dbuser,
											 "wp_db_password"    =>$wpdb->dbpassword,
											 "wp_db_name"        =>$wpdb->dbname,
											 "wp_db_host"        =>$wpdb->dbhost,

											 //errors
											 "wp_suppress_errors"      =>$wpdb->suppress_errors,
											 "wp_show_errors"          =>$wpdb->show_errors,
											 //Startup Errors are being displayed
											 "php_display_errors"      => strtolower(ini_get('display_errors')),
											 "php_display_startup_errors"=> strtolower(ini_get('display_startup_errors')),

											 //fs stuff
											 "fs_root"                 => is_dir(ABSPATH) ? fileperms(ABSPATH) : 0,
											 "fs_plugins_index_php"    => is_file(ABSPATH.'plugins/index.php') ? fileperms(ABSPATH.'/plugins/index.php') : 0,
											 "fs_uploads"              => is_dir(ABSPATH."uploads") ? fileperms(ABSPATH."uploads") : 0,
											 "fs_themes_index_php"     => is_file(ABSPATH.'uploads/index.php') ? fileperms(ABSPATH.'/uploads/index.php') : 0,
											 "fs_uploads_index_php"    => is_file(ABSPATH.'themes/index.php') ? fileperms(ABSPATH.'/themes/index.php') : 0,
											 "fs_readme"               => is_file(ABSPATH.'readme.html') ? fileperms(ABSPATH.'readme.html') : 0,
											 "fs_htaccess"             => is_file(ABSPATH.'.htaccess') ? fileperms(ABSPATH.'.htaccess') : 0,
											 "fs_wp_config_php"        => is_file(ABSPATH.'wp-config.php') ? fileperms(ABSPATH.'wp-config.php') : 0,
											 "fs_wp_admin_htaccess"    => is_file(ABSPATH.'wp-admin/.htaccess') ? fileperms(ABSPATH.'wp-admin/.htaccess') : 0,
											 "fs_wp_admin"             => is_dir(ABSPATH."wp-admin") ? fileperms(ABSPATH."wp-admin") : 0,
											 "fs_wp_admin"             => is_dir(ABSPATH."wp-content") ? fileperms(ABSPATH."wp-content") : 0,
											 "fs_wp_admin"             => is_dir(ABSPATH."wp-includes") ? fileperms(ABSPATH."wp-includes") : 0,
											 "fs_wp_admin"             => is_dir(ABSPATH."wp-admin") ? fileperms(ABSPATH."wp-admin") : 0,

                                             "sys_os"                  => PHP_OS,
                                             "sys_server"              => $_SERVER["SERVER_SOFTWARE"],
                                             "sys_mysql_server"        => $wpdb->get_var("SELECT VERSION() AS version"),

                                             "php_version"             => PHP_VERSION,
                                             "php_sm"                  => ini_get('safe_mode'),
                                             "php_allow_url_fopen"     => ini_get('allow_url_fopen'),
                                             "php_memory_limit"        => ini_get('memory_limit'),
                                             "php_post_max_size"       => ini_get('post_max_size'),
                                             "php_max_execution_time"  => ini_get('max_execution_time')
											  )), sha1($key))
					 );
		$result = wsdplugin_RPC::execute("push", $data, $key);
		//echo "result\n"; var_dump($result);
		return $result['body']['result'];
	}
}

class wsdplugin_security
{
    static $fixes  = array();
    static $system_info = array();
    static $alerts = array();

    static function alert($type, $severity = 0, $value = NULL, $stack = 1)
    {
        if($severity === 0)
        {
            if(array_key_exists($type, self::$alerts))
                unset(self::$alerts[$type]);
            return;
        }
        if(!array_key_exists($type, self::$alerts)) self::$alerts[$type] = array();
        self::$alerts[$type][] = array(time(), $severity, $value);
        $c = count(self::$alerts[$type]);
        if($c > $stack) self::$alerts[$type] = array_slice(self::$alerts[$type], $c - $stack);
        return TRUE;
    }

    static function check_CurrentVersion()
    {
        $c = get_site_transient('update_core');
        if(is_object($c))
        {
            if(empty($c->updates)) return self::alert('core-update');
            if (!empty($c->updates[0]))
            {
                $c = $c->updates[0];
                if (!isset($c->response) || 'latest' == $c->response ) return self::alert('core-update');
                if ('upgrade' == $c->response)
                    return self::alert('core-update', 3, $c->current);
            }
        }
    }

    static function check_AdminUsernameInfo()
    {
        global $wpdb;
        $u = $wpdb->get_var("SELECT `ID` FROM $wpdb->users WHERE user_login='admin';");
        if(!empty($u)) self::alert('user-admin-found', 2, TRUE);
        else self::alert('user-admin-found');
    }

    static function check_DatabasePrefixInfo()
    {
        global $table_prefix;
        if(strcasecmp('wp_', $table_prefix)==0)self::alert('table-prefix', 2, 'wp_');
        else self::alert('table-prefix');
    }

    public static function check_files()
    {
        if(!is_file(WP_CONTENT_DIR.'/index.php')) self::alert('no-index-wp-content', 2);
        else self::alert('no-index-wp-content');

        if(!is_file(WP_CONTENT_DIR.'/plugins/index.php')) self::alert('no-index-plugins', 2);
        else self::alert('no-index-plugins');

        if(!is_file(WP_CONTENT_DIR.'/themes/index.php')) self::alert('no-index-themes', 2);
        else self::alert('no-index-themes');

        if(!is_dir(WP_CONTENT_DIR.'/uploads')) self::alert('no-index-uploads');
        else
        {
            if(!is_file(WP_CONTENT_DIR.'/uploads/index.php')) self::alert('no-index-uploads', 2);
            else self::alert('no-index-uploads');
        }

        if(!is_file(ABSPATH.'wp-admin/.htaccess'))self::alert('no-htaccess-wp-admin', 2);
        else self::alert('no-htaccess-wp-admin');

        if(!is_file(ABSPATH.'wp-admin/.htaccess'))self::alert('no-htaccess-wp-admin', 2);
        else self::alert('no-htaccess-wp-admin');

        //this should be tested if can be downloaded
        if(!is_file(ABSPATH.'readme.html'))self::alert('readme-in-root', 2);
        else self::alert('readme-in-root');
    }

    public static function check_DatabaseUserAccessRights()
    {
        global $wpdb;

        $rights = $wpdb->get_results("SHOW GRANTS FOR CURRENT_USER()", ARRAY_N);

        if(empty($rights)) return self::alert('access-rights');

        foreach($rights as $right)
        {
            if(!empty($right[0]))
            {
                $r = strtoupper($right[0]);

                if (preg_match("/GRANT ALL PRIVILEGES/i", $r)) {
                    return self::alert('access-rights', 2, array("ALL PRIVILEGES"));
                }
                else
                {
                    if (preg_match_all("/CREATE|DELETE|DROP|EVENT|EXECUTE|FILE|PROCESS|RELOAD|SHUTDOWN|SUPER/", $r, $matches)){
                        if (! empty($matches[0])){
                            $m = $matches[0];
                            $m = array_unique($m);
                            if (count($m) >= 5){
                                return self::alert('access-rights', 2, $m);
                            }
                        }
                    }
                }
            }
        }
    }

    static function getSystemInfoScanReport()
    {
        global $wpdb;
        self::$system_info = array();
        self::$system_info[__('Operating System')] = array(PHP_OS, NULL);
        self::$system_info[__('Server')] = array($_SERVER["SERVER_SOFTWARE"], NULL);

        $msqlv = $wpdb->get_var("SELECT VERSION() AS version");
        self::$system_info[__('MYSQL Version')] = array($msqlv, NULL);

        $mysqlinfo = $wpdb->get_results("SHOW VARIABLES LIKE 'sql_mode'");
        if (is_array($mysqlinfo)) $sql_mode = $mysqlinfo[0]->Value;
        if (empty($sql_mode)) $sql_mode = __('Not set');
        self::$system_info[__('SQL Mode')] = array($sql_mode, "wsdwp_sql_mode");

        self::$system_info[__('PHP Version')]  = array(PHP_VERSION, NULL);

        $sm = ini_get('safe_mode'); if (empty($sm)) { $sm = __('Off');}
        self::$system_info[__('PHP Safe Mode')] = array($sm, "wsdwp_safe_mode");

        if(ini_get('allow_url_fopen')) $allow_url_fopen = __('On');
        else $allow_url_fopen = __('Off');
        self::$system_info[__('PHP Allow URL fopen')] = array($allow_url_fopen, "wsdwp_url_fopen");

        if(ini_get('memory_limit')) $memory_limit = ini_get('memory_limit');
        else $memory_limit = __('N/A');
        self::$system_info[__('PHP Memory Limit')] = array($memory_limit, "wsdwp_memory_limit");

        if (function_exists('memory_get_usage')) $memory_usage = round(memory_get_usage() / 1024 / 1024, 2) . __(' MByte');
        else $memory_usage = __('N/A');
        self::$system_info[__('Memory Usage')] = array($memory_usage, NULL);

        if(ini_get('upload_max_filesize')) $upload_max = ini_get('upload_max_filesize');
        else $upload_max = __('N/A');
        self::$system_info[__('PHP Max Upload Size')] = array($upload_max, 'wsdwp_upload_max_filesize');

        self::$system_info[__('PHP Display Errors')] = array(ini_get('display_errors') == 1 ? 'On': 'Off', NULL);
        self::$system_info[__('PHP Display Startup Errors')] = array(ini_get('display_startup_errors') == 1 ?'On' : 'Off', NULL);
        self::$system_info[__('WP Errors')] = array(($wpdb->show_errors || !$wpdb->suppress_errors) ? 'On' : 'Off', NULL);

        if(ini_get('post_max_size')) $post_max = ini_get('post_max_size');
        else $post_max = __('N/A');
        self::$system_info[__('PHP Post Max Size')] = array($post_max, "wsdwp_post_max_size");

        if(ini_get('max_execution_time')) $max_execute = ini_get('max_execution_time');
        else $max_execute = __('N/A');
        self::$system_info[__('PHP Max Script Execute Time')] = array($max_execute, "wsdwp_max_execution_time");

        if (is_callable('exif_read_data')) $exif = __('Yes'). " ( V" . substr(phpversion('exif'),0,4) . ")" ;
        else $exif = __('No');
        self::$system_info[__('PHP Exif Support')] = array($exif, "wsdwp_exif");

        if (is_callable('iptcparse')) $iptc = __('Yes');
        else $iptc = __('No');
        self::$system_info[__('PHP IPTC Support')] = array($iptc, "wsdwp_iptc");

        if (is_callable('xml_parser_create')) $xml = __('Yes');
        else $xml = __('No');
        self::$system_info[__('PHP XML Support')] = array($xml, "wsdwp_xml");

    }


    static function fix_hideWpVersionBackend()
    {
        if(is_admin() && !current_user_can('administrator'))
        {
            wp_enqueue_style('remove-wpv-css', wsdplugin_PLUGIN_PATH.'css/remove_wp_version.css');
            wp_enqueue_script('remove-wp-version', wsdplugin_PLUGIN_PATH.'js/remove_wp_version.js', array('jquery'));
            remove_action( 'update_footer', 'core_update_footer' );
        }
        return TRUE;
    }

    static function fix_preventWpContentDirectoryListing()
    {
        function cf($name)
        {
            $file = $name.'/index.php';
            if(is_file($file)) return TRUE;

            if(is_writable(WP_CONTENT_DIR))
            {
                $f = @fopen($file,'w');
                if($f)
                {
                    fclose($f);
                    @chmod($file,'0644');
                    return TRUE;
                }
            }
            return FALSE;
        }
        $v1 = cf(WP_CONTENT_DIR);
        $v2 = cf(WP_CONTENT_DIR.'/plugins');
        $v3 = cf(WP_CONTENT_DIR.'/themes');

        return $v1 && $v2 && $v3;
    }

     static function fix_removeErrorNotificationsFrontEnd()
    {
        $str = '<link rel="stylesheet" type="text/css" href="'.wsdplugin_PLUGIN_PATH.'css/styles-extra.css"/>';
        add_action('login_head', create_function('$a', "echo '{$str}';"));
        add_filter('login_errors', create_function('$a', "return null;"));
        return TRUE;
    }

    static function fix_removeThemeUpdateNotifications()
    {
        if(current_user_can('administrator')) return TRUE;

        remove_action( 'load-themes.php', 'wp_update_themes' );
        remove_action( 'load-update.php', 'wp_update_themes' );
        remove_action( 'admin_init', '_maybe_update_themes' );
        remove_action( 'wp_update_themes', 'wp_update_themes' );
        // 3.0
        remove_action( 'load-update-core.php', 'wp_update_themes' );
        add_filter( 'pre_transient_update_themes', create_function( '$a', "return null;" ));

        return TRUE;
    }

    static function fix_removeCoreUpdateNotification()
    {
        if(current_user_can('administrator')) return TRUE;

        add_action( 'admin_init', create_function( '$a', "remove_action( 'admin_notices', 'maintenance_nag' );" ) );
        add_action( 'admin_init', create_function( '$a', "remove_action( 'admin_notices', 'update_nag', 3 );" ) );
        add_action( 'admin_init', create_function( '$a', "remove_action( 'admin_init', '_maybe_update_core' );" ) );
        add_action( 'init', create_function( '$a', "remove_action( 'init', 'wp_version_check' );" ) );
        add_filter( 'pre_option_update_core', create_function( '$a', "return null;" ) );
        remove_action( 'wp_version_check', 'wp_version_check' );
        remove_action( 'admin_init', '_maybe_update_core' );
        add_filter( 'pre_transient_update_core', create_function( '$a', "return null;" ) );
        // 3.0
        add_filter( 'pre_site_transient_update_core', create_function( '$a', "return null;" ) );

        return TRUE;
    }

    static function fix_removePluginUpdateNotifications()
    {
        if(current_user_can('administrator')) return TRUE;

        add_action( 'admin_init', create_function( '$a', "remove_action( 'admin_init', 'wp_plugin_update_rows' );" ), 2 );
        add_action( 'admin_init', create_function( '$a', "remove_action( 'admin_init', '_maybe_update_plugins' );" ), 2 );
        add_action( 'admin_menu', create_function( '$a', "remove_action( 'load-plugins.php', 'wp_update_plugins' );" ) );
        add_action( 'admin_init', create_function( '$a', "remove_action( 'admin_init', 'wp_update_plugins' );" ), 2 );
        add_action( 'init', create_function( '$a', "remove_action( 'init', 'wp_update_plugins' );" ), 2 );
        add_filter( 'pre_option_update_plugins', create_function( '$a', "return null;" ) );
        remove_action( 'load-plugins.php', 'wp_update_plugins' );
        remove_action( 'load-update.php', 'wp_update_plugins' );
        remove_action( 'admin_init', '_maybe_update_plugins' );
        remove_action( 'wp_update_plugins', 'wp_update_plugins' );
        // 3.0
        remove_action( 'load-update-core.php', 'wp_update_plugins' );
        add_filter( 'pre_transient_update_plugins', create_function( '$a', "return null;" ) );

        return TRUE;
    }

    static function fix_removeWindowsLiveWriter()
    {
        if(!function_exists('wlwmanifest_link')) return FALSE;
        if(current_user_can('administrator')) return TRUE;

        remove_action('wp_head', 'wlwmanifest_link');
        return TRUE;
    }

    static function fix_removeReallySimpleDiscovery()
    {
        if(!function_exists('rsd_link')) return FALSE;
        if(current_user_can('administrator')) return TRUE;

        remove_action('wp_head', 'rsd_link');
        return TRUE;
    }

    static function fix_disableErrorReporting()
    {
        if(current_user_can('administrator')) return TRUE;
        global $wpdb;
	    ini_set('display_errors', '0');
	    ini_set('display_startup_errors', false);
	    error_reporting(0);
        $wpdb->hide_errors();
        $wpdb->suppress_errors();
        return TRUE;
    }

    static function fix_removeWpMetaGenerators()
    {
        if(current_user_can('administrator')) return TRUE;
        //@@ remove various meta tags generators from blog's head tag
        function wsdplugin_filter_generator($gen, $type)
        {
            switch ($type)
            {
                case 'html':
                    $gen = '<meta name="generator" content="WordPress">';
                    break;
                case 'xhtml':
                    $gen = '<meta name="generator" content="WordPress" />';
                    break;
                case 'atom':
                    $gen = '<generator uri="http://wordpress.org/">WordPress</generator>';
                    break;
                case 'rss2':
                    $gen = '<generator>http://wordpress.org/?v=</generator>';
                    break;
                case 'rdf':
                    $gen = '<admin:generatorAgent rdf:resource="http://wordpress.org/?v=" />';
                    break;
                case 'comment':
                    $gen = '<!-- generator="WordPress" -->';
                    break;
            }
            return $gen;
        }
        foreach (array( 'html', 'xhtml', 'atom', 'rss2', 'rdf', 'comment' ) as $type)
            add_filter("get_the_generator_".$type, 'wsdplugin_filter_generator', 10, 2);
    }

    static function fix_hideWpVersion()
    {
        global $wp_version, $wp_db_version, $manifest_version, $tinymce_version;

        if(current_user_can('administrator')) return TRUE;

        // random values
        $v = intval( rand(0, 9999) );
        $d = intval( rand(9999, 99999) );
        $m = intval( rand(99999, 999999) );
        $t = intval( rand(999999, 9999999) );

        if ( function_exists('the_generator') )
        {
            // eliminate version for wordpress >= 2.4
            remove_filter( 'wp_head', 'wp_generator' );
            $actions = array( 'rss2_head', 'commentsrss2_head', 'rss_head', 'rdf_header', 'atom_head', 'comments_atom_head', 'opml_head', 'app_head' );
            foreach ( $actions as $action ) {
                remove_action( $action, 'the_generator' );
            }

            // for vars
            $wp_version = $v;
            $wp_db_version = $d;
            $manifest_version = $m;
            $tinymce_version = $t;
        }
        else {
            // for wordpress < 2.4
            add_filter( "bloginfo_rss('version')", create_function('$a', "return $v;") );

            // for rdf and rss v0.92
            $wp_version = $v;
            $wp_db_version = $d;
            $manifest_version = $m;
            $tinymce_version = $t;
        }
    }

    public static function run_fixes()
    {
        $result = array();
        $methods = get_class_methods('wsdplugin_security');
        foreach($methods as $method)
        {
            $m = explode('_', $method);
            if((count($m) == 2) && ($m[0] === 'fix'))
            {
                try
                {
                    $result[$m[1]] = call_user_func('wsdplugin_security::'.$method);
                }
                catch(Exception $e)
                {
                    $result[$m[1]] = NULL;
//                    var_dump($e);
                }
            }
        }
        self::$fixes = $result;
    }

    public static function run_checks()
    {
        //We don't have any stacking LGA alerts yet, so there is no reason to save this alerts between sessions
        static $alerts = array();
        //$stralerts = wsdplugin_Handler::get_option('WSD-ALERTS', FALSE);
        //self::$alerts = ($stralerts === FALSE) ? array() : unserialize($stralerts);

        $methods = get_class_methods('wsdplugin_security');
        foreach($methods as $method)
        {
            $m = explode('_', $method);
            if((count($m) == 2) && ($m[0] === 'check'))
            {
                try
                {
                    call_user_func('wsdplugin_security::'.$method);
                }
                catch(Exception $e)
                {
                    //var_dump($e);
                }
            }
        }
        //see above
        //wsdplugin_Handler::set_option('WSD-ALERTS', serialize(self::$alerts));
    }
}
