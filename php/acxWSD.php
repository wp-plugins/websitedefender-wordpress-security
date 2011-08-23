<?php if (!defined('ABSPATH')) { exit; }
/**
 * Facilitates the login/register with websitedefender.com for website scanner.
 *
 * @author WebsiteDefender
 * $rev #1 07/16/2011 {c}$
 * $rev #2 07/21/2011 {c}$
 * $rev #3 08/23/2011 {c}$
 */
class acxWSD
{
    const WSD_URL = 'https://dashboard.websitedefender.com/';
    const WSD_URL_RPC = 'https://dashboard.websitedefender.com/jsrpc.php';
    const WSD_URL_DOWN = 'https://dashboard.websitedefender.com/download.php';
    const WSD_SOURCE = 1;
    //error codes
    const WSD_ERROR_LIMITATION = 0x27;
    const WSD_ERROR_WPP_SERVICE_DOWN = 0x50;
    const WSD_ERROR_WPP_ERROR_INVALID_URL = 0x51;
    const WSD_ERROR_WPP_URL_REGISTERED = 0x52;
    const WSD_WSD_ERROR_WPP_NEWUSR_PARAM = 0x53;
    const WSD_ERROR_WPP_INVALID_CAPTCHA =0x54 ;
    const WSD_ERROR_WPP_USER_EXIST = 0x55;
    const WSD_ERROR_WPP_URL_EXIST = 0x56;
    //http status
    const HTTP_STATUS = 0;
    const HTTP_HEADERS = 1;
    const HTTP_BODY = 2;
    const HTTP_CHUNK_HEADER = 3;
    const HTTP_CHUNK_BODY = 4;
    
    
    // constructor
    public function __construct() {}

    
    function site_url()
    {
        $url = get_option( 'siteurl' );
        return trailingslashit($url);
    }


    function parseUrl($url)
    {
        $result = parse_url($url);
        if($result === null) { return array("error"=> __("Invalid URL.")); }
        $result["error"] = null;
        if(!array_key_exists("port", $result)) {$result["port"] = 80;}
        if(!array_key_exists("scheme", $result)) {$result["scheme"] = "http";}
        if(!array_key_exists("query", $result)) {$result["query"] = "";}
        else {$result["query"] = "?" . $result["query"];}
        if(array_key_exists("host", $result))
        {
            if(!array_key_exists("path", $result)) $result["path"] = "";
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
            else {return array("error"=>__("Invalid URL (no host)."));}
        }

        if($result["host"] == "") {return array("error"=>__("Invalid URL (no host)."));}

        $scheme = "http";
        if(array_key_exists("scheme", $result)) {$scheme = $result["scheme"];}

        if((strcasecmp($scheme,"http")!=0) && (strcasecmp($scheme,"https")!=0)) {return array("error"=>__("Invalid URL (unknown scheme)."));}

      if(strcasecmp($scheme,"https")==0) $result["port"] = 443;

        $userPass = "";
        if(array_key_exists("user", $result) && array_key_exists("pass", $result)) {
            $userPass = $result["user"].":".$result["pass"]."@";
        }

        $port = "";
        if(array_key_exists("port", $result)) {$port = ":".$result["port"];}

        $result["all"] = $scheme."://".$userPass.$result["host"].$port;
        
        return $result;
    }

