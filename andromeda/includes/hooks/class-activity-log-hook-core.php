<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('Activity_Log_Hook_Core')) {

    class Activity_Log_Hook_Core {

        /**
         * @var Activity_Log_Hook_Core
         */
        public static $instance;

        public function __construct() { 
            add_action( '_core_updated_successfully', array( &$this, 'send_core_updated_successfully_log' ) );
        }

        /**
         * send core update successfully log
         * 
         * @param int $wp_version
         */
        public function send_core_updated_successfully_log($wp_version) {
            global $pagenow;

            // Auto updated
            if ( 'update-core.php' !== $pagenow )
                $object_name = 'WordPress Auto Updated';
            else
                $object_name = 'WordPress Updated';

            $params = array(
                'type' => 'Core',
                'label' => $object_name,
                'action' => 'updated',
                'description' => $object_name . " to version " . $wp_version . " successfully."
            );
            
            $user_params = ANDROMEDA_User_General_Data::get_user_params_api();
            $params = array_merge($user_params, $params);

            ANDROMEDA_Activity_Log_Api::ANDROMEDA_activity_log_api_call($params);
        }

        /**
         * Activity_Log_Hook_Core instance
         *
         * @return object
         */
        public static function get_instance() {
            if (!isset(self::$instance) || is_null(self::$instance))
                self::$instance = new self();

            return self::$instance;
        }

    }
    
    Activity_Log_Hook_Core::get_instance();

}