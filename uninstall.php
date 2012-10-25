<?php
if (!defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN'))
	exit;

$options = array('WSD-USER', 'WSD-HASH', 'WSD-KEY', 'WSD-ID', 'WSD-NAME', 'WSD-SCANTYPE', 'WSD-SURNAME',
	'WSD-WORKING', 'WSD-AGENT-DATA', 'WSD-AGENT-NAME', 'WSD-EXPIRATION');

foreach ($options as $option) {
	delete_option($option);
}