    function httpRequest($verb, $url, $body="", $headers=array(), $timeout = 10)
    {
      $e = error_reporting(0);

        $result = array();
        $result["cookie"] = null;
        $result["body"] = "";
        $result["length"] = null;
        $result["error"] = null;

        $now = time();
        $url = $this->parseUrl($url);

        if($url["error"] !== null) {return $url;}

        $scheme = $url["scheme"]=="https" ? "ssl://" : "";

        $fp = fsockopen($scheme.$url["host"], $url["port"] , $errno, $errstr, $timeout);	

      if (!$fp)
      {
        if($scheme == "ssl://")
        {
          $fp = fsockopen($url["host"], 80 , $errno, $errstr, $timeout);
          if (!$fp)
          {
            error_reporting($e);
            return array("error"=> __("Can't connect to server")." [$errno]");
          }
        }
        else
        {
          error_reporting($e);
          return array("error"=>__("Can't connect to server")." [$errno]");
        }
      }

      $out  = $verb." ".$url["path"].$url["query"]." HTTP/1.1\r\n";
      $out .= "Host: ". $url["host"] . "\r\n";
      $out .= "Connection: Close\r\n";
      $out .= "Accept-Encoding: identity\r\n"; 
      if($verb == "POST") {$out .= "Content-Length: " . strlen($body) . "\r\n"; }   
      foreach ($headers as $name => $value) {$out .= $name .": " . $value . "\r\n";}    
      $out .= "\r\n";    
      if($verb == "POST") {$out .= $body;}    
      fwrite($fp, $out);
      fflush($fp);

      //print "<br>".str_replace("\r\n", "<br>", $out)."<br>";

      $status = self::HTTP_STATUS;
      $chunked = false;
      $lastChunk = "";
      $chunkLength = 0;

      while (!feof($fp))
      {
        $remaining = $timeout - (time() - $now);
        if($remaining < 0) {return array("error"=>__("Request timed out [1]."));}

        stream_set_timeout($fp, $remaining + 1);
        $data = fgets($fp, 4096);
        $info = stream_get_meta_data($fp);

        if ($info["timed_out"])
        {
          error_reporting($e);
          return array("error"=>__("Request timed out [2]."));
        }

        //print($data."<br>");

        if($status == self::HTTP_STATUS)
        {
          //TODO: check status for 200, error on rest, eventually work arround 302 303
          $resultStatus = trim($data);
          $status = self::HTTP_HEADERS;
          continue;
        }

        if($status == self::HTTP_HEADERS)
        {
          if($data == "\r\n")
          {
            if($chunked) {
              $status = self::HTTP_CHUNK_HEADER;
            }
            else {$status = self::HTTP_BODY;}
            
            continue;
          }

          $data = trim($data);    		
          $separator = strpos($data, ": ");

          if(($separator === false)||($separator == 0) || ($separator >= (strlen($data) -2))) {
            return array("error"=>__("Invalid HTTP response header."));
          }

          $name = substr($data, 0, $separator);
          $value  = substr($data, $separator + 2);
          if(strcasecmp("Set-Cookie", $name) == 0)
          {
            $result["cookie"] = $value;
            continue;
          }
          if(strcasecmp("Content-Length", $name) == 0)
          {
            $result["length"] = $value + 0;
            continue;
          }
          if((strcasecmp("Transfer-Encoding", $name) == 0) && (strpos($value, 'chunked') !== false) )
          {
            $chunked = true;
            continue;
          }
          continue;
        }

        if($status == self::HTTP_CHUNK_HEADER)
        {
          $data = trim($data);
          $sc = strpos($data, ';');
          if($sc !== false) {$data = substr($data, 0, $sc);}
          $chunkLength = hexdec($data);
          if($chunkLength == 0) {
            break;
          }
          $lastChunk = "";
          $status = self::HTTP_CHUNK_BODY;
          continue;
        }

        if($status == self::HTTP_CHUNK_BODY)
        {
          $lastChunk .= $data;
          if(strlen($lastChunk) >= $chunkLength)
          {
            $result["body"] .= substr($lastChunk, 0, $chunkLength);
            $status = self::HTTP_CHUNK_HEADER;
          }
          continue;
        }

        if($status == self::HTTP_BODY)
        {
          $result["body"] .= $data;
          if(($result["length"] !== null) && (strlen($result["body"]) >= $result["length"])) {
            break;
          }
          continue;
        }
      }
      fclose($fp);

      if(($result["length"] !== null) && (strlen($result["body"]) != $result["length"])) {
        array("error"=>__("Invalid HTTP body length."));
      }

      error_reporting($e);
      return $result;
    }

