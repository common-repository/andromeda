<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('Activity_Log_Hook_Attachments')) {

    class Activity_Log_Hook_Attachments {

        /**
         * @var Activity_Log_Hook_Attachments
         */
        public static $instance;

        public function __construct() { 
            // add attachment log
            add_action('add_attachment', array(&$this, 'add_attachment_log'));
            // edit attachment log
            add_action('edit_attachment', array(&$this, 'edit_attachment_log'));
            // delete attachment log
            add_action('delete_attachment', array(&$this, 'delete_attachment_log'));
        }

        /**
         * add attachment log
         * 
         * @param int $attachment_id
         */
        public function add_attachment_log($attachment_id) {
            $this->send_attachment_log('uploaded', $attachment_id, 'Uploaded Attachment with id ' . $attachment_id);
        }

        /**
         * edit attachment log
         * 
         * @param int $attachment_id
         */
        public function edit_attachment_log($attachment_id) {
            $this->send_attachment_log('updated', $attachment_id, 'Updated Attachment with id ' . $attachment_id);
        }

        /**
         * delete attachment log
         * 
         * @param int $attachment_id
         */
        public function delete_attachment_log($attachment_id) {
            $this->send_attachment_log('deleted', $attachment_id, 'Deleted Attachment with id ' . $attachment_id);
        }

        protected function send_attachment_log($action, $attachment_id, $description = '') {
            $params = [];
            if ($action && $attachment_id) {
                $attachment = get_post($attachment_id);
                if ($attachment) { 
                    $params = array(
                        'type' => 'Attachments',
                        'label' => $attachment->post_type,
                        'action' => $action,
                        'description' => $description
                    );
                    
                    $user_params = ANDROMEDA_User_General_Data::get_user_params_api();
                    $params = array_merge($user_params, $params);

                    ANDROMEDA_Activity_Log_Api::ANDROMEDA_activity_log_api_call($params);
                }
            }
        }
        
        /**
         * Activity_Log_Hook_Attachments instance
         *
         * @return object
         */
        public static function get_instance() {
            if (!isset(self::$instance) || is_null(self::$instance))
                self::$instance = new self();

            return self::$instance;
        }

    }
    
    Activity_Log_Hook_Attachments::get_instance();

}