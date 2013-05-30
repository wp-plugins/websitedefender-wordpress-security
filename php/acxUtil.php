<?php
    //@@ require a valid request
if (!defined('ACX_PLUGIN_NAME')) { exit; }
    //@@ Only load in the plug-in pages
if (!ACX_SHOULD_LOAD) { return; }

/**
 * Static class providing utility methods
 *
 * @package ACX
 * @since v0.1
 * @author {c}
 */
class acxUtil
{
/*
 * PRIVATE METHODS
 * ===================================================
 */
    private function __clone(){}
    private function __sleep() {}
    private function __wakeup() {}
    private function __construct(){}

    /**
     * @privates
     * @static
     * @since v0.1
     * @see acxUtil::hideWpVersionBackend(), acxUtil::hideWpVersionFrontend()
     *
     * Check to see whether or not the WP version has been hidden from non-admins.
     * Defaults to false.
     *
     * @var boolean
     */
    private static $_isVersionHidden = false;

    private static $_pluginID = 'acx_plugin_dashboard_widget';


    /*
     * PUBLIC METHODS
     * ===================================================
     */
    /**
     * @public
     * @static
     * @since v0.1
     * @uses wp_die()
     *
     * Check the specified file name for directory traversal attacks.
     * Exits the script if the "..[/]" is found in the $fileName.
     *
     * @param string $fileName The name of the file to check
     * @return void
     */
    public static function checkFileName($fileName)
    {
        $fileName = trim($fileName);

        //@@ Check for directory traversal attacks
        if (preg_match("/\.\.\//",$fileName)) {
            wp_die('Invalid Request!');
        }
    }

    /**
     * @public
     * @static
     * @since v0.1
     * @uses acxUtil::checkFileName()
     *
     * Retrieve the content of the specified template file.
     *
     * @param type $fileName the name of the template file to load.
     * Without the ".php" file extension.
     * @param array $data The data to send to the template file
     * @return string The parsed content of the template file
     */
    public static function loadTemplate($fileName, array $data = array())
    {
        self::checkFileName($fileName);

        $str = '';
        $file = ACX_PLUGIN_DIR.'tpl/'.$fileName.'.php';
        if (is_file($file))
        {
            ob_start();
                if (!empty($data)) {
                    extract($data);
                }
                include($file);
                $str = ob_get_contents();
            ob_end_clean();
        }

        return $str;
    }

    /**
     * @public
     * @static
     * @since v0.1
     * @uses acxUtil::checkFileName()
     *
     * Retrieve the content of the specified page file.
     *
     * @param type $fileName the name of the page file to load.
     * Without the ".php" file extension.
     * @return string The parsed content of the page file
     */
    public static function loadPage($fileName)
    {
        self::checkFileName($fileName);

        $str = '';
        $file = ACX_PLUGIN_DIR.'pages/'.$fileName.'.php';
        if (is_file($file))
        {
            ob_start();
                if (!empty($data)) {
                    extract($data);
                }
                include($file);
                $str = ob_get_contents();
            ob_end_clean();
        }

        return $str;
    }

	/**
     * @public
     * @static
     * @since v0.1
	 * @uses acxUtl::canWriteToFile()
     *
	 * Attempts to write the provided $data into the specified $file
	 * using either file_put_contents or fopen/fwrite functions (whichever is available).
	 *
	 * @param  string $file The path to the file
	 * @param string $data The content to write into the file
	 *
	 * @return int  The number of bytes written to the file, otherwise -1.
	 */
	public static function writeFile($file, $data)
	{
		if (!self::canWriteToFile())
		{
			return -1;
		}

		if (function_exists('file_put_contents')) {
			return file_put_contents($file,$data);
		}
		else
		{
			if (function_exists('fopen'))
			{
				$h = fopen($file,'w');
				if (!is_resource($h)) {
					return -1;
				}
				else {
					fwrite($h,$data);
					fclose($h);
					return strlen($data);
				}
			}
		}
		return -1;
	}

