<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('Activity_Log_Hook_Menus')) {

    class Activity_Log_Hook_Menus {

        /**
         * @var Activity_Log_Hook_Menus
         */
        public static $instance;

        public function __construct() {
            // create / update menu log
            add_action('wp_update_nav_menu', array(&$this, 'menu_created_updated_log'));
            add_action('wp_create_nav_menu', array(&$this, 'menu_created_updated_log'));
            // delete menu log
            add_action('delete_nav_menu', array(&$this, 'menu_deleted_log'), 10, 3);
        }

        /**
         * create / update menu log
         * 
         * @param int $nav_menu_selected_id
         */
        public function menu_created_updated_log($nav_menu_selected_id) {
            if ($menu_object = wp_get_nav_menu_object($nav_menu_selected_id)) {
                if ('wp_create_nav_menu' === current_filter()) {
                    $action = 'created';
                } else {
                    $action = 'updated';
                }

                $this->send_nav_menu_log($action, $menu_object->name, $action . ' Menu Name ' . $menu_object->name );
            }
        }

        /**
         * delete menu log
         * 
         * @param object $term
         * @param int $tt_id
         * @param object $deleted_term
         */
        public function menu_deleted_log($term, $tt_id, $deleted_term) {
            $this->send_nav_menu_log('deleted', $deleted_term->name,'Deleted Menu Name ' . $deleted_term->name );
        }

        protected function send_nav_menu_log($action, $label, $description = '') {
            $params = [];
            if ($action && $label) { 
                $params = array(
                    'type' => 'Menus',
                    'label' => $label,
                    'action' => $action,
                    'description' => $description
                );

                $user_params = ANDROMEDA_User_General_Data::get_user_params_api();
                $params = array_merge($user_params, $params);

                ANDROMEDA_Activity_Log_Api::ANDROMEDA_activity_log_api_call($params);
            }
        }

        /**
         * Activity_Log_Hook_Menus instance
         *
         * @return object
         */
        public static function get_instance() {
            if (!isset(self::$instance) || is_null(self::$instance))
                self::$instance = new self();

            return self::$instance;
        }

    }

    Activity_Log_Hook_Menus::get_instance();
}