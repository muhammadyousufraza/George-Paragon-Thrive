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

class Request_Method extends Action_Field {

	public static function get_name() {
		return __( 'Request type', 'thrive-automator' );
	}

	public static function get_description() {
		return __( 'Pick the desired request method.', 'thrive-automator' );
	}

	public static function get_placeholder() {
		return __( 'Post', 'thrive-automator' );
	}

	public static function get_id() {
		return 'request_method';
	}

	public static function get_type() {
		return 'select';
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_default_value() {
		return 'post';
	}

	public static function get_options_callback( $action_id, $action_data ) {
		return [
			'post' => [
				'id'    => 'post',
				'label' => 'POST',
			],
			'get'  => [
				'id'    => 'get',
				'label' => 'GET',
			],
			'put'  => [
				'id'    => 'put',
				'label' => 'PUT',
			],
		];
	}

	public static function get_preview_template() {
		return '';
	}
}
