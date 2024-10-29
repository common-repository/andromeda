<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

/**
 * CDN User General Data
 */
if (!class_exists('ANDROMEDA_User_General_Data')) {

    class ANDROMEDA_User_General_Data {

        /**
         * @var ANDROMEDA_User_General_Data
         */
        public static $instance;

        /**
         * get user data by user is
         * 
         * @param int $user_id
         * @return array
         */
        public static function get_user_data($user_id) {

            $user_params = array(
                'author' => 0,
                'display_name' => '',
                'user_login' => '',
                'user_email' => '',
                'roles' => ''
            );
            if ($user_id) {
                $user = get_user_by('id', $user_id);
                if ($user) {
                    $user_params = array(
                        'author' => strval($user_id),
                        'display_name' => $user->data->display_name,
                        'user_login' => $user->data->user_login,
                        'user_email' => $user->data->user_email,
                        'roles' => implode(',', $user->roles)
                    );
                }
            }

            return $user_params;
        }

        /**
         * get user data that send to api
         *  
         * @return array
         */
        public static function get_user_params_api() {
            $user = get_user_by('id', get_current_user_id());
            if ($user) {
                $user_params = self::get_user_data($user->ID);
            } else {
                $user_params = array(
                    'author' => '0',
                    'display_name' => '',
                    'user_login' => '',
                    'user_email' => '',
                    'roles' => 'guest'
                );
            }

            return $user_params;
        }

        /**
         * ANDROMEDA_User_General_Data instance
         *
         * @return object
         */
        public static function get_instance() {
            if (!isset(self::$instance) || is_null(self::$instance))
                self::$instance = new self();

            return self::$instance;
        }

    }

}