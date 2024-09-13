<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace Thrive\Automator\Items;
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Connection_Test extends Action_Field {

	public static function get_name() {
		return __( 'Test connection', 'thrive-automator' );
	}

	public static function get_description() {
		return __( 'Test the connection.', 'thrive-automator' );
	}

	public static function get_placeholder() {
		return __( 'Send test', 'thrive-automator' );
	}

	public static function get_id() {
		return 'test_connection_button';
	}

	public static function get_type() {
		return 'action_test';
	}

	/**
	 * An array of extra options to be passed to the field which can affect the display of the field
	 *
	 * @return array
	 */
	public static function get_extra_options() {
		return [
			'success_message'   => __( 'Webhook sent successfully', 'thrive-automator' ),
			'fail_message'      => __( 'Webhook failed with error code', 'thrive-automator' ),
			'append_error_code' => true,
		];
	}
}
