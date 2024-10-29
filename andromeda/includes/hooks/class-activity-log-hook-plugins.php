<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('Activity_Log_Hook_Plugins')) {

    class Activity_Log_Hook_Plugins {

        /**
         * @var Activity_Log_Hook_Plugins
         */
        public static $instance;

        public function __construct() {
            // on active plugin send to api to log activity
            add_action('activated_plugin', array(&$this, 'activated_plugin_log'));
            // on deactive plugin send to api to log activity
            add_action('deactivated_plugin', array(&$this, 'deactivated_plugin_log'));
            // on install/ update plugin send to api to log activity
            add_action('upgrader_process_complete', array(&$this, 'hooks_plugin_install_or_update'), 10, 2);
            // on delete plugin send to api to log activity
            add_action('deleted_plugin', array($this, 'deleted_plugin_log'));
            
            add_filter('wp_redirect', array(&$this, 'plugin_modify_log'), 10, 2);
            // ajax to log plugin file update
            add_action('wp_ajax_plugin_file_change_log', array($this, 'plugin_file_change_log'));
        }
        
        /**
         * on delete plugin send to api to log activity
         * 
         * @param string $plugin_path
         */
        public function deleted_plugin_log($plugin_path){
            $this->send_plugin_activity_log('deleted', $plugin_path, 'Delete Plugin ' . $plugin_path);
        }
        /**
         * on active plugin send to api to log activity
         * 
         * @param string $plugin_name
         */
        public function activated_plugin_log($plugin_name) {
            $this->activity_log_plugin_actions('activated', $plugin_name, 'Activiate Plugin ' . $plugin_name);
        }

        /**
         * on deactive plugin send to api to log activity
         * 
         * @param type $plugin_name
         */
        public function deactivated_plugin_log($plugin_name) {
            $this->activity_log_plugin_actions('deactivated', $plugin_name, 'Deactiviate Plugin ' . $plugin_name);
        }

        /**
         * on install/ update plugin send to api to log activity
         * 
         * @param Plugin_Upgrader $upgrader
         * @param array $extra
         */
        public function hooks_plugin_install_or_update($upgrader, $extra) {
            if (!isset($extra['type']) || 'plugin' !== $extra['type'])
                return;
            // on install plugin
            if ('install' === $extra['action']) {
                $path = $upgrader->plugin_info();
                if (!$path)
                    return;

                $data = get_plugin_data($upgrader->skin->result['local_destination'] . '/' . $path, true, false);

                $this->activity_log_plugin_actions('installed', $data['Name'], 'Install Plugin ' . $data['Name'] . ' With Version ' . $data['Version']);
            }
            // on update plugins
            if ('update' === $extra['action']) {
                if (isset($extra['bulk']) && true == $extra['bulk']) {
                    $slugs = $extra['plugins'];
                } else {
                    $plugin_slug = isset($upgrader->skin->plugin) ? $upgrader->skin->plugin : $extra['plugin'];

                    if (empty($plugin_slug)) {
                        return;
                    }

                    $slugs = array($plugin_slug);
                }

                foreach ($slugs as $slug) {
                    $data = get_plugin_data(WP_PLUGIN_DIR . '/' . $slug, true, false);

                    $this->activity_log_plugin_actions('updated', $data['Name'], 'Update Plugin ' . $data['Name'] . ' With Version ' . $data['Version']);
                }
            }
        }

        public function plugin_modify_log($location, $status) {
            if (false !== strpos($location, 'plugin-editor.php')) { 
                if ((!empty($_POST) && 'update' === $_REQUEST['action'])) { 
                    $file_name = '';

                    if (!empty($_REQUEST['file'])) {  
                        // Get plugin name
                        $plugin_dir = explode('/', sanitize_text_field(urldecode($_REQUEST['file'])));
                        $plugin_data = array_values(get_plugins('/' . $plugin_dir[0]));
                        $plugin_data = array_shift($plugin_data);

                        $file_name = $plugin_data['Name'];
                    }  
                    $this->activity_log_plugin_actions('file_updated', $file_name, 'Update File ' . $file_name);
                } 
            }

            // We are need return the instance, for complete the filter.
            return $location;
        }
        
        /**
         * ajax to log plugin file update
         */

         
        public function plugin_file_change_log(){
            // Step 1: Sanitize the input data.
            $file = isset($_POST['edit_file']) ? sanitize_text_field(urldecode($_POST['edit_file'])) : '';

            // Step 2: Validate the sanitized data.
            if (!empty($file) && preg_match('/^[a-zA-Z0-9-_\/\.]+$/', $file)) {
                // File is valid, proceed with the intended operations.
                $this->send_plugin_activity_log('file_updated', $file, 'Update Plugin File ' . $file);
            } else {
                // Handle invalid input: Set default value, log an error, or show a message.
                $file = ''; // Reset invalid file.
                wp_die();
            }
        }

        /**
         * prepare plugin data to send to api activity log
         * 
         * @param string $action
         * @param string $plugin_name
         * @param string $description
         */
        protected function activity_log_plugin_actions($action, $plugin_name, $description = '') {
            // Get plugin name if is a path
            if (false !== strpos($plugin_name, '/')) {
                $plugin_dir = explode('/', $plugin_name);
                $plugin_data = array_values(get_plugins('/' . $plugin_dir[0]));
                $plugin_data = array_shift($plugin_data);
                $plugin_name = $plugin_data['Name'];
            }

            $this->send_plugin_activity_log($action, $plugin_name, $description);
        }

        protected function send_plugin_activity_log($action, $label, $description = '') {
            $params = [];
            if ($action && $label) {
                $params = array(
                    'type' => 'Plugins',
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

    Activity_Log_Hook_Plugins::get_instance();
}