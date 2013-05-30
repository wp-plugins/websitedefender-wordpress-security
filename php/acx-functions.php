<?php
    //@@ require a valid request
if (!defined('ACX_PLUGIN_NAME')) { exit; }
    //@@ Only load in the plug-in pages
if (!ACX_SHOULD_LOAD) { return; }

/*
 * Global functions. Require valid request.
 */
?>
<?php
/*
 * MENU
 * ==========================================================
 */
/** Displays the plug-in's dashboard page */
function _pageDashboard() { echo acxUtil::loadPage('dashboard'); }
/** Displays the plug-in's database page */
function _pageDatabase()  { echo acxUtil::loadPage('database'); }
/** Displays the plug-in's about page */
function _pageAbout()     { echo acxUtil::loadPage('about'); }

/** Creates the plug-in's admin menu */
function _acx_createAdminMenu()
{
    if (function_exists('add_menu_page'))
    {
        add_menu_page( __('WSD Security Dashboard'), __('WSD Security'), 'edit_pages', ACX_PREFIX, '_pageDashboard', ACX_PLUGIN_PATH.'res/images/wsd-logo-small-list.png');
            add_submenu_page(ACX_PREFIX, __('Database'), __('Database'), 'edit_pages', ACX_PREFIX.'database', '_pageDatabase');
            add_submenu_page(ACX_PREFIX, __('About WSD'), __('About WSD'), 'edit_pages', ACX_PREFIX.'about', '_pageAbout');
    }
}

/**
 * @public
 * @since v0.1
 *
 * Add the 'Settings' link to the plugin page
 *
 * @param array $links
 * @return array
 */
function acx_admin_plugin_actions($links) {
	$links[] = '<a href="admin.php?page='.ACX_PREFIX.'">'.__('Settings').'</a>';
	return $links;
}


/**
 * @public
 * @since v0.1
 * global array $acxFileList
 *
 * Apply the suggested permissions for the list of files
 * provided in the global $acxFileList array.
 *
 * @return array  array('success' => integer, 'failed' => integer)
 */
function acx_changeFilePermissions()
{
	global $acxFileList;

	if (empty($acxFileList)) {
		return array();
	}

	$s = $f = 0;

	foreach($acxFileList as $k => $v)
	{
		$filePath = $v['filePath'];
		$sp = $v['suggestedPermissions'];

		//@ include directories too
		if (file_exists($filePath))
        {
            $sp = (is_string($sp) ? octdec($sp) : $sp);
			if (@chmod($filePath, $sp)) {
				$s++;
			}
			else { $f++; }
		}
	}

	return array('success' => $s, 'failed' => $f);
}


if (!function_exists('make_seed')) :
	/**
	 * @public
	 * @since v0.1
	 *
	 * Create a number
	 *
	 * @return double
	 */
    function make_seed()
    {
        list($usec, $sec) = explode(' ', microtime());
        return (float)$sec + ((float)$usec * 100000);
    }
endif;

if (!function_exists('acx_getFilePermissions')) :
	/**
	 * @public
	 * @since v0.1
	 * @uses fileperms(), clearstatcache()
	 *
	 * Retrieve file permissions for the specified file.
	 *
	 * @return string the file permissions or empty string on error.
	 */
	function acx_getFilePermissions($filePath)
	{
		if (!function_exists('fileperms')) {
			return '-1';
		}

        if (!file_exists($filePath)) {
            return '-1';
        }

		clearstatcache();

		return substr(sprintf("%o", fileperms($filePath)), -4);
	}
endif;

if (!function_exists('acx_backupDatabase')) :
	/**
	 * @public
	 * @since v0.1
	 * @uses wp_die()
	 *
	 * Backup the database and save the script to the plug-in's backups directory.
	 * This directory must be writable!
	 *
	 * @return string The name of the generated backup file or empty string on failure.
	 */
	function acx_backupDatabase()
	{
		$dir =  ACX_PLUGIN_DIR.'backups';
		if (!is_writable($dir))
		{
			$s = sprintf(__('The %s directory <strong>MUST</strong> be writable for this feature to work!'), $dir);
			wp_die($s);
		}

		$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
		if (!$link) {
			wp_die(__('Error: Cannot connect to database!'));
		}
		if (!mysql_select_db(DB_NAME,$link)) {
			wp_die(__('Error: Could not select the database!'));
		}

		//get all of the tables
		$tables = array();
		$result = mysql_query('SHOW TABLES');
		while($row = mysql_fetch_row($result))
		{
			$tables[] = $row[0];
		}

		if (empty($tables))
		{
			wp_die(__('Could not retrieve the list of tables from the database!'));
		}

		$return = 'CREATE DATABASE IF NOT EXISTS '.DB_NAME.";\n\n";
		$return .= 'USE '.DB_NAME.";\n\n";

		//cycle through
		foreach($tables as $table)
		{
			$result = mysql_query('SELECT * FROM '.$table);
			$num_fields = mysql_num_fields($result);

			$return.= 'DROP TABLE IF EXISTS '.$table.';';
			$row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
			$return.= "\n\n".$row2[1].";\n\n";

			for ($i = 0; $i < $num_fields; $i++)
			{
				while($row = mysql_fetch_row($result))
				{
					$return.= 'INSERT INTO '.$table.' VALUES(';
					for($j=0; $j<$num_fields; $j++)
					{
						$row[$j] = addslashes($row[$j]);
						$row[$j] = @ereg_replace("\n","\\n",$row[$j]);
						if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
						if ($j<($num_fields-1)) { $return.= ','; }
					}
					$return.= ");\n";
				}
			}
			$return.="\n\n\n";
		}

		//save file
        $time = gmdate("m-j-Y-h-i-s", time());
        $rand = make_seed()+rand(12131, 9999999);
		$fname = 'bck_'.$time.'_'.$rand.'.sql';
		$filePath = trailingslashit($dir).$fname;
		$ret = acxUtil::writeFile($filePath, $return);

		return (($ret > 0) ? $fname : '');
	}
