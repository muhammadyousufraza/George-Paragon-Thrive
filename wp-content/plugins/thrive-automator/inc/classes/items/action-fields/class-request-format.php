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

class Request_Format extends Action_Field {

	public static function get_name() {
		return __( 'Request format', 'thrive-automator' );
	}

	public static function get_description() {
		return __( 'Pick the desired request format.', 'thrive-automator' );
	}

	public static function get_placeholder() {
		return __( 'FORM', 'thrive-automator' );
	}

	public static function get_id() {
		return 'request_format';
	}

	public static function get_type() {
		return 'select';
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_default_value() {
		return 'form';
	}

	public static function get_options_callback( $action_id, $action_data ) {
		return [
			'form' => [
				'id'    => 'form',
				'label' => 'FORM',
			],
			'json' => [
				'id'    => 'json',
				'label' => 'JSON',
			],
			'xml'  => [
				'id'    => 'xml',
				'label' => 'XML',
			],
		];
	}

	public static function get_preview_template() {
		return '';
	}
}
