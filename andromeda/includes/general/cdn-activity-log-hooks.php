<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
 
/**
 * CDN Activity Log Hooks
 */ 
if (!class_exists('ANDROMEDA_Activity_log_Hooks')) {

    class ANDROMEDA_Activity_log_Hooks {

        /**
         * @var ANDROMEDA_Activity_log_Hooks
         */
        public static $instance;

        public function __construct() {
   
            add_action( 'admin_enqueue_scripts', array($this, 'register_script') );
            // Load all our hooks.
            include ANDROMEDA_ACTIVITY_LOG_FILE . 'includes/hooks/class-activity-log-hook-users.php';
            include ANDROMEDA_ACTIVITY_LOG_FILE . 'includes/hooks/class-activity-log-hook-attachments.php';
            include ANDROMEDA_ACTIVITY_LOG_FILE . 'includes/hooks/class-activity-log-hook-menus.php';
            include ANDROMEDA_ACTIVITY_LOG_FILE . 'includes/hooks/class-activity-log-hook-options.php' ;
            include ANDROMEDA_ACTIVITY_LOG_FILE . 'includes/hooks/class-activity-log-hook-plugins.php';
            include ANDROMEDA_ACTIVITY_LOG_FILE . 'includes/hooks/class-activity-log-hook-posts.php';
            include ANDROMEDA_ACTIVITY_LOG_FILE . 'includes/hooks/class-activity-log-hook-taxonomies.php';
            include ANDROMEDA_ACTIVITY_LOG_FILE . 'includes/hooks/class-activity-log-hook-themes.php';
            include ANDROMEDA_ACTIVITY_LOG_FILE . 'includes/hooks/class-activity-log-hook-widgets.php';
            include ANDROMEDA_ACTIVITY_LOG_FILE . 'includes/hooks/class-activity-log-hook-core.php';
            include ANDROMEDA_ACTIVITY_LOG_FILE . 'includes/hooks/class-activity-log-hook-export.php';
            include ANDROMEDA_ACTIVITY_LOG_FILE . 'includes/hooks/class-activity-log-hook-comments.php';
            include ANDROMEDA_ACTIVITY_LOG_FILE . 'includes/hooks/class-activity-log-integration-woocommerce.php';
        }

        
        public function register_script($hook_suffix){
            if($hook_suffix == "plugin-editor.php" || $hook_suffix == 'theme-editor.php'){
                wp_enqueue_script('theme-plugin-activity-log', ANDROMEDA_ACTIVITY_LOG_URL . '/assets/js/theme-plugin-activity-log.js', array('jquery'), ANDROMEDA_ACTIVITY_LOG_VERSION, true);
                wp_localize_script('theme-plugin-activity-log', 'theme_login_activity_log', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
            }
        }
        
        /**
         * ANDROMEDA_Activity_log_Hooks instance
         *
         * @return object
         */
        public static function get_instance() {
            if (!isset(self::$instance) || is_null(self::$instance))
                self::$instance = new self();

            return self::$instance;
        }

    }
    
    ANDROMEDA_Activity_log_Hooks::get_instance();

}