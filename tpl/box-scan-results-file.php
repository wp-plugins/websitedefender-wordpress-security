<?php
/*
 * Displays the File sscan results info
 * 
 * @package ACX
 * @since v0.1
 */
?>
<?php
    //@@ require a valid request
if (!defined('ACX_PLUGIN_NAME')) { exit; }
    //@@ Only load in the plug-in pages
if (!ACX_SHOULD_LOAD) { exit; }

	global $acxFileList;
?>
<?php
	$acx_isPostBack = false;
	$acx_message = '';
	
	//@ IF POSTBACK
	if ($_SERVER['REQUEST_METHOD'] == 'POST')
	{
		$acx_isPostBack = true;

		$result = acx_changeFilePermissions();

		if (empty($result)) {
			$acx_message = __('No changes applied!');
		}
		else {
			$acx_message = __('Successful changes').': '.$result['success'].'<br/>';
			$acx_message .= __('Failed').': '.$result['failed'].__('TODO: add error desc here').'<br/>';
		}
	}
?>
<?php
//@ Check the files
if (empty($acxFileList)) {
	echo __('There are currently no files set for scanning!');
}
else
{
	//@@ Display action result
	if ($acx_isPostBack)
	{
		echo '<p class="acx-info-box">';
			echo $acx_message;
		echo '</p>';
	}
	
	echo '<table class="acx-table" cellpadding="0" cellspacing="0">';
		echo '<thead class="widget-top">';
			echo '<tr>';
                echo '<td></td>';
				echo '<td>',__('Name'),'</td>';
				echo '<td>',__('Path'),'</td>';
				echo '<td class="center">',__('Current permissions'),'</td>';
				echo '<td class="center">',__('Suggested permissions'),'</td>';
			echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
	foreach($acxFileList as $fileName => $v)
	{
		$filePath = $v['filePath'];
		$p = acx_getFilePermissions($filePath);
		$sp = $v['suggestedPermissions'];
		
		$cssClass = ((octdec($p) == octdec($sp)) ? 'success' : 'error');
		
		echo '<tr>';
            echo '<td class="td_'.$cssClass.'"></td>';
			echo '<td>',$fileName,'</td>';
			echo '<td>',$filePath,'</td>';
            //@ Current
			if ($p > octdec('0')) {
				echo '<td class="center">',$p,'</td>';
			}
			else { echo '<td class="center">',__('not found'),'</td>'; }
			
            //@ Suggested
            if (file_exists($filePath))
            {
                echo '<td class="center">',$sp,'</td>';
            }
            else
            {
                if (is_file($filePath)) {
                    echo '<td class="center">0644</td>';
                }
                elseif (is_dir($filePath)) { echo '<td class="center">0755</td>'; }
                else {
                    echo '<td class="center">',$sp,'</td>';
                }
            }
		echo '</tr>';
	}
		echo '</tbody>';
	echo '</table>';

    echo '<p class="acx-info-box" style="margin: 0 0 7px 0;">';
        echo __('Our suggested permissions are still secure but more permissive in oder to not break some servers\' setups.
            If your existent file permissions are more restrictive, ex: 0750 instead of the suggested 0755 then you have no reason to 
            change it to the suggested 0755 permissions.');
    echo '</p>';
}
?>
