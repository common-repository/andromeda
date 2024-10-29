<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('Activity_Log_Hook_Themes')) {

    class Activity_Log_Hook_Themes {

        /**
         * @var Activity_Log_Hook_Themes
         */
        public static $instance;

        public function __construct() { 
            // add_filter( 'wp_redirect', array( &$this, 'hooks_theme_modify_log' ), 10, 2 );
            add_action('wp_ajax_theme_file_change_log', array($this, 'theme_file_change_log'));
            add_action( 'switch_theme', array( &$this, 'hooks_switch_theme_log' ), 10, 2 );
            add_action( 'delete_site_transient_update_themes', array( &$this, 'hooks_theme_deleted_log' ) );
            add_action( 'upgrader_process_complete', array( &$this, 'hooks_theme_install_or_update_log' ), 10, 2 );

            // Theme customizer
            add_action( 'customize_save', array( &$this, 'hooks_theme_customizer_modified_log' ) );
        }

        public function theme_file_change_log() {
            // Sanitize and validate the 'edit_file' input from the POST request
            $edit_file = ( isset( $_POST['edit_file'] ) && !empty( $_POST['edit_file'] ) ) ? sanitize_text_field( urldecode( wp_unslash( $_POST['edit_file'] ) ) ) : null;
        
            if ( $edit_file ) {
                // Escape the file name for safe use in the log message
                $escaped_file = esc_html( $edit_file );
                $this->send_theme_log( 'file_updated', $escaped_file, "Update theme File '$escaped_file'" );
            }
            wp_die();
        }
    
        public function hooks_switch_theme_log( $new_name, WP_Theme $new_theme ) {
            $this->send_theme_log('activated',$new_theme->get_stylesheet(),$new_name . " theme is activated." );
        }
    
        public function hooks_theme_customizer_modified_log( WP_Customize_Manager $obj ) {
            $theme_name = $obj->theme()->display( 'Name' );
            $this->send_theme_log('updated', $theme_name , $theme_name . " is updated from theme customizer." );
        }
    
        public function hooks_theme_deleted_log() {
            $backtrace_history = debug_backtrace();
    
            $delete_theme_call = null;
            foreach ( $backtrace_history as $call ) {
                if ( isset( $call['function'] ) && 'delete_theme' === $call['function'] ) {
                    $delete_theme_call = $call;
                    break;
                }
            }
    
            if ( empty( $delete_theme_call ) )
                return;
    
            $name = $delete_theme_call['args'][0];
            
            $this->send_theme_log('deleted', $name, $name . " theme is deleted.");
        }
    
        /**
         * @param Theme_Upgrader $upgrader
         * @param array $extra
         */
        public function hooks_theme_install_or_update_log( $upgrader, $extra ) {
            if ( ! isset( $extra['type'] ) || 'theme' !== $extra['type'] )
                return;
            
            if ( 'install' === $extra['action'] ) {
                $slug = $upgrader->theme_info();
                if ( ! $slug )
                    return;
    
                wp_clean_themes_cache();
                $theme   = wp_get_theme( $slug );
                $name    = $theme->name;
                $version = $theme->version;
    
                $this->send_theme_log('installed', $name, $name . " theme is installed.");
            }
            
            if ( 'update' === $extra['action'] ) {
                if ( isset( $extra['bulk'] ) && true == $extra['bulk'] )
                    $slugs = $extra['themes'];
                else
                    $slugs = array( $upgrader->skin->theme );
    
                foreach ( $slugs as $slug ) {
                    $theme      = wp_get_theme( $slug );
                    $stylesheet = $theme['Stylesheet Dir'] . '/style.css';
                    $theme_data = get_file_data( $stylesheet, array( 'Version' => 'Version' ) );
                    
                    $name    = $theme['Name'];
                    $version = $theme_data['Version'];
    
                    $this->send_theme_log('updated', $name, $name . " theme is updated to version $version.");
                }
            }
        }

        protected function send_theme_log($action, $label, $description = '') {
            $params = array(
                'type' => 'Themes',
                'label' => $label,
                'action' => $action,
                'description' => $description
            );
            
            $user_params = ANDROMEDA_User_General_Data::get_user_params_api();
            $params = array_merge($user_params, $params);

            ANDROMEDA_Activity_Log_Api::ANDROMEDA_activity_log_api_call($params);
        }
        
        /**
         * Activity_Log_Hook_Themes instance
         *
         * @return object
         */
        public static function get_instance() {
            if (!isset(self::$instance) || is_null(self::$instance))
                self::$instance = new self();

            return self::$instance;
        }

    }
    
    Activity_Log_Hook_Themes::get_instance();

}