    /**
     * @public
     * @static
     * @since v0.1
	 *
	 * Check to see whether or not we can write into a file using either
	 * file_put_contents or fopen/fwrite functions.
     *
     * @return boolean
     */
	public static function canWriteToFile()
	{
		return (function_exists('file_put_contents') || function_exists('fopen'));
	}

    /**
     * @public
     * @static
     * @since v0.1
     * @global ACX_BLOG_FEED, $wpdb
	 *
	 * Retrieve the rights the current used user to connect to the database server has.
     *
     * @return array  array('rightsEnough' => true|false, 'rightsTooMuch' => true|false);
     */
	public static function getDatabaseUserAccessRights()
	{
    	global $wpdb;

		$rightsenough = $rightstoomuch = false;
		$data = array(
			'rightsEnough' => $rightsenough,
			'rightsTooMuch' => $rightstoomuch
		);

//@ $rev #1 07/26/2011 {cos}
		$rights = $wpdb->get_results("SHOW GRANTS FOR '".DB_USER."'@'".DB_HOST."'", ARRAY_N);

		if (empty($rights)) {
			return $data;
		}

		$to = preg_quote("TO '".DB_USER."'@'".DB_HOST."'");

        foreach ($rights as $right)
        {
            if (!empty($right[0]))
            {
                //@ If GRANT ALL
                if (preg_match("/\bALL PRIVILEGES\b(.*)".$to."/msiU", $right[0]))
                {
                    $rightsenough = $rightstoomuch = true;
                    break;
                }
                //@ IF ALTER
                else if (preg_match("/\bALTER(\s+)[^a-z],\b".$to."/msiU", $right[0]))
                {
                    $rightsenough = true;
                    break;
                }
            }
        }

		return array(
			'rightsEnough' => $rightsenough,
			'rightsTooMuch' => $rightstoomuch,
		);
	}


/*
 * COMMON
 * ===================================================
 */

    /**
     * @public
     * @static
     * @since v0.1
     * @global ACX_BLOG_FEED
     *
     * Retrieve and display a list of links for an existing RSS feed, limiting the selection to the 5 most recent items.
	 *
	 * @return void
     */
    public static function displayDashboardWidget()
    {
        // @since v2.0.6
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            $opt = get_option('WSD-RSS-WGT-DISPLAY');
            if (empty($opt)) {
                add_option('WSD-RSS-WGT-DISPLAY', 'no');
            }
            else {
                update_option('WSD-RSS-WGT-DISPLAY', 'no');
            }
            self::_hideDashboardWidget();
            return;
        }

        //@ flag
        $run = false;

        //@ check cache
        $optData = get_option('wsd_feed_data');
        if (! empty($optData))
        {
            if (is_object($optData))
            {

                $lastUpdateTime = @$optData->expires;
                // invalid cache
                if (empty($lastUpdateTime)) { $run = true; }
                else
                {
                    $nextUpdateTime = $lastUpdateTime+(24*60*60);
                    if ($nextUpdateTime >= $lastUpdateTime)
                    {
                        $data = @$optData->data;
                        if (empty($data)) { $run = true; }
                        else {
                            // still a valid cache
                            echo $data;
                            return;
                        }
                    }
                    else { $run = true; }
                }
            }
            else { $run = true; }
        }
        else { $run = true; }

        if (!$run) { return; }

        $rss = fetch_feed(ACX_BLOG_FEED);

