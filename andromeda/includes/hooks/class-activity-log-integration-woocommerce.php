<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('Activity_Log_Hook_Woocommerce_Integration')) {

    class Activity_Log_Hook_Woocommerce_Integration {

        /**
         * @var Activity_Log_Hook_Woocommerce_Integration
         */
        public static $instance;
        private $_wc_options = array();

        public function __construct() {
            add_action('init', array(&$this, 'init'));
        }

        public function init() {
            if (!class_exists('Woocommerce')) {
                return;
            }

            add_filter('woocommerce_get_settings_pages', array($this, 'wc_get_settings_log'), 10);
            add_filter('wp_activity_log_options', array($this, 'wc_add_setting_options_log'));
        }

        /**
         * @param WC_Settings_Page[] $settings
         *
         * @return WC_Settings_Page[]
         */
        public function wc_get_settings_log($settings) {
            if (empty($this->_wc_options)) {
                $wc_exclude_types = array(
                    'title',
                    'sectionend',
                );
                $this->_wc_options = array();

                foreach ($settings as $setting) {
                    if ('advanced' === $setting->get_id()) {
                        continue;
                    }

                    foreach ($setting->get_settings() as $option) {
                        if (isset($option['id']) && (!isset($option['type']) || !in_array($option['type'], $wc_exclude_types) )) {
                            $this->_wc_options[] = $option['id'];
                        }
                    }
                }
            }

            return $settings;
        }

        public function wc_add_setting_options_log($whitelist_options) {
            if (!empty($this->_wc_options)) {
                $whitelist_options = array_unique(array_merge($whitelist_options, $this->_wc_options));
            } 
            return $whitelist_options;
        }

        /**
         * Activity_Log_Hook_Woocommerce_Integration instance
         *
         * @return object
         */
        public static function get_instance() {
            if (!isset(self::$instance) || is_null(self::$instance))
                self::$instance = new self();

            return self::$instance;
        }

    }

    Activity_Log_Hook_Woocommerce_Integration::get_instance();
}
