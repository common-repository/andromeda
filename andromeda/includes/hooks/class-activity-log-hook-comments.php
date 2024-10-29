<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('Activity_Log_Hook_Comments')) {

    class Activity_Log_Hook_Comments {

        /**
         * @var Activity_Log_Hook_Comments
         */
        public static $instance;

        public function __construct() { 
            add_action( 'wp_insert_comment', array( &$this, 'comment_log_handler' ), 10, 2 );
            add_action( 'edit_comment', array( &$this, 'comment_log_handler' ) );
            add_action( 'trash_comment', array( &$this, 'comment_log_handler' ) );
            add_action( 'untrash_comment', array( &$this, 'comment_log_handler' ) );
            add_action( 'spam_comment', array( &$this, 'comment_log_handler' ) );
            add_action( 'unspam_comment', array( &$this, 'comment_log_handler' ) );
            add_action( 'delete_comment', array( &$this, 'comment_log_handler' ) );
            add_action( 'transition_comment_status', array( &$this, 'transition_comment_status_handler' ), 10, 3 );
        }

        /**
         * handle all actions on comments
         * @param int $comment_ID
         * @param object $comment
         */
        public function comment_log_handler( $comment_ID, $comment = null ) {
            if ( is_null( $comment ) )
                $comment = get_comment( $comment_ID );

            $action = 'created';
            switch ( current_filter() ) {
                case 'wp_insert_comment' :
                    $action = 1 === (int) $comment->comment_approved ? 'approved' : 'pending';
                    break;
                
                case 'edit_comment' :
                    $action = 'updated';
                    break;
    
                case 'delete_comment' :
                    $action = 'deleted';
                    break;
                
                case 'trash_comment' :
                    $action = 'trashed';
                    break;
                
                case 'untrash_comment' :
                    $action = 'untrashed';
                    break;
                
                case 'spam_comment' :
                    $action = 'spammed';
                    break;
                
                case 'unspam_comment' :
                    $action = 'unspammed';
                    break;
            }
            
            $this->send_comment_log( $comment_ID, $action, $comment );
        }

        /**
         * changing status of the comment
         * @param string $new_status
         * @param string $old_status
         * @param object $comment
         */
        public function transition_comment_status_handler( $new_status, $old_status, $comment ) {
            $this->send_comment_log( $comment->comment_ID, $new_status, $comment );
        }

        /**
         * add comment log
         * @param int $id
         * @param string $action
         * @param object $comment
         */
        protected function send_comment_log( $comment_id, $action, $comment = null ) {
            if ( is_null( $comment ) ) $comment = get_comment( $comment_id );

            $params = [];
            if ($action && !empty($comment)) {
                $params = array(
                    'type' => 'Comments',
                    'label' => get_post_type( $comment->comment_post_ID ),
                    'action' => $action,
                    'description' => "Comment with id " . $comment_id . " is " . $action. "."
                );
                
                $user_params = ANDROMEDA_User_General_Data::get_user_params_api();
                $params = array_merge($user_params, $params);

                ANDROMEDA_Activity_Log_Api::ANDROMEDA_activity_log_api_call($params);
            }
        }
        
        /**
         * Activity_Log_Hook_Comments instance
         *
         * @return object
         */
        public static function get_instance() {
            if (!isset(self::$instance) || is_null(self::$instance))
                self::$instance = new self();

            return self::$instance;
        }

    }
    
    Activity_Log_Hook_Comments::get_instance();

}