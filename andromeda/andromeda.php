<?php

/*
 * Plugin Name: Andromeda
 * Description: Monitor and track your WordPress website activity
 * Version: 1.0.0
 * License: Proprietary (do not copy)
 * Author: Naqi Rizvi
 * Author URI: https://andromeda.io
 * Text Domain:  andromeda
 * 
 * Uses portion of code from Cloudflare-Wordpress plugin by Cloudflare:
 * https://github.com/naqirizvi/andromeda
*/

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'ANDROMEDA_ACTIVITY_LOG_VERSION' ) ) {
    $plugin_data = get_file_data( __FILE__, array( 'Version' => 'Version' ) );
    define( 'ANDROMEDA_ACTIVITY_LOG_VERSION', $plugin_data['Version'] );
}

if (!defined('ANDROMEDA_ACTIVITY_LOG_FILE')) {
    define('ANDROMEDA_ACTIVITY_LOG_FILE', plugin_dir_path(__FILE__));
}

if (!defined('ANDROMEDA_ACTIVITY_LOG_URL')) {
    define('ANDROMEDA_ACTIVITY_LOG_URL', plugin_dir_url(__FILE__));
}



/**
 * The main cdn activity log class.
 *
 * @since 1.0
 */
class ANDROMEDA_CDN_Activity_Log_Plugin {
    /**
     * @var ANDROMEDA_CDN_Activity_Log_Plugin
     */
    public static $instance;

    public function __construct()
    {
        
        require_once ANDROMEDA_ACTIVITY_LOG_FILE . 'includes/index.php';

    }

   
    /**
     * ANDROMEDA_CDN_Activity_Log_Plugin instance
     *
     * @return object
     */
    public static function get_instance()
    {
        if (!isset(self::$instance) || is_null(self::$instance))
            self::$instance = new self();

        return self::$instance;
    }

}

ANDROMEDA_CDN_Activity_Log_Plugin::get_instance();