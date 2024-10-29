<?php
/*
* Plugin Name: Andromeda
* Description: Log the wordpress activities without bloating the hosting server. This plugin will use our API to log the activities.
* Version: 0.0.1
* License: GPLv2 or later
* 
*/

if (!defined('ABSPATH') || !is_dir(ABSPATH)) {
	exit;
}

/**
* Define the constants in wp-config.php
*/
if (!defined('CDN_SITE_ID') || !defined('CDN_SITE_TOKEN')) {
	return;
}


require_once 'andromeda/andromeda.php';