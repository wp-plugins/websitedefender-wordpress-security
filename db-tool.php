<?php
if (!defined('wsdplugin_WSD_PLUGIN_SESSION')) exit;


wp_enqueue_style('wsdplugin_css_general',   wsdplugin_Utils::cssUrl('general.css'), array(), '1.0');
wp_enqueue_style('dashboard');
wp_enqueue_script('dashboard');



// Database backup

function wsdplugin_database_backup_new()
{
	$location = wsdplugin_database_backup_location();

	if (!wsdplugin_database_backup_location_writable($location))
		throw new Exception(sprintf(__("The plugin does not have permissions to write to the backup directory <strong>%s</strong>. Change the backup directory permissions to 777 for the backup to work. Once you've done the backup, you can revert back the permissions."), $location));

	$link = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
	if (!$link || !mysql_select_db(DB_NAME, $link))
		throw new Exception(__('Cannot connect to database!'));

	// get all of the tables
	$tables = array();
	$queryResult = mysql_query('SHOW TABLES');

	if ($queryResult !== false)
	{
		while($row = mysql_fetch_row($queryResult))
			$tables[] = $row[0];

		mysql_free_result($queryResult);
	}
	if (count($tables) == 0)
		throw new Exception(__('There are no tables in the database!'));

	// Generate a new file name
	$time = date('Y-m-d-H-i-s');
	$rand = md5($time . mt_rand(0, 999999999));
	$filePath = "{$location}bck_{$time}_{$rand}.sql";
	$handle = @fopen($filePath, 'w');

	if ($handle === false)
		throw new Exception(__('Cannot save the backup.'));


	fwrite($handle, 'CREATE DATABASE IF NOT EXISTS '.DB_NAME.";\n\n");
	fwrite($handle, 'USE '.DB_NAME.";\n\n");

	foreach ($tables as $table)
	{
		$queryResult = mysql_query('SELECT * FROM '.$table);
		$num_fields = mysql_num_fields($queryResult);

		fwrite($handle, "DROP TABLE IF EXISTS {$table};");
		$row = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
		fwrite($handle, "\n\n" . $row[1] . ";\n\n");

		for ($i = 0; $i < $num_fields; $i++)
		{
			while ($row = mysql_fetch_row($queryResult))
			{
				fwrite($handle, "INSERT INTO {$table} VALUES(");
				for($j = 0; $j < $num_fields; $j++)
				{
					$row[$j] = addslashes($row[$j]);
					$row[$j] = @str_replace("\n", "\\n", $row[$j]);

					fwrite($handle, isset($row[$j]) ? '"'.$row[$j].'"' : '""');

					if ($j < $num_fields - 1)
						fwrite($handle, ',');
				}
				fwrite($handle, ");\n");
			}
		}
		fwrite($handle, "\n\n\n");
		mysql_free_result($queryResult);
	}
	fclose($handle);

	return $filePath;
}
function wsdplugin_database_backup_location()
{
	$path = wsdplugin_WSD_PLUGIN_BASE_PATH . 'backups/';
	return $path;
}
function wsdplugin_database_backup_location_writable($path = null)
{
	if ($path === null) $path = wsdplugin_database_backup_location();
	return file_exists($path) && is_writable($path);
}
function wsdplugin_database_backup_list()
{
	$location = wsdplugin_database_backup_location();
	$files = array();
	$handle = @opendir($location);

	if ($handle !== false)
	{
		while ($entry = @readdir($handle))
		{
			if (strlen($entry) > 4 && substr_compare($entry, 'bck_', 0, 4, true) === 0 && substr_compare($entry, '.sql', -4, 4, true) === 0)
				$files[] = $location . $entry;
		}
		closedir($handle);
	}
	return $files;
}


// Change table prefix

