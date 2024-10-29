<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('Activity_Log_Hook_Export')) {

    class Activity_Log_Hook_Export {

        /**
         * @var Activity_Log_Hook_Export
         */
        public static $instance;

        public function __construct() { 
            add_action( 'export_wp', array( &$this, 'send_export_log' ) );
        }

        /**
         * send export log
         * @param array $args
         */
        public function send_export_log( $args ) {
            $exported_type = isset( $args['content'] ) ? $args['content'] : 'all';
            $params = array(
                'type' => 'Export',
                'label' => $exported_type,
                'action' => 'downloaded',
                'description' => $exported_type ." data are exported."
            );

            $user_params = ANDROMEDA_User_General_Data::get_user_params_api();
            $params = array_merge($user_params, $params);
            ANDROMEDA_Activity_Log_Api::ANDROMEDA_activity_log_api_call($params);
        }

        /**
         * Activity_Log_Hook_Export instance
         *
         * @return object
         */
        public static function get_instance() {
            if (!isset(self::$instance) || is_null(self::$instance))
                self::$instance = new self();

            return self::$instance;
        }

    }
    
    Activity_Log_Hook_Export::get_instance();

}