    function jsonHttpRequest($url, $data, $timeout = 10)
    {
        $body = json_encode($data);
        $headers = array("Content-type" => "application/json");

      $cookie = '';
      $option_cookie = get_option("WSD-COOKIE");
      if($option_cookie !== false) {$cookie = $option_cookie;}

      $token = get_option("WSD-TOKEN");
      if($token !== false)
      {
        if($cookie != ''){ $cookie .= '; ';}
        $cookie .= "token=".$token;
      }

      if($cookie != '') {
        $headers["Cookie"] = $cookie;
      }

        $result = $this->httpRequest("POST", $url, $body, $headers, $timeout);

      if($result["cookie"] !== null)
      {
        if($option_cookie === false) {
          add_option("WSD-COOKIE", $result["cookie"]);
        }
        else {update_option("WSD-COOKIE", $result["cookie"]);}
      }

      if($result["error"] === null)
      {
        $decoded = json_decode($result["body"], true);
        if($decoded == null) {$result["error"] = __("Invalid JSON response.").$result["body"];}
        $result["body"] = $decoded;
      }
        return $result;
    }

    function jsonRPC($url, $method, $params, $timeout = 10)
    {
      $GLOBALS['wsd_last_err'] = array('code'=>0, 'message'=>'');
      $id = rand(1,100);

      $token = get_option("WSD-TOKEN");
      if($token === false) {
        $request = array("jsonrpc"=>"2.0", "id"=>$id, "method"=>$method, "params"=>$params);
      }
      else {$request = array("jsonrpc"=>"2.0", "id"=>$id, "method"=>$method, "params"=>$params, "token"=>$token);}

        $response = $this->jsonHttpRequest($url, $request, $timeout);

//print("request:"); print_r($request); print("<hr>"); print("response:"); print_r($response); print("<hr>");

      if($response["error"] !== null)
      {
        $GLOBALS['wsd_last_err'] = array("code" => 0, "message" => $response["error"]);
        return null;
      }

      if((! array_key_exists("id", $response["body"])) || ($response["body"]["id"] != $id) )
      {
        $GLOBALS['wsd_last_err'] = array("code" => 0, "message" =>  __("Invalid JSONRPC response [0]."));
        return null;
      }

      if( array_key_exists("token", $response["body"]))
      {
        if($token === false) {add_option("WSD-TOKEN", $response["body"]['token']);}
        else {update_option("WSD-TOKEN", $response["body"]['token']);}
      }

      if(array_key_exists("error", $response["body"]))
      {
        $GLOBALS['wsd_last_err'] = $response["body"]["error"];
        return null;
      }

      if(! array_key_exists("result", $response["body"]))
      {
        $GLOBALS['wsd_last_err'] = array("code" => 0, "message" => __("Invalid JSONRPC response [1]."));
        return null;
      }

      return $response["body"]["result"];
    }

    // ========================= RENDER UI ===========================================================

    function render_error($custom_message = null)
    {
      $html = '';
      
      if ($custom_message === null) {
        $html = '<p class="wsd-error-summary">' . $GLOBALS['wsd_last_err']['message'];
      }
      else {$html = '<p class="wsd-error-summary">' . $custom_message;}
      
        $html .= '<br /><span class="wsd-error-summary-detail">';
            $html .= __('If the problem persists please continue at <a href="https://dashboard.websitedefender.com" target="_blank">Website Defender</a>.');
        $html .='</span>';
      $html .= '</p>';

      echo $html;
    }

    function render_agent_install_issues($message)
    {
      //echo "render_agent_install_issues<br>";
      $html = '<p class="wsd-error-summary">' . $message;
        $html .= '<br /><span class="wsd-error-summary-detail">';
            $html .= __('It has to be installed manually from the <a href="https://dashboard.websitedefender.com" target="_blank">WebsiteDefender dashboard</a>.');
        $html .= '</span>';
      $html .= '</p>';

      echo $html;
    }