        $out = '';
        if (is_wp_error( $rss ) )
        {
            $out = '<li>'.__('An error has occurred while trying to load the rss feed!').'</li>';
            echo $out;
            return;
        }
        else
        {
            // Limit to 5 entries.
            $maxitems = $rss->get_item_quantity(5);

            // Build an array of all the items,
            $rss_items = $rss->get_items(0, $maxitems);

            $out .= '<ul>';
            if ($maxitems == 0)
            {
                $out.= '<li>'.__('There are no entries for this rss feed!').'</li>';
            }
            else
            {
                foreach ( $rss_items as $item ) :
                    $url = esc_url($item->get_permalink());
                    $out.= '<li>';
                    $out.= '<h4><a href="'.$url.'" target="_blank" title="Posted on '.$item->get_date('F j, Y | g:i a').'">';
                    $out.= esc_html( $item->get_title() );
                    $out.= '</a></h4>';
                    $out.= '<p>';
                    $d = esc_html( $item->get_description());
                    $p = substr($d, 0, 115).' <a href="'.$url.'" target="_blank" title="Read all article">[...]</a>';
                    $out.= $p;
                    $out.= '</p>';
                    $out.= '</li>';
                endforeach;
            }
            $out.= '</ul>';

            $path = trailingslashit(get_option('siteurl')).'wp-content/plugins/secure-wordpress/';

            $out .= '<div style="border-top: solid 1px #ccc; margin-top: 4px; padding: 2px 0;">';
            $out .= '<p style="margin: 5px 0 0 0; padding: 0 0; line-height: normal; overflow: hidden;">';
            $out .= '<a href="http://feeds.feedburner.com/Websitedefendercom"
                                style="float: left; display: block; width: 50%; text-align: right; margin-left: 30px;
                                padding-right: 22px; background: url('.$path.'img/rss.png) no-repeat right center;"
                                target="_blank">Follow us on RSS</a>';
            $out .= '<a href="#" id="wsd_close_rss_widget"
                                style="float: right; display: block; width: 16px; height: 16px;
                                margin: 0 0; background: url('.$path.'img/close-button.png) no-repeat 0 0;"
                                    title="Close widget"></a><form id="wsd_form" method="post"></form>';
            $out .= '</p>';
            $out .= '<script type="text/javascript">
                    document.getElementById("wsd_close_rss_widget").onclick = function(){
                            document.getElementById("wsd_form").submit();
                        };
                </script>';
            $out .= '</div>';
        }

        // Update cache
        $obj = new stdClass();
        $obj->expires = time();
        global $wpdb;
        $obj->data = $wpdb->prepare($out);
        update_option('wsd_feed_data', $obj);

        echo $out;
    }

    /**
     * @public
     * @static
     * @since v0.1
     *
     * Add the rss widget to dashboard
	 *
     * @return void
     */
    public static function addDashboardWidget()
    {
        wp_add_dashboard_widget('acx_plugin_dashboard_widget', __('WebsiteDefender news and updates'), 'acxUtil::displayDashboardWidget');
    }
    /**
     * Hide the dashboard rss widget
     * @static
     * @public
     * @since v2.0.6
     */
    public static function _hideDashboardWidget()
    {
        echo '<script>document.getElementById("'.self::$_pluginID.'").style.display = "none";</script>';
    }


