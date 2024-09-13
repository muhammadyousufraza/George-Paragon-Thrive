<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class Thrive_Dash_Api_SendOwl_Exception extends Exception {

	public function __construct( $message = '', $code = 0, $previous = null ) {

		parent::__construct( $message, $code, $previous );

		global $wpdb;

		$log_data = array(
			'date'          => date( 'Y-m-d H:i:s' ),
			'error_message' => tve_sanitize_data_recursive( $message, 'sanitize_text_field' ),
			'api_data'      => serialize( array() ),
			'connection'    => 'sendowl',
			'list_id'       => maybe_serialize( array() ),
		);

		$wpdb->insert( $wpdb->prefix . 'tcb_api_error_log', $log_data );
	}
}