    function render_user_login($error = '')
    {
		if(empty($error))
		{
			echo '<div class="wsd-inside">';
				echo acxUtil::loadTemplate('wsd-login-form');
			echo '</div>';
		}
		else {
			$this->render_error($error);
		}
    }

    function render_new_user($error = '')
    {
		$form = $this->jsonRPC(self::WSD_URL_RPC, "cPlugin.getfrm", $this->site_url());
		if ($form === null)
		{
			$this->render_error();
			return;
		}
		$recaptcha_publickey = $form['captcha'];
		if(empty($recaptcha_publickey))
		{
			$this->render_error(__('Invalid server response.'));
			return;
		}
		//@ Display form
		echo '<div class="wsd-inside">';
        
			echo acxUtil::loadTemplate('wsd-register-form', array('acxWsd' => $this, 'error' => $error, 'recaptcha_publickey' => $recaptcha_publickey));

			echo '<br/>';

            $this->render_user_login();

		echo '</div>';
    }


    function process_login()
    {
        $email = isset($_POST['wsd_login_form_email']) ? $_POST['wsd_login_form_email'] : null;
        $password = isset($_POST['wsd_login_form_password']) ? $password = $_POST['wsd_login_form_password'] : null;

        if (empty($email)) {
            $this->render_user_login(__('Email address is required.'));
            return;
        }

        if (empty($password)) {
            $this->render_user_login(__('Password is required.'));
            return;
        }

        // $password is received as MD5 hash
        $login = $this->jsonRPC(self::WSD_URL_RPC, "cUser.login", array($email, $password));

        if ($login == null) {
            $this->render_user_login(__('Invalid login'));
            return;
        }

        $user = get_option("WSD-USER");
        if ($user === false) {
            add_option("WSD-USER", $email);
        }
        else {update_option("WSD-USER", $email);}

        $this->add_or_process_target();
    }

    function render_add_target_id()
    {
		if(empty($error))
		{
			echo '<div class="wsd-inside">';
				echo acxUtil::loadTemplate('wsd-targetid-form');
			echo '</div>';
		}
		else {
			$this->render_error($error);
		}
    }

    function process_add_target_id()
    {
		//echo "process_add_target_id<br>";
		add_option('WSD-TARGETID', $_POST['targetid']);
		$this->render_target_status();
    }