/*
 * SECURITY
 * ===================================================
 */

    /**
     * @public
     * @static
     * @since v0.1
     *
     * Replaces the WP version on the front-end with a random generated number.
	 *
     * @return void
     */
    public static function hideWpVersionFrontend()
    {
        //@@ on the front-end
        if (!is_admin())
        {
            global $wp_version, $wp_db_version, $manifest_version, $tinymce_version;

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

            self::$_isVersionHidden = true;
        }
    }

    /**
     * @public
     * @static
     * @since v0.1
     *
	 * Removes various meta tags generators from the blog's head tag.
	 *
     * @return void
     */
	public static function removeWpMetaGeneratorsFrontend()
	{
		if (!is_admin())
		{
			//@@ remove various meta tags generators from blog's head tag
			function acx_filter_generator($gen, $type)
			{
				switch ( $type ) {
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
			foreach ( array( 'html', 'xhtml', 'atom', 'rss2', 'rdf', 'comment' ) as $type ) :
				add_filter( "get_the_generator_".$type, 'acx_filter_generator', 10, 2 );
			endforeach;
		}
	}

	/**
     * @public
     * @static
     * @since v0.1
     *
     * Hide WP version on dashboard from users that cannot update plug-ins/core.
     *
     * @return void
	*/
	public static function hideWpVersionBackend()
	{
		if (is_admin() && !user_can(wp_get_current_user(),'update_plugins'))
		{
			wp_enqueue_script('remove-wp-version', ACX_PLUGIN_PATH.'res/js/remove_wp_version.js', array('jquery'));
			remove_action( 'update_footer', 'core_update_footer' );

			self::$_isVersionHidden = true;
		}
	}

    /**
     * @public
     * @static
     * @since v0.1
     *
     * Disable error reporting
     *
     * @return void
     */
    public static function disableErrorReporting()
    {
        @error_reporting(0);
        @ini_set('display_errors','off');
		@ini_set('display_startup_errors', 0);

		global $wpdb;

		$wpdb->hide_errors();
		$wpdb->suppress_errors();
    }

    /**
     * @public
     * @static
     * @since v0.1
     *
     * Load the text domain
	 *
     * @return void
     */
    public static function loadTextDomain()
    {
        if ( function_exists('load_plugin_textdomain') ) {
            load_plugin_textdomain(ACX_TEXT_DOMAIN, false, ACX_PLUGIN_DIR.'languages/');
        }
    }

    /**
     * @public
     * @static
     * @since v0.1
     *
     * Load css and js resources
	 *
     * @return void
     */
	public static function loadResources()
	{
		wp_enqueue_script('jquery');

        //@ for dashboard
    	wp_enqueue_style('acx-wp-dashboard', ACX_PLUGIN_PATH.'res/css/acx-wp-dashboard.css');

        if (ACX_SHOULD_LOAD)
		{
			//@ Only in back-end in the plug-in's pages
			wp_enqueue_style('acx-wsd', ACX_PLUGIN_PATH.'res/css/acx-wsd.css');
			wp_enqueue_style('acx-styles', ACX_PLUGIN_PATH.'res/css/acx-styles.css');
        }
	}

    /**
     * @public
     * @static
     * @since v0.1
     *
     * Adds the plug-in's credits in the admin footer
	 *
     * @return void
     */
	public static function addPluginInfoFooter()
	{
		if(ACX_SHOULD_LOAD)
		{
			echo '<p>';
			$plugin_data = get_plugin_data(ACX_PLUGIN_DIR.ACX_PLUGIN_NAME.'.php');
			printf('%1$s plugin | ' . __('Version') . ' <a href="http://wordpress.org/extend/plugins/websitedefender-wordpress-security/changelog/"
															target="_blank"
															title="'.__('History').'">%2$s</a> | '.__('Author').' %3$s<br />'
					, $plugin_data['Title'], $plugin_data['Version'], $plugin_data['Author']);
			echo '</p>';
		}
	}

    /**
     * @public
     * @static
     * @since v0.1
     *
     * Removes Really Smple Discovery meta tags from front-end
	 *
     * @return void
     */
	public static function removeReallySimpleDiscovery()
	{
		if (!is_admin() && function_exists('rsd_link')) {
			remove_action('wp_head', 'rsd_link');
		}
	}

    /**
     * @public
     * @static
     * @since v0.1
     *
     * Removes Windows Live Writer meta tags from front-end
	 *
     * @return void
     */
	public static function removeWindowsLiveWriter()
	{
		if (!is_admin() && function_exists('wlwmanifest_link')) {
			remove_action('wp_head', 'wlwmanifest_link');
		}
	}

    /**
     * @public
     * @static
     * @since v0.1
     *
     * Removes core update notifications from back-end
	 *
     * @return void
     */
	public static function removeCoreUpdateNotification()
	{
		if (!user_can(wp_get_current_user(),'update_plugins'))
		{
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
		}
	}

    /**
     * @public
     * @static
     * @since v0.1
     *
     * Removes plug-ins update notifications from back-end
	 *
     * @return void
     */
	public static function removePluginUpdateNotifications()
	{
		if (!user_can(wp_get_current_user(),'update_plugins'))
		{
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
		}
	}

    /**
     * @public
     * @static
     * @since v0.1
     *
     * Removes themes update notifications from back-end
	 *
     * @return void
     */
	public static function removeThemeUpdateNotifications()
	{
		if (!user_can(wp_get_current_user(),'edit_themess'))
		{
			remove_action( 'load-themes.php', 'wp_update_themes' );
			remove_action( 'load-update.php', 'wp_update_themes' );
			remove_action( 'admin_init', '_maybe_update_themes' );
			remove_action( 'wp_update_themes', 'wp_update_themes' );
			// 3.0
			remove_action( 'load-update-core.php', 'wp_update_themes' );
			add_filter( 'pre_transient_update_themes', create_function( '$a', "return null;" ) );
		}
	}

    /**
     * @public
     * @static
     * @since v0.1
     *
     * Removes login error notifications from front-end
	 *
     * @return void
     */
	public static function removeErrorNotificationsFrontEnd()
	{
		$str = '<link rel="stylesheet" type="text/css" href="'.ACX_PLUGIN_PATH.'res/css/acx-styles-extra.css"/>';
		add_action('login_head', create_function('$a', "echo '{$str}';"));
		add_filter('login_errors', create_function('$a', "return null;"));
	}

    /**
     * @public
     * @static
     * @since v0.1
     *
     * Tries to reates the index.php file in the wp-content, wp-content/plugins and wp-content/themes directories to prevent directory listing
	 *
     * @return void
     */
	public static function preventWpContentDirectoryListing()
	{
		$data = '<?php exit;?>';

		$baseDir = trailingslashit(WP_CONTENT_DIR);
		$pluginsDir = $baseDir.'plugins';
		$themesDir = $baseDir.'themes';


		if (is_writable($baseDir))
		{
			$file = $baseDir.'index.php';
			if (!is_file($file))
			{
				self::writeFile($file,$data);
				@chmod($file,'0644');
			}
		}

		if (is_writable($pluginsDir))
		{
			$file = $pluginsDir.'/index.php';
			if (!is_file($file))
			{
				self::writeFile($file,$data);
				@chmod($file,'0644');
			}
		}

		if (is_writable($themesDir))
		{
			$file = $themesDir.'/index.php';
			if (!is_file($file))
			{
				self::writeFile($file,$data);
				@chmod($file,'0644');
			}
		}
	}

	/**
     * @public
     * @static
     * @since v0.1
     *
	 * Removes the version parameter from urls
	 *
	 * @param  string $src Original script URI
	 * @return string
	 */
	public static function removeWpVersionFromLinks($src)
	{
		global $wp_version;

		// Just the URI without the query string.
		$src = preg_replace("/\?ver=(.*)/mi", '', $src);

		return $src;
	}

	/**
     * @public
     * @static
     * @since v0.1
     *
	 * Hide admin notifications for non admins.
	 *
	 * @return void
	 */
	public static function hideAdminNotifications()
	{
		if (!user_can(wp_get_current_user(),'update_plugins'))
		{
			add_action('init', create_function('$a', "remove_action('init', 'wp_version_check');"), 2);
			add_filter('pre_option_update_core', create_function('$a', "return null;"));
		}
	}


/*
*	INFO
*=================================================
*/
	//@@ 11.a
	public static function getCurrentVersionInfo()
	{
		$c = get_site_transient( 'update_core' );
		if ( is_object($c))
		{
			if (empty($c->updates))
			{
				return '<span class="acx-icon-alert-success">'.__('You have the latest version of Wordpress.').'</span>';
			}

			if (!empty($c->updates[0]))
			{
				$c = $c->updates[0];

				if ( !isset($c->response) || 'latest' == $c->response ) {
					return '<span class="acx-icon-alert-success">'.__('You have the latest version of Wordpress.').'</span>';
				}

				if ('upgrade' == $c->response)
				{
					$lv = $c->current;
					$m = '<span class="acx-icon-alert-critical">'.sprintf('A new version of Wordpress <strong>(%s)</strong> is available. You should upgrade to the latest version.', $lv).'</span>';
					return __($m);
				}
			}
		}

		return '<span class="acx-icon-alert-critical">'.__('An error has occurred while trying to retrieve the status of your Wordpress version.').'</span>';
	}

	//@@ 11.b
	public static function getDatabasePrefixInfo()
	{
		global $table_prefix;

		if (strcasecmp('wp_', $table_prefix)==0) {
			return '<span class="acx-icon-alert-critical">'
                        .__('Your database prefix should not be <code>wp_</code>.')
                        .'(<a href="http://www.websitedefender.com/wordpress-security/wordpress-database-tables-prefix/" target="_blank">'.__('read more').'</a>)</span>';
		}

		return '<span class="acx-icon-alert-success">'.__('Your database prefix is not <code>wp_</code>.').'</span>';
	}

	//@@ 11.c
	public static function getWpVersionStatusInfo()
	{
		if (!self::$_isVersionHidden) {
			return '<span class="acx-icon-alert-success">'.__('The Wordpress version <code>is</code> hidden for all users but administrators.').'</span>';
		}
	}

	//@@ 11.d-1
    public static function getDbErrorStatusInfo()
    {
		global $wpdb;

		if ($wpdb->show_errors || !$wpdb->suppress_errors) {
			return '<span class="acx-icon-alert-critical">'.__('WP <code>displays</code> your database errors.').'</span>'.'<br/>';
		}

		return '<span class="acx-icon-alert-success">'.__('Database errors <code>are not</code> displayed.').'</span>'.'<br/>';
    }
	//@@ 11.d-2
    public static function getPhpErrorStatusInfo()
    {
		$de = strtolower(ini_get('display_errors'));
		if ($de == 'off') {
			return '<span class="acx-icon-alert-success">'.__('PHP errors <code>are not</code> displayed.').'</span>'.'<br/>';
		}

        return '<span class="acx-icon-alert-critical">'.__('PHP errors <code>are displayed</code>.').'</span>'.'<br/>';
    }
	//@@ 11.d-3
    public static function getPhpStartupErrorStatusInfo()
    {
		$dse = strtolower(ini_get('display_startup_errors'));
		if ($dse == 0) {
			return '<span class="acx-icon-alert-success">'.__('Startup errors <code>are not</code> displayed.').'</span><br/>';
		}

		return '<span class="acx-icon-alert-critical">'.__('Startup errors <code>are displayed</code>.').'</span>'.'<br/>';
    }


	//@@ 11.e
	public static function getAdminUsernameInfo()
	{
		global $wpdb;

		$u = $wpdb->get_var("SELECT `ID` FROM $wpdb->users WHERE user_login='admin';");

		if (empty($u)) {
			return '<span class="acx-icon-alert-success">'.__('User <code>admin</code> was not found.').'</span>';
		}

		return '<span class="acx-icon-alert-critical">'.__('User <code>admin</code> was found! You should change it in order to avoid user enumeration attacks.').'</span>';
	}

	//@@ 11.f
	public static function getWpAdminHtaccessInfo()
	{
		$file = trailingslashit(ABSPATH).'wp-admin/.htaccess';
		if (is_file($file)) {
			return '<span class="acx-icon-alert-success">'.__('The <code>.htaccess</code> file was found in the <code>wp-admin</code> directory.').'</span>';
		}

		return '<span class="acx-icon-alert-info">'
                .__('The <code>.htaccess</code> file was not found in the <code>wp-admin</code> directory.')
                .'(<a href="http://www.websitedefender.com/wordpress-security/htaccess-files-wordpress-security/" target="_blank">'.__('read more').'</a>)</span>';
	}

	//@@ 11.g
	public static function getDatabaseUserAccessRightsInfo()
	{
		$rights = self::getDatabaseUserAccessRights();
		$m = '';

        if (!$rights['rightsEnough']) {
            $m .= __('The User which is used to access your Wordpress Database, hasn\'t enough rights (is missing the <code>ALTER</code> right) to alter the Table structure.
                Please visit the <a href="http://www.websitedefender.com/category/faq/" target=_blank">WebsiteDefender WP Security Scan WordPress plugin documentation</a> website for more information.
                If the user <code>has ALTER</code> rights and the tool is still not working,
                please <a href="http://www.websitedefender.com/contact/" target="_blank">contact</a> us for assistance.');
        }
        if ($rights['rightsTooMuch']) {
            $m .= __("Your currently used User to access the Wordpress Database <code>holds too many rights</code>.
                We suggest that you limit his rights or to use another User with more limited rights instead, to increase your website's Security.");
        }

		return '<span class="acx-icon-alert-info">'.$m.'</span>';
	}

	//@@ 11.h-1 (c)
	public static function getWpContentIndexInfo()
	{
		if (is_file(trailingslashit(WP_CONTENT_DIR).'index.php')) {
			return '<span class="acx-icon-alert-success">'.__('The <code>index.php</code> file <code>was found</code> in the wp-content directory.').'</span>'.'<br/>';
		}

		return '<span class="acx-icon-alert-info">'.__('The <code>index.php</code> file <code>was not found</code> in the wp-content directory! You should create one in order to prevent directory listings.').'</span>'.'<br/>';
	}

	//@@ 11.h-2 (ce)
	public static function getWpContentPluginsIndexInfo()
	{
		if (is_file(trailingslashit(WP_CONTENT_DIR).'plugins/index.php')) {
			return '<span class="acx-icon-alert-success">'.__('The <code>index.php</code> file <code>was found</code> in the plugins directory.').'</span>'.'<br/>';
		}

		return '<span class="acx-icon-alert-info">'.acxt_t('The <code>index.php</code> file <code>was not found</code> in the plugins directory! You should create one in order to prevent directory listings.').'</span>'.'<br/>';
	}

	//@@ 11.h-3 (c)
	public static function getWpContentThemesIndexInfo()
	{
		if (is_file(trailingslashit(WP_CONTENT_DIR).'themes/index.php')) {
			return '<span class="acx-icon-alert-success">'.__('The <code>index.php</code> file <code>was found</code> in the themes directory.').'</span>'.'<br/>';
		}

		return '<span class="acx-icon-alert-info">'.__('The <code>index.php</code> file <code>was not found</code> in the themes directory! You should create one in order to prevent directory listings.').'</span>'.'<br/>';
	}

	//@@ 11.h-4 (c)
	public static function getWpContentUploadsIndexInfo()
	{
		if (is_file(trailingslashit(WP_CONTENT_DIR).'uploads/index.php')) {
			return '<span class="acx-icon-alert-success">'.__('The <code>index.php</code> file <code>was found</code> in the uploads directory.').'</span>'.'<br/>';
		}

		return '<span class="acx-icon-alert-info">'.__('The <code>index.php</code> file <code>was not found</code> in the uploads directory! You should create one in order to prevent directory listings.').'</span>'.'<br/>';
	}

	//@@ 11.h-5 (c)
    public static function getWpReadmeFileInfo()
    {
        //@ Try to change permissions on ./readme.html in order to hide the WP version
        $path = trailingslashit(ABSPATH).'readme.html';
        if (is_file($path))
        {
            // Get permissions
            $fpath = trailingslashit(ABSPATH).'readme.html';
            $url = trailingslashit(get_option('siteurl')).'readme.html';

            $fp = @substr(sprintf("%o", fileperms($fpath)), -4);
            $range = range('0400','0640');

            $m = sprintf(__('The <code>readme.html</code> file <code>was found</code> in the root directory (<a href="%s" target="_blank">view file</a>).'), $url);

            //@ bad
            if (!in_array($fp,$range))
            {
                $m .= ' '.__('It is very important to either delete this file or make it inaccessible (chmod <strong>0400</strong> or <strong>0440</strong>) from your browser as it displays your Wordpress version!');
                return '<span class="acx-icon-alert-critical">'.$m.'</span>'.'<br/>';
            }
            //@ safe
            else
            {
                $m .= ' '.__('Although the file was found proper file permissions are set in order to make it innaccessible from the browser window.');
                return '<span class="acx-icon-alert-success">'.$m.'</span>'.'<br/>';
            }
        }

    	return '<span class="acx-icon-alert-success">'.__('The <code>readme.html</code> file was <code>not found</code> in the root directory!').'</span>'.'<br/>';
    }


}
/* End of file: acxUtil.php */