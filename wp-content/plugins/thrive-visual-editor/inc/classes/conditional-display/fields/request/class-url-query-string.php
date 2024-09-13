<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\ConditionalDisplay\Fields\Request;

use TCB\ConditionalDisplay\Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Url_Query_String extends Field {
	/**
	 * @return string
	 */
	public static function get_entity() {
		return 'request_data';
	}

	/**
	 * @return string
	 */
	public static function get_key() {
		return 'url_query_string';
	}

	public static function get_label() {
		return esc_html__( 'URL Query String', 'thrive-cb' );
	}

	public static function get_conditions() {
		return [ 'string_comparison' ];
	}

		public function get_value( $request_data ) {
		return wp_doing_ajax() && isset( $_GET['query_strings'] ) ? $_GET['query_strings'] : $_GET;
	}
}