    function add_or_process_target()
    {
      //check if we already registered
      $targetid = get_option('WSD-TARGETID');

      if($targetid !== false)
      {
        $this->render_target_status();
        return;
      }
      else
      {
        //check first is this url is already there
        $target = $this->jsonRPC(self::WSD_URL_RPC, "cPlugin.urlstatus", $this->site_url());
        if($target === null)
        {
          $this->render_error();
          return;
        }
        if(array_key_exists('id', $target) && ($target['id'] != null))
        {
          if($targetid === false) {add_option('WSD-TARGETID', $target['id']);}
          else {update_option('WSD-TARGETID', $target['id']);}
          $this->render_target_status();
          return;
        }
      }  

      //the target was not there so we have to register a new one
      $newtarget = $this->jsonRPC(self::WSD_URL_RPC, "cTargets.add", $this->site_url());
      if($newtarget === null)
      {
        if($GLOBALS['wsd_last_err']['code'] == self::WSD_ERROR_LIMITATION)
        {
          $this->render_error(__("This account reached the maximum number of targets."));
          return;
        }
        if($GLOBALS['wsd_last_err']['code'] == self::WSD_ERROR_WPP_URL_EXIST)
        {
          $this->render_add_target_id();
          return;
        }
        print_r($GLOBALS['wsd_last_err']);
        return;
      }

      if(!array_key_exists("id", $newtarget))
      {
        $this->render_error(__("Invalid WSD response received."));
        return;
      }

      delete_option('WSD-TARGETID');
      add_option('WSD-TARGETID', $newtarget['id']);

      //download agent
      $targetInstalError = '';

      $headers = array("a"=>"a");
      $option_cookie = get_option("WSD-COOKIE");
      if($option_cookie !== false) $headers["Cookie"] = $option_cookie;

      //print "<br>Downloading: ". WSD_URL_DOWN.'?id='.$newtarget['id'] ."#". print_r($headers, true). "<br>";

      $agent = $this->httpRequest("GET", self::WSD_URL_DOWN.'?id='.$newtarget['id'], "", $headers);

      $_e = __('WebsiteDefender Agent failed to be copied automatically. Please <a href="http://www.websitedefender.com/faq/agent-installation-failure/" target="_blank">read</a> the following for further instructions on how to copy it manually.');  

      if($agent["error"] !== null) {
        $targetInstalError = $_e; //can't download
      }
      else
      {
        //try to copy the target
        $agentURL = $agent["sensor_url"];
        if(preg_match('/[a-f0-9]{40}.php/', $newtarget["sensor_url"], $matches))
        {
          $path = rtrim(ABSPATH, '/');
          $path .= '/'.$matches[0];

          $r = file_put_contents($path, $agent['body']);
          if(!$r) {$targetInstalError = $_e; } /* can't save */
        }
        else {$targetInstalError = $_e;} /* other */
      }

      //test the agent, this will triger agentless if agent not functioning
      $testTarget = $this->jsonRPC(self::WSD_URL_RPC, "cTargets.agenttest", $newtarget['id']);  
      $enbableTarget = $this->jsonRPC(self::WSD_URL_RPC, "cTargets.enable", array($newtarget['id'], true));

      if($targetInstalError != '') {$this->render_agent_install_issues($targetInstalError);}

      $this->render_target_status();  
    }

    function process_new_user_form()
    {
      //print "process_new_user_form<br>";

        $email = $_POST['wsd_new_user_email'];
        $name = $_POST['wsd_new_user_name'];
        $surname = $_POST['wsd_new_user_surname'];
        $password	= $_POST['wsd_new_user_password'];
        $password_re = $_POST['wsd_new_user_password_re'];

        if (empty($email)) {
            $this->render_new_user(__('Email is required.'));
            return;
        }
        if (empty($name)) {
            $this->render_new_user(__('Name is required.'));
            return;
        }
        if (empty($surname)) {
            $this->render_new_user(__('Surname is required.'));
            return;
        }
        if (empty($password)) {
            $this->render_new_user(__('Password is required.'));
            return;
        }
        if ($password != $password_re) {
            $this->render_new_user(__('Passwords do not match.'));
            return;
        }

        $register = $this->jsonRPC(self::WSD_URL_RPC, "cPlugin.register",
                              array(
                                    array("challenge"=>$_POST['recaptcha_challenge_field'],
                                          "response"=>$_POST['recaptcha_response_field']),
                                    array(
                                          "url" => $this->site_url(),
                                          "email" => $email,
                                          "name" => $name,
                                          "surname" => $surname,
                                        /* the password coming from the client already as a hash */
                                          "pass" => $password,
                                          "source" => self::WSD_SOURCE
                                          )
                                    ));
      if($register == null)
      {
        if($GLOBALS['wsd_last_err']['code'] == self::WSD_ERROR_WPP_INVALID_CAPTCHA)
        {
          $this->render_new_user(__('Invalid captcha. Please try again.'));
          return;
        }
        if($GLOBALS['wsd_last_err']['code'] == self::WSD_ERROR_WPP_USER_EXIST)
        {
          $this->render_new_user(__("This user is already registered. To continue with this user, please use the login form above or register with a new user name."));
          return;
        }
        $this->render_new_user(__('Registration failed! Please try again.'));
        return;
      }
      $user = get_option("WSD-USER");
      if($user === false) {
          add_option("WSD-USER", $email);
      } 
      else {update_option("WSD-USER", $email);}
      
      $this->add_or_process_target();
    }

