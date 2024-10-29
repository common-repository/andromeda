<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('Activity_Log_Hook_Posts')) {

    class Activity_Log_Hook_Posts {

        /**
         * @var Activity_Log_Hook_Posts
         */
        public static $instance;
        public $change_post_status_count = 0;

        public function __construct() {
            // post change activity log
            add_action('transition_post_status', array(&$this, 'transition_post_status_log'), 10, 3);
            // post delete activity log
            add_action('delete_post', array(&$this, 'delete_post_log'));
 
        }
        
        /**
         * post change activity log
         * 
         * @param string $new_status
         * @param string $old_status
         * @param object $post 
         */
        public function transition_post_status_log($new_status, $old_status, $post) {
            if($this->change_post_status_count > 0)
                    return;
            $this->change_post_status_count++;
            
            if ('auto-draft' === $old_status && ( 'auto-draft' !== $new_status && 'inherit' !== $new_status )) {
                // page created
                $action = 'created';
            } elseif ('auto-draft' === $new_status || ( 'new' === $old_status && 'inherit' === $new_status )) {
                // nvm.. ignore it.
                return;
            } elseif ('trash' === $new_status) {
                // page was deleted.
                $action = 'trashed';
            } elseif ('trash' === $old_status) {
                $action = 'restored';
            } else {
                // page updated. I guess.
                $action = 'updated';
            }

            if (wp_is_post_revision($post->ID))
                return;

            // Skip for menu items.
            if ('nav_menu_item' === get_post_type($post->ID))
                return;

            $post_type = $post->post_type;
            $post_title = $this->get_post_title($post->ID); 
            $this->send_posts_activity_log($action, $post_type, $post_title, ucfirst($action . ' Posts with post type ' . $post_type) . ' with name ' . $post_title . ' and ID ' . $post->ID );
        }

        /**
         * post delete activity log
         * 
         * @param int $post_id
         */
        public function delete_post_log($post_id) {
            if (wp_is_post_revision($post_id))
                return;

            $post = get_post($post_id);

            if (!$post) {
                return;
            }

            if (in_array($post->post_status, array('auto-draft', 'inherit')))
                return;

            // Skip for menu items.
            if ('nav_menu_item' === get_post_type($post->ID))
                return;
 
            $post_type = $post->post_type;
            $post_title = $this->get_post_title($post->ID); 
            $this->send_posts_activity_log('deleted', $post_type, $post_title, 'Deleted  Posts with post type ' . $post_type . ' with name ' . $post_title . ' and ID ' . $post->ID );
        }
        
        protected function get_post_title($post = 0) {
            $title = esc_html(get_the_title($post));

            if (empty($title))
                $title = __('(no title)', 'andromeda');

            return $title;
        }

        protected function send_posts_activity_log($action, $type, $label, $description = '') {
            $params = [];
            if ($action && $label) {
                $params = array(
                    'type' => $type,
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
         * Activity_Log_Hook_Plugins instance
         *
         * @return object
         */
        public static function get_instance() {
            if (!isset(self::$instance) || is_null(self::$instance))
                self::$instance = new self();

            return self::$instance;
        }

    }

    Activity_Log_Hook_Posts::get_instance();
}
