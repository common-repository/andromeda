<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('Activity_Log_Hook_Widgets')) {

    class Activity_Log_Hook_Widgets {

        /**
         * @var Activity_Log_Hook_Widgets
         */
        public static $instance;

        public function __construct() {
            add_filter( 'widget_update_callback', array( &$this, 'hooks_widget_update_callback_log' ), 9999, 4 );

            // delete widget in low wp versions :)
            add_filter( 'sidebar_admin_setup', array( &$this, 'hooks_widget_delete_log' ) ); // Widget delete.

            // delete widget in new wp versions :)
	        add_filter( 'rest_post_dispatch', [$this, 'hooks_new_wp_versions_widget_delete_log'], 10, 3 );

        }

        public function hooks_new_wp_versions_widget_delete_log($response, $server, $request){
            $data = $response->get_data();
            if (! is_array($data)) {
                return $response;
            }

            $is_widget_page = strpos($request->get_route(),"wp/v2/widgets");
            $status = $response->get_status();

            if($request->get_method() == "DELETE"
                && $status == 200
                && isset($data['deleted'])
                && $data['deleted']
                && isset($data['previous'])
                && isset($data['previous']['id'])
                && isset($data['previous']['sidebar'])
                && $is_widget_page
            ){
                $block_id = $data['previous']['id'];
                $widget_name = $data['previous']['sidebar'];
                $this->send_widget_log('deleted', $data['previous']['sidebar'] , "The $block_id block from $widget_name widget is deleted.");
            }
		    return $response;
        }

        public function hooks_widget_update_callback_log( $instance, $new_instance, $old_instance, WP_Widget $widget ) {
            // Sanitize and validate the 'sidebar' parameter from the request
            $sidebar = isset( $_REQUEST['sidebar'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['sidebar'] ) ) : '';
        
            if ( empty( $sidebar ) ) {
                return $instance;
            }
        
            // Escape the sanitized 'sidebar' value before using it in the log
            $escaped_sidebar = esc_html( $sidebar );
            $this->send_widget_log( 'updated', $escaped_sidebar, $escaped_sidebar . ' is updated with id ' . esc_html( $widget->id ) );
        
            // Return the instance to complete the filter.
            return $instance;
        }

        public function hooks_widget_delete_log() {
            // Check if the request method is POST and the required parameters are present
            if ( 'post' === strtolower( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) && ! empty( $_REQUEST['widget-id'] ) ) {
                // Sanitize and validate the request parameters
                $delete_widget = isset( $_REQUEST['delete_widget'] ) ? (int) sanitize_text_field( wp_unslash( $_REQUEST['delete_widget'] ) ) : 0;
                $widget_id = isset( $_REQUEST['widget-id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['widget-id'] ) ) : '';
                $sidebar = isset( $_REQUEST['sidebar'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['sidebar'] ) ) : '';
        
                // Check if the widget should be deleted
                if ( 1 === $delete_widget && ! empty( $widget_id ) && ! empty( $sidebar ) ) {
                    // Escape values for safe use in the log message
                    $escaped_widget_id = esc_html( $widget_id );
                    $escaped_sidebar = esc_html( $sidebar );
        
                    // Log the widget deletion
                    $this->send_widget_log( 'deleted', $escaped_sidebar, "The $escaped_widget_id block from $escaped_sidebar widget is deleted" );
                }
            }
        }

        public function send_widget_log($action, $label, $description){
            $params = array(
                'type' => 'Widget',
                'label' => $label,
                'action' => $action,
                'description' => $description
            );

            $user_params = ANDROMEDA_User_General_Data::get_user_params_api();
            $params = array_merge($user_params, $params);

            ANDROMEDA_Activity_Log_Api::ANDROMEDA_activity_log_api_call($params);
        }

        /**
         * Activity_Log_Hook_Widgets instance
         *
         * @return object
         */
        public static function get_instance() {
            if (!isset(self::$instance) || is_null(self::$instance))
                self::$instance = new self();

            return self::$instance;
        }

    }

    Activity_Log_Hook_Widgets::get_instance();

}