function wsdplugin_change_prefix_db_rights($key = null)
{
	global $wpdb;

	$data   = array('rightsEnough' => false, 'rightsTooMuch' => false);
	$rights = $wpdb->get_results("SHOW GRANTS FOR CURRENT_USER()", ARRAY_N);

	if (count($rights) == 0)
		return $data;

	foreach ($rights as $right)
	{
		if (strlen($right[0]) == 0)
			continue;

		$r = strtoupper($right[0]);

		if (preg_match("/GRANT ALL PRIVILEGES/i", $r)) {
			$data['rightsEnough'] = $data['rightsTooMuch'] = true;
			break;
		}
		else
		{
			if (preg_match("/ALTER\s*[,|ON]/i", $r) &&
				preg_match("/CREATE\s*[,|ON]/i", $r) &&
				preg_match("/INSERT\s*[,|ON]/i", $r) &&
				preg_match("/UPDATE\s*[,|ON]/i", $r) &&
				preg_match("/DROP\s*[,|ON]/i", $r))
			{
				$data['rightsEnough'] = true;
			}
			if (preg_match_all("/CREATE|DELETE|DROP|EVENT|EXECUTE|FILE|PROCESS|RELOAD|SHUTDOWN|SUPER/", $r, $matches))
			{
				if (count($matches) > 0)
				{
					$matches = array_unique($matches[0]);
					if (count($matches) >= 5)
						$data['rightsTooMuch'] = true;
				}
			}
		}
	}
	if ($key === 'rightsEnough')
		return $data['rightsEnough'];
	else if ($key === 'rightsTooMuch')
		return $data['rightsTooMuch'];
	return $data;
}
function wsdplugin_change_prefix_config_path()
{
	$configPath = ABSPATH . 'wp-config.php';
	return $configPath;
}
function wsdplugin_change_prefix_config_writable($path = null)
{
	if ($path === null) $path = wsdplugin_change_prefix_config_path();

	if (!is_file($path))
		return false;

	$handle = @fopen($path, 'a+');
	if ($handle !== false)
	{
		fclose($handle);
		return true;
	}
	return false;
}
function wsdplugin_change_prefix_verify_config($path)
{
	global $table_prefix;

	$foundPrefix = false;
	$handle = @fopen($path, 'r');

	if ($handle === false || fseek($handle, 0, SEEK_SET) !== 0)
		return false;

	while (!feof($handle))
	{
		$line = fgets($handle);

		if ($line !== false && strpos($line, '$table_prefix') !== false)
		{
			preg_match("/=(.*)\;/", $line, $matches);

			if (is_array($matches) && count($matches) > 0 && isset($matches[1]))
			{
				$prefix = trim(trim($matches[1]), '"\'');
				if ($prefix === $table_prefix)
				{
					$foundPrefix = true;
					break;
				}
			}
		}
	}
	fclose($handle);

	return $foundPrefix;
}
function wsdplugin_change_prefix($configPath, $newPrefix)
{
	global $table_prefix;

	if ($table_prefix === $newPrefix)
		return;

	if (strlen($newPrefix) < 0) {
		throw new Exception(__('The table prefix cannot be empty.'));
	}
	if (strlen($newPrefix) > 15) {
		throw new Exception(__('The table prefix cannot exceed 15 characters.'));
	}

	wsdplugin_change_prefix_update_db($newPrefix);
	wsdplugin_change_prefix_update_config($configPath, $newPrefix);

	// Reflect the new prefix
	$table_prefix = $newPrefix;
}
function wsdplugin_change_prefix_update_db($newPrefix)
{
	global $table_prefix;
	global $wpdb;

	$tables = $wpdb->get_results("SHOW TABLES LIKE '{$table_prefix}%'", ARRAY_N);
	if (count($tables) === 0)
		throw new Exception(__("Internal Error: We couldn't retrieve the list of tables from the database! Please inform the plug-in author about this error! Thank you!"));

	// Update DB - Rename tables
	foreach ($tables as $k => $table)
	{
		$tableOldName = &$table[0];
		$tableNewName = substr_replace($tableOldName, $newPrefix, 0, strlen($table_prefix));
		$wpdb->query("RENAME TABLE `{$tableOldName}` TO `{$tableNewName}`");
	}

	// Update DB - Update fields
	$wpdb->query("UPDATE {$newPrefix}options SET option_name='{$newPrefix}user_roles' WHERE option_name='{$table_prefix}user_roles';");
	$wpdb->query('UPDATE '.$newPrefix.'usermeta
					SET meta_key = CONCAT(replace(left(meta_key, ' . strlen($table_prefix) . "), '{$table_prefix}', '{$newPrefix}'), SUBSTR(meta_key, " . (strlen($table_prefix) + 1) . "))
				WHERE
					meta_key IN ('{$table_prefix}autosave_draft_ids', '{$table_prefix}capabilities', '{$table_prefix}metaboxorder_post', '{$table_prefix}user_level', '{$table_prefix}usersettings',
					'{$table_prefix}usersettingstime', '{$table_prefix}user-settings', '{$table_prefix}user-settings-time', '{$table_prefix}dashboard_quick_press_last_post_id')");
}
function wsdplugin_change_prefix_update_config($configPath, $newPrefix)
{
	$newContent = array();

	if (!is_file($configPath))
		return false;

	$handle = fopen($configPath, 'a+');

	if (fseek($handle, 0, SEEK_SET) !== 0)
		return false;

	while (!feof($handle))
	{
		$line = fgets($handle);
		if ($line === false)
			continue;

		if (strpos($line, '$table_prefix') !== false)
		{
			$line = preg_replace("/=(.*)\;/", "= '{$newPrefix}';", $line);
		}
		$newContent[] = $line;
	}

	if (ftruncate($handle, 0) === false)
		return false;

	foreach ($newContent as &$line)
	{
		fputs($handle, $line);
	}
	fclose($handle);
}

//----------------------------------------------------------------------------------------------------------------------


$backupError            = null;
$backupSuccess          = null;
$changePrefixError      = null;
$changePrefixSuccess    = null;
$configFilePath         = wsdplugin_change_prefix_config_path();
$wpConfigFileWritable   = wsdplugin_change_prefix_config_writable($configFilePath);
$wpConfigFileValid      = wsdplugin_change_prefix_verify_config($configFilePath);
$dbRights               = wsdplugin_change_prefix_db_rights();


global $wsdplugin_nonce;

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	check_admin_referer('wsdplugin_nonce');
	$_nonce = $_POST['wsdplugin_nonce_form'];
	if (empty($_nonce) || ($_nonce != $wsdplugin_nonce)){
		wp_die("Invalid request!");
	}

	//
	// Backup database
	//
	if (isset($_POST['backup-db']))
	{

		try
		{
			wsdplugin_database_backup_new();
		}
		catch (Exception $e)
		{
			$backupError = $e->getMessage();
		}
	}

	//
	// Change table prefix
	//
	if (isset($_POST['change-prefix']))
	{
		$newPrefix = isset($_POST['new-prefix']) ? trim($_POST['new-prefix']) : '';

		if (preg_match('/^[0-9a-zA-Z_]+$/', $newPrefix) !== 1)
		{
			$changePrefixError = __('The new prefix provided is not valid.');
		}
		else
		{
			try
			{
				if (!$wpConfigFileWritable)
					$changePrefixError = __('The <strong title="%s">wp-config</strong> file <strong>MUST</strong> be writable!');

				if (!$wpConfigFileValid)
					$changePrefixError = sprintf(__('The <strong>table prefix</strong> was not found in the <strong title="%s">wp-config.php</strong> file.'),
						htmlentities($configFilePath));

				if ($dbRights['rightsEnough'] === false)
					$changePrefixError = __('The User used to access the database must have <strong>ALTER</strong> rights in order to perform this action!');

				if ($changePrefixError === null)
				{
					wsdplugin_change_prefix($configFilePath, $newPrefix);
					$changePrefixSuccess = 'The table prefix was successfully changed to ' . htmlentities($newPrefix);
				}
			}
			catch (Exception $e)
			{
				$changePrefixError = $e->getMessage();
			}
		}
	}
}

?>



<div class="wrap wsdplugin_content dashboard-widgets-wrap">


<?php
$expiration = get_option('WSD-EXPIRATION');
if ($expiration == -1) { $expiration = 'expired'; }
else if ($expiration !== false) { $expiration = (int)floor($expiration / 60.0 / 60.0 / 24.0); }

//TODO: Add the error box needed to display AJAX errors

$optInfo = get_option('WSD-SCANTYPE');
if ($optInfo === 'BAK' && get_option('WSD-EXPIRATION') == -1)
	$optInfo = 'WSDFREE';
// 1. DISPLAY THE BANNER
?>
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


	<div class="wsdplugin_page_title">
		<h2>Database Tool</h2>
	</div>

	<div style="margin-top: 10px; margin-bottom: 10px; margin-left: 2px">
		<div class="wsdplugin_warning_small" style="margin-right: 5px; float: left; margin-top: 2px;"></div>
		<div style="padding-top: 2px">
			This tool does not support WordPress multisite and can only be used on single installations of WordPress.
		</div>
	</div>

	<div class="wsdplugin_page_backup_db">
		<style type="text/css">.wsdplugin_page_backup_db .meta-box-sortables .postbox > h3 {cursor: default !important;}</style>

		<div class="metabox-holder">
			<div class="meta-box-sortables">
				<div class="postbox">
					<div title="Click to toggle" class="handlediv"><br></div>
					<h3><span>Backup database</span></h3>
					<div class="inside">

						<?php
							if ($_SERVER['REQUEST_METHOD'] == 'POST')
							{
								if (strlen($backupError) > 0 && strpos($backupError, '777') === false)
									echo '<div class="wsdplugin_message wsdplugin_message_error"><p>', wptexturize($backupError), '</p></div>';
								else if (strlen($backupSuccess) > 0)
									echo '<div class="wsdplugin_message wsdplugin_message_success"><p><strong>', wptexturize($backupSuccess), '</strong></p></div>';
							}
						?>
						<p>
							It is recommended to backup your database before using this tool to rename the WordPress database table prefixes.
							Click on the backup button below to generate a database backup.
						</p>

						<?php
							$path = wsdplugin_database_backup_location();
							if (!wsdplugin_database_backup_location_writable($path))
							{
								echo '<div class="wsdplugin_message wsdplugin_message_error"><p>';
								echo wptexturize(
									sprintf(__("The plugin does not have permissions to write to the backup directory <strong>%s</strong>. Change the backup directory permissions to 777 for the backup to work. Once you've done the backup, you can revert back the permissions."), $path)
								);
								echo '</p>';
							}
							else
							{
								?>

								<form method="post" enctype="application/x-www-form-urlencoded" style="overflow: hidden;">
									<input name="backup-db" type="submit" class="button-primary" value="Backup now" />

									<?php
										echo '<input type="hidden" name="wsdplugin_nonce_form" value="'.$wsdplugin_nonce.'" />';
										wp_nonce_field('wsdplugin_nonce');
									?>
								</form>

								<?php
							}
						?>
					</div>
				</div>
			</div>

			<div class="meta-box-sortables">
				<div class="postbox">
					<div title="Click to toggle" class="handlediv"><br></div>
					<h3><span>Change Database Prefix</span></h3>
					<div class="inside">

						<?php
						if ($_SERVER['REQUEST_METHOD'] == 'POST')
						{
							if (strlen($changePrefixError) > 0)
							{
								echo '<div class="wsdplugin_message wsdplugin_message_error"><p>';
								echo '<strong>', wptexturize($changePrefixError), '</strong>';
								echo '</p></div>';
							}
							else if (strlen($changePrefixSuccess) > 0)
							{
								echo '<div class="wsdplugin_message wsdplugin_message_warning"><p>';
								echo '<strong>', wptexturize($changePrefixSuccess), '</strong>';
								echo '</p></div>';
							}
						}
						else
						{
							if ($dbRights['rightsTooMuch'])
							{
								$html = '<div class="wsdplugin_message wsdplugin_message_warning"><p>'
									. __('The database user used to access the WordPress Database <strong>has too many rights</strong>. Limit the user’s rights to increase your Website’s Security.')
									. '</p></div>';

								echo wptexturize($html);
							}
							else if (!$dbRights['rightsEnough'])
							{
								$html = '<div class="wsdplugin_message wsdplugin_message_error"><p>'
									. __('The User used to access the database must have <strong>ALTER</strong> rights in order to perform this action!')
									. '</p></div>';
								echo wptexturize($html);
							}
						}
						?>

						<p>Change your database table prefix to avoid zero-day SQL Injection attacks.</p>
						<p>Before running this script:</p>
						<ul style="margin-left: 40px; list-style-type: disc;">
							<li>Backup your database.</li>
							<?php if ($wpConfigFileValid && $wpConfigFileWritable) { ?>
							<li>The wp-config.php file must be writable and table prefix available. <strong style="color: #006600;">(Yes)</strong></li>
							<?php } else { ?>
							<li>The wp-config.php file must be writable and table prefix available. <strong style="color: #cc0000;">(No)</strong></li>
							<?php } ?>
							<?php if ($dbRights['rightsEnough']) { ?>
							<li>The database user you're using to connect to database must have ALTER rights. <strong style="color: #006600">(Yes)</strong></li>
							<?php } else { ?>
							<li>The database user you're using to connect to database must have ALTER rights. <strong style="color: #cc0000">(No)</strong></li>
							<?php } ?>
						</ul>

						<?php
						if (!$wpConfigFileValid)
						{
							$html = '<div class="wsdplugin_message wsdplugin_message_error"><p>'
								. sprintf(__('The <strong>table prefix</strong> was not found in the <strong title="%s">%s</strong> file.'), htmlentities($configFilePath), htmlentities(basename($configFilePath)))
								. '</p></div>';
							echo wptexturize($html);
						}
						else if (!$wpConfigFileWritable)
						{
							$html = '<div class="wsdplugin_message wsdplugin_message_error"><p>';
							$html .= sprintf(__('The <strong title="%s">%s</strong> file <strong>MUST</strong> be writable for this feature to work!'), htmlentities($configFilePath), htmlentities(basename($configFilePath)));
							$html .= '</div></p>';
							echo wptexturize($html);
						}
						else
						{
							?>

							<form method="post" enctype="application/x-www-form-urlencoded">
								<?php
									$html = '<div class="wsdplugin_message wsdplugin_message_warning"><p>'
									. __('<strong>Maximum prefix length is of 15 characters.</strong>')
									. '</p></div>';

									echo wptexturize($html);
								?>

								<p>Change the current:<input name="new-prefix" type="text" maxlength="15" value="<?php global $table_prefix; echo $table_prefix;?>"/> table prefix to something different.</p>
								<p>Allowed characters: all latin alphanumeric as well as the _ (underscore).</p>
								<?php if ($dbRights['rightsEnough']) { ?>
								<input type="submit" name="change-prefix" class="button-primary" value="Start renaming"/>
								<?php } ?>
								<?php
									echo '<input type="hidden" name="wsdplugin_nonce_form" value="'.$wsdplugin_nonce.'" />';
									wp_nonce_field('wsdplugin_nonce');
								?>
							</form>

					<?php } ?>
					</div>
				</div>
			</div>

			<div class="meta-box-sortables">
				<div class="postbox">
					<div title="Click to toggle" class="handlediv"><br></div>
					<h3><span>Database Backup Files</span></h3>
					<div class="inside">

						<div style="margin-top: 10px; margin-bottom: 10px; margin-left: 2px">
							<div style="margin-right: 5px; float: left; margin-top: 2px;" class="wsdplugin_warning_small"></div>
							<div style="padding-top: 2px">
								Once you rename the WordPress database table prefix you can delete these backups from the
								backup folder <strong><?php echo wptexturize(wsdplugin_database_backup_location()); ?></strong>.
								Alternatively you can upload an empty index.php file to avoid directory listing and information disclosure.
							</div>
						</div>

						<div style="clear:both"></div>

						<div style="margin-top: 20px">

						<?php
						$backupFiles = wsdplugin_database_backup_list();
						if (count($backupFiles) > 0)
						{
							natsort($backupFiles);
							$backupFiles = array_reverse($backupFiles);

							foreach($backupFiles as $item)
							{
								$name = basename($item);
								list($y, $m, $d, $h, $i, $s) = explode('-', substr($name, 4, 19));
								$backupDate = date_create("$y-$m-$d $h:$i:$s");
								$displayName = ($backupDate === false) ? $name : $backupDate->format('d M Y H:i:s');

								echo '<a style="text-decoration: none" href="', htmlentities(wsdplugin_WSD_PLUGIN_BASE_URL . 'backups/' . basename($item)), '">',
									htmlentities($displayName), '</a>';

								echo '<br>';
							}
						}
						else
						{
							?>
						<p><?php echo __('You don\'t have any backups so far.'); } ?></p>

						</div>
					</div>
				</div>
			</div>

		</div>

	</div>
</div>