endif;

if (!function_exists('acx_getAvailableBackupFiles')) :
	/**
	 * @public
	 * @since v0.1
	 * Retrieve the list of all available backup files from the backups directory
	 * @return array
	 */
	function acx_getAvailableBackupFiles()
	{
		$files = glob(ACX_PLUGIN_DIR.'backups/*.sql');
		if (empty($files)) { return array();}
		return array_map('basename', $files/*, array('.sql')*/);
	}
endif;

if (!function_exists('acx_getTablesToAlter')) :
	/**
	 * @public
	 * @since v0.1
	 * @global object $wpdb
	 * Get the list of tables to modify
	 * @return array
	 */
	function acx_getTablesToAlter()
	{
		global $wpdb;

		return $wpdb->get_results("SHOW TABLES LIKE '".$GLOBALS['table_prefix']."%'", ARRAY_N);
	}
endif;


if (!function_exists('acx_renameTables')) :
	/**
	 * @public
	 * @since v0.1
	 * @global object $wpdb
	 * Rename tables from database
	 * @param array the list of tables to rename
	 * @param string $currentPrefix the current prefix in use
	 * @param string $newPrefix the new prefix to use
	 * @return array
	 */
	function acx_renameTables($tables, $currentPrefix, $newPrefix)
	{
		global $wpdb;

		$changedTables = array();

		foreach ($tables as $k=>$table)
		{
			$tableOldName = $table[0];

			// Try to rename the table
			$tableNewName = substr_replace($tableOldName, $newPrefix, 0, strlen($currentPrefix));

			// Try to rename the table
			$wpdb->query("RENAME TABLE `{$tableOldName}` TO `{$tableNewName}`");
			array_push($changedTables, $tableNewName);
		}
		return $changedTables;
	}
endif;


if (!function_exists('acx_renameDbFields')) :
	/**
	 * @public
	 * @since v0.1
	 * @global object $wpdb
	 * Rename some fields from options & usermeta tables in order to reflect the prefix change
	 * @param string $oldPrefix the existent db prefix
	 * @param string $newPrefix the new prefix to use
	 */
	function acx_renameDbFields($oldPrefix,$newPrefix)
	{
		global $wpdb;

		$str = '';

		if (false === $wpdb->query("UPDATE {$newPrefix}options SET option_name='{$newPrefix}user_roles' WHERE option_name='{$oldPrefix}user_roles';")) {
			$str .= '<br/>'.sprintf(__('Changing value: %suser_roles in table <strong>%soptions</strong>: <font color="#ff0000">Failed</font>')
							,$newPrefix, $newPrefix);
		}

		$query = 'UPDATE '.$newPrefix.'usermeta
					SET meta_key = CONCAT(replace(left(meta_key, ' . strlen($oldPrefix) . "), '{$oldPrefix}', '{$newPrefix}'), SUBSTR(meta_key, " . (strlen($oldPrefix) + 1) . "))
				WHERE
					meta_key IN ('{$oldPrefix}autosave_draft_ids', '{$oldPrefix}capabilities', '{$oldPrefix}metaboxorder_post', '{$oldPrefix}user_level', '{$oldPrefix}usersettings',
					'{$oldPrefix}usersettingstime', '{$oldPrefix}user-settings', '{$oldPrefix}user-settings-time', '{$oldPrefix}dashboard_quick_press_last_post_id')";

		if (false === $wpdb->query($query)) {
			$str .= '<br/>'.sprintf(__('Changing values in table <strong>%susermeta</strong>: <font color="#ff0000">Failed</font>'), $newPrefix);
		}

		if (!empty($str)) {
			$str = __('Changing database prefix').': '.$str;
		}

		return $str;
	}
endif;


if (!function_exists('acx_updateWpConfigTablePrefix')) :
	/**
	 * @public
	 * @since v0.1
	 * Update the wp-config file to reflect the table prefix change.
	 * The wp file must be writable for this operation to work!
	 *
	 * @param string $wsd_wpConfigFile The path to the wp-config file
	 * @param string $oldPrefix the old db prefix
	 * @param string $newPrefix The new prefix to use instead of the old one
	 * @return int the number of bytes written to te file or -1 on error
	 */
	function acx_updateWpConfigTablePrefix($wsd_wpConfigFile, $oldPrefix, $newPrefix)
	{
		// If file is not writable...
		if (!is_writable($wsd_wpConfigFile))
		{
			return -1;
		}

		// We need the 'file' function...
		if (!function_exists('file')) {
			return -1;
		}

		// Try to update the wp-config file
		$lines = file($wsd_wpConfigFile);
		$fcontent = '';
		$result = -1;
		foreach($lines as $line)
		{
			$line = ltrim($line);
			if (!empty($line)){
				if (strpos($line, '$table_prefix') !== false){
					$line = preg_replace("/=(.*)\;/", "= '".$newPrefix."';", $line);
				}
			}
			$fcontent .= $line;
		}
		if (!empty($fcontent))
		{
			// Save wp-config file
			$result = acxUtil::writeFile($wsd_wpConfigFile, $fcontent);
		}

		return $result;
	}
endif;


