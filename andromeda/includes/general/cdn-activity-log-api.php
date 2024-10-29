<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * CDN Activity Log Api
 */
if (!class_exists('ANDROMEDA_Activity_Log_Api')) {

    class ANDROMEDA_Activity_Log_Api {

        /**
         * send site activity log api request
         * 
         * @param array $params 
         * @return array
         */
        public static function ANDROMEDA_activity_log_api_call( $params ) { 
            if ( $params ) {
                $api_url = 'https://api.rocket.net/v1/sites/' . CDN_SITE_ID . '/activity/events';
        
                // Append IP to request params
                $params['ip'] = self::_get_ip_address();
        
                // Terminate label if its length is more than 40 characters
                if ( isset( $params['label'] ) && strlen( $params['label'] ) > 40 ) {
                    $params['label'] = substr( $params['label'], 0, 40 ) . '...';
                }
        
                // Prepare request arguments
                $args = array(
                    'body'        => wp_json_encode( $params ),
                    'headers'     => array(
                        'Authorization' => 'Bearer ' . CDN_SITE_TOKEN,
                        'Accept'        => 'application/json',
                        'Content-Type'  => 'application/json',
                    ),
                    'method'      => 'POST',
                    'data_format' => 'body',
                    'timeout'     => 5,
                );
        
                // Make the HTTP request using wp_remote_post
                $response = wp_remote_post( $api_url, $args );
        
                // Check for errors in the response
                if ( is_wp_error( $response ) ) {
                    $error_message = $response->get_error_message();
                    $message = 'Activity log Exception: ' . $error_message;
                    self::cdn_activity_log( $message );
                    return array();
                }
        
                // Log the request and response
                $message = 'Activity log Request: ' . wp_json_encode( $params ) . ' Activity log Response: ' . wp_remote_retrieve_body( $response );
                self::cdn_activity_log( $message );
        
                // Return the result
                $result = json_decode( wp_remote_retrieve_body( $response ), true );
                return $result;
            }
        
            return $params;
        }
        
        /**
	 * Get real address
	 * 
	 * @since 2.1.4
	 * 
	 * @return string real address IP
	 */
	public static function _get_ip_address() {
		$server_ip_keys = array(
			'HTTP_CF_CONNECTING_IP', // CloudFlare
			'HTTP_TRUE_CLIENT_IP', // CloudFlare Enterprise header
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);
		
        foreach ( $server_ip_keys as $key ) {
            if ( isset( $_SERVER[ $key ] ) ) {
                // Sanitize and validate the IP address
                $ip_address = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
                if ( filter_var( $ip_address, FILTER_VALIDATE_IP ) ) {
                    // Escape the validated IP address before returning
                    return esc_html( $ip_address );
                }
            }
        }
		
		// Fallback local ip.
		return '127.0.0.1';
	}
        

        /**
         * wp cdn activity log custom log
         * 
         * @param string $message
         */
        public static function cdn_activity_log($message) {
            if (WP_DEBUG_LOG) {
                try {
                    error_log($message);
                } catch (\Exception $e) {
                    
                }
            }
        }

    }

}