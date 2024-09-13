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

class Request_Headers_Toggle extends Action_Field {

	public static function get_name() {
		return __( 'Headers', 'thrive-automator' );
	}

	public static function get_description() {
		return __( 'Whether you want custom headers', 'thrive-automator' );
	}

	public static function get_placeholder() {
		return __( 'Headers', 'thrive-automator' );
	}

	public static function get_id() {
		return 'request_headers_toggle';
	}

	public static function get_type() {
		return 'radio';
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_default_value() {
		return 'none';
	}

	public static function get_options_callback( $action_id, $action_data ) {
		return [
			'none'   => [
				'id'    => 'none',
				'label' => __( 'None', 'thrive-automator' ),
			],
			'custom' => [
				'id'    => 'custom',
				'label' => __( 'Custom', 'thrive-automator' ),
			],

		];
	}

	public static function get_preview_template() {
		return '';
	}
}
