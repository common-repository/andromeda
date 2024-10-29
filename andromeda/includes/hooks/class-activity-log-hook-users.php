<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('Activity_Log_Hook_Users')) {

    class Activity_Log_Hook_Users {

        /**
         * @var Activity_Log_Hook_Users
         */
        public static $instance;
        public $update_profile_count = 0;

        public function __construct() {
            // log register user data
            add_action('user_register', array(&$this, 'user_register_log'));
            // log login user data
            add_action('wp_login', array(&$this, 'wp_login_log'), 10, 2);
            // log logout user activity
            add_action('clear_auth_cookie', array(&$this, 'wp_logout_log'));
            // log login failed user activity 
            add_filter('wp_login_failed', array(&$this, 'wrong_password_log'));
            // log profile update data
            add_action('profile_update', array(&$this, 'profile_update_log'));
            // log delete user data
            add_action('delete_user', array(&$this, 'delete_user_log'));
        }

        /**
         * log register user data
         * 
         * @param int $user_id
         */
        public function user_register_log($user_id) {
            $user = get_user_by('id', $user_id);
            $current_user_id = get_current_user_id();
            if(!$current_user_id || $current_user_id == $user_id)
                $current_user_id = $user_id;
            
            $this->send_user_log_data('registered', 'Users', 'Profile', 'Register User With name '  . $user->user_nicename . ' and id ' . $user_id, $current_user_id);
        }

        /**
         * log login user data
         * 
         * @param int $user_id
         */
        public function wp_login_log($user_login, $user) {
            $this->send_user_log_data('logged_in', 'Users', 'Session', 'Login User With user login ' . $user_login, $user->ID);
        }

        /**
         * log logout user activity
         * 2olt 
         */
        public function wp_logout_log() {
            $user = wp_get_current_user();

            if (empty($user) || !$user->exists()) {
                return;
            }
            
            $this->send_user_log_data('logged_out', 'Users', 'Session', 'Logout User ' . $user->data->user_nicename, $user->ID);
        }
        
        /**
         * log login failed user activity 
         * 
         * @param string $username
         * @return string
         */
        public function wrong_password_log($username) { 
            $this->send_user_log_data('failed_login', 'Users', 'Session', 'Failed Login For User ' . $username, 0, $username);
        }

        /**
         * log profile update data
         * 
         * @param int $user_id
         */
        public function profile_update_log($user_id) {
            if($this->update_profile_count > 0)
                return;
            $user = get_user_by('id', $user_id);
            $this->update_profile_count++;

            $this->send_user_log_data('updated', 'Users', 'Profile', 'Update User Profile With name ' . $user->user_nicename, $user_id);
        }
        
        /**
         * log delete user data
         * 
         * @param int $user_id
         */
        public function delete_user_log($user_id) {
            $user = get_user_by('id', $user_id);
 
            $this->send_user_log_data('deleted', 'Users', 'Profile', 'Delete User With name ' . $user->user_nicename, $user_id);
        }

        /**
         * send user data to api
         * 
         * @param string $action
         * @param string $type
         * @param string $label
         * @param string $description
         * @param int $user_id
         * @param string $user_name
         */
        public function send_user_log_data($action, $type, $label, $description = '', $user_id = false, $user_name = false) {
            $params = array();
            if ($action && $type && $label && ($user_id || $user_name)) {
                $params = array(
                    'type' => $type,
                    'label' => $label,
                    'action' => $action,
                    'description' => $description
                );
                if ($action == 'failed_login') {
                    $user_params = array(
                        'author' => '0',
                        'display_name' => '',
                        'user_login' => $user_name,
                        'user_email' => '',
                        'roles' => ''
                    );
                } else {
                    $user_params = ANDROMEDA_User_General_Data::get_user_data($user_id);
                }
                $params = array_merge($user_params, $params);

                ANDROMEDA_Activity_Log_Api::ANDROMEDA_activity_log_api_call($params);
            }
        }

        /**
         * Activity_Log_Hook_Users instance
         *
         * @return object
         */
        public static function get_instance() {
            if (!isset(self::$instance) || is_null(self::$instance))
                self::$instance = new self();

            return self::$instance;
        }

    }

    Activity_Log_Hook_Users::get_instance();
}
