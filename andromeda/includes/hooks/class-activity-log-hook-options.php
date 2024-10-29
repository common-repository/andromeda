<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('Activity_Log_Hook_Options')) {

    class Activity_Log_Hook_Options {

        /**
         * @var Activity_Log_Hook_Options
         */
        public static $instance;

        public function __construct() {
            add_action('updated_option', array(&$this, 'update_option_activity_log'), 10, 3);
        }

        public function update_option_activity_log($option, $oldvalue, $_newvalue) {
            $options_log = apply_filters('wp_activity_log_options', array(
                // General
                'blogname',
                'blogdescription',
                'siteurl',
                'home',
                'admin_email',
                'users_can_register',
                'default_role',
                'WPLANG',
                'timezone_string',
                'date_format',
                'time_format',
                'start_of_week',
                // Writing
                'use_smilies',
                'use_balanceTags',
                'default_category',
                'default_post_format',
                'mailserver_url',
                'mailserver_login',
                'mailserver_pass',
                'default_email_category',
                'ping_sites',
                // Reading
                'show_on_front',
                'page_on_front',
                'page_for_posts',
                'posts_per_page',
                'posts_per_rss',
                'rss_use_excerpt',
                'blog_public',
                // Discussion
                'default_pingback_flag',
                'default_ping_status',
                'default_comment_status',
                'require_name_email',
                'comment_registration',
                'close_comments_for_old_posts',
                'close_comments_days_old',
                'thread_comments',
                'thread_comments_depth',
                'page_comments',
                'comments_per_page',
                'default_comments_page',
                'comment_order',
                'comments_notify',
                'moderation_notify',
                'comment_moderation',
                'comment_whitelist',
                'comment_max_links',
                'moderation_keys',
                'blacklist_keys',
                'show_avatars',
                'avatar_rating',
                'avatar_default',
                // Media
                'thumbnail_size_w',
                'thumbnail_size_h',
                'thumbnail_crop',
                'medium_size_w',
                'medium_size_h',
                'large_size_w',
                'large_size_h',
                'uploads_use_yearmonth_folders',
                // Permalinks
                'permalink_structure',
                'category_base',
                'tag_base',
                // Privacy
                'wp_page_for_privacy_policy',
                // Widgets
                'sidebars_widgets',
                    ));

            if (!in_array($option, $options_log))
                return;

            // TODO: need to think about save old & new values.
            $this->send_option_log_api('updated', $option, 'Update Option ' . $option);
        }
        
        protected function send_option_log_api($action, $label, $description = '') {
            $params = [];
            if ($action && $label) {
                $params = array(
                    'type' => 'Options',
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
         * Activity_Log_Hook_Options instance
         *
         * @return object
         */
        public static function get_instance() {
            if (!isset(self::$instance) || is_null(self::$instance))
                self::$instance = new self();

            return self::$instance;
        }

    }

    Activity_Log_Hook_Options::get_instance();
}