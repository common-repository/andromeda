<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('Activity_Log_Hook_Taxonomies')) {

    class Activity_Log_Hook_Taxonomies {

        /**
         * @var Activity_Log_Hook_Taxonomies
         */
        public static $instance;

        public function __construct() { 
            add_action( 'created_term', array( &$this, 'hooks_created_edited_deleted_term_log' ), 10, 3 );
            add_action( 'edited_term', array( &$this, 'hooks_created_edited_deleted_term_log' ), 10, 3 );
            add_action( 'delete_term', array( &$this, 'hooks_created_edited_deleted_term_log' ), 10, 4 );
        }

        /**
         * handle all tax actions
         * 
         * @param int $term_id
         * @param int $tt_id
         * @param string $taxonomy
         * @param object $deleted_term
         */
        public function hooks_created_edited_deleted_term_log( $term_id, $tt_id, $taxonomy, $deleted_term = null ) {
            // Make sure do not action nav menu taxonomy.
            if ( 'nav_menu' === $taxonomy )
                return;
    
            if ( 'delete_term' === current_filter() )
                $term = $deleted_term;
            else
                $term = get_term( $term_id, $taxonomy );
    
            if ( $term && ! is_wp_error( $term ) ) {
                if ( 'edited_term' === current_filter() ) {
                    $action = 'updated';
                } elseif ( 'delete_term' === current_filter() ) {
                    $action  = 'deleted';
                    $term_id = '';
                } else {
                    $action = 'created';
                }
    
                $this->send_taxonomy_log($action, $taxonomy, "Term '$term->name' of taxonomy $taxonomy is $action.");
            }
        }

        /**
         * send_taxonomy_log
         * @param string $action
         * @param string $label
         * @param string $description
         */
        protected function send_taxonomy_log($action, $label, $description = '') {
            $params = array(
                'type' => 'Taxonomies',
                'label' => $label,
                'action' => $action,
                'description' => $description
            );
            
            $user_params = ANDROMEDA_User_General_Data::get_user_params_api();
            $params = array_merge($user_params, $params);
            ANDROMEDA_Activity_Log_Api::ANDROMEDA_activity_log_api_call($params);
        }
        
        /**
         * Activity_Log_Hook_Taxonomies instance
         *
         * @return object
         */
        public static function get_instance() {
            if (!isset(self::$instance) || is_null(self::$instance))
                self::$instance = new self();

            return self::$instance;
        }

    }
    
    Activity_Log_Hook_Taxonomies::get_instance();

}