    function render_target_status()
    {
      #echo "render_target_status<br>";
      $user = get_option('WSD-USER');
      if(!is_string($user)||($user == "") ) { $user = get_option("admin_email"); } 
      $status = $this->jsonRPC(self::WSD_URL_RPC, "cPlugin.status", array($user, get_option('WSD-TARGETID')));
      if($status === null)
      {
        $this->render_error();
        return;  
      }
      if((!array_key_exists('active', $status)) || ($status['active'] !== 1))
      {
        //our target is not valid anymore
        delete_option('WSD-TARGETID');
        return false;
      }

      echo '<p class="wsd-inside">';
        echo __('Thank you for registering with WebsiteDefender. 
                    Please navigate to the <a target="_blank" href="https://dashboard.websitedefender.com/">WebsiteDefender dashboard</a> 
                    to monitor your site\'s security.');
      echo "</p>";

      $enabled = array_key_exists('enabled', $status) ? $status['enabled'] : null;
      $scanned = array_key_exists('scanned', $status) ? $status['scanned'] : null;
      $agentless = array_key_exists('agentless', $status) ? $status['agentless'] : null;

      if (!is_numeric($enabled) || !is_numeric($scanned) || !is_numeric($agentless))
      {
          $this->render_error(__('Invalid server response.'));
          return;
      }
      $enabled = intval($enabled);
      $scanned = intval($scanned);
      $agentless = intval($agentless);
      ?>
<div id="wsd-target-status-holder" class="wsd-inside">
    <p class="wsd-target-status-title"><?php echo __('Website status on Website Defender');?></p>
    <div class="wsd-target-status-section">
        <?php
			$statusText = (($enabled == 1) ? __('YES') : __('NO'));

			echo '<span class="wsd-target-status-section-label">'.__('Enabled').': </span>',
				 '<span class="wsd-target-status-section-', $enabled ? 'enabled' : 'disabled', '">', $statusText, '</span>';
		?>
    </div>
    <div class="wsd-target-status-section">
        <?php
			$statusText = (($scanned == 1) ? __('YES') : __('NO'));

			echo '<span class="wsd-target-status-section-label">'.__('Scanned').': </span>',
				 '<span class="wsd-target-status-section-', $scanned ? 'enabled' : 'disabled', '">', $statusText, '</span>';
		?>
    </div>
    <div class="wsd-target-status-section">
        <?php
			$statusText = (($agentless == 1) ?  __('DOWN') :  __('UP'));

			echo '<span class="wsd-target-status-section-label">'.__('Agent status').': </span>',
				 '<span class="wsd-target-status-section-', $agentless ? 'disabled' : 'enabled', '">', $statusText, '</span>';
		?>
    </div>
</div>
<?php return true; }

    function render_main()
    {
      if(1==0)
      {
        delete_option('WSD-TARGETID');
        delete_option("WSD-COOKIE");
        delete_option("WSD-USER");
        return;
      }

      if(isset($_POST['wsd-new-user']))
      {
        $this->process_new_user_form();
        return;
      }

      if(isset($_POST['wsd-login']))
      {
        $this->process_login();
        return;
      }

      if(isset($_POST['wsd_update_target_id']))
      {
        $this->process_add_target_id();
        return;
      }

      $targetid = get_option("WSD-TARGETID");
      if($targetid !== false)
      {
        $this->render_target_status();
        return;
      }

      $hello = $this->jsonRPC(self::WSD_URL_RPC, "cPlugin.hello", $this->site_url());
      if($hello == null)
      {
        $this->render_error();
        return;
      }

      if($hello == 'registered')
      {
        $this->render_add_target_id();
        return;
      }
      elseif($hello == 'new')
      {
        //$user = get_option("WSD-USER"); if($user === false)
        $this->render_new_user();
        //else render_user_login();
      }
      else
      {
        $this->render_error(__("Invalid server response."));
        return;
      }
    }

}
/* End of file: acxWSD.php */
