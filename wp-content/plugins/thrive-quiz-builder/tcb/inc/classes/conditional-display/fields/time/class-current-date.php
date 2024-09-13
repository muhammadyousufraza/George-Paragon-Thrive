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

class Current_Date extends Field {
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
		return 'current_date';
	}

	public static function get_label() {
		return esc_html__( 'Date', 'thrive-cb' );
	}

	public static function get_conditions() {
		return [ 'date' ];
	}

	public function get_value( $data ) {
		return current_time( 'Y/m/d H:i' );
	}

	/**
	 * @return int
	 */
	public static function get_display_order() {
		return 0;
	}
}
