<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\ConditionalDisplay\Fields\Time;

use TCB\ConditionalDisplay\Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Current_Time extends Field {
	/**
	 * @return string
	 */
	public static function get_entity() {
		return 'time_data';
	}

	/**
	 * @return string
	 */
	public static function get_key() {
		return 'current_time';
	}

	public static function get_label() {
		return esc_html__( 'Time', 'thrive-cb' );
	}

	public static function get_conditions() {
		return [ 'time' ];
	}

	public function get_value( $data ) {
		return current_time( 'H:i' );
	}

	/**
	 * @return int
	 */
	public static function get_display_order() {
		return 5;
	}
}
