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

class Day_Of_Month extends Field {
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
		return 'day_of_month';
	}

	public static function get_label() {
		return esc_html__( 'Day of month', 'thrive-cb' );
	}

	public static function get_conditions() {
		return [ 'number_equals' ];
	}

	public function get_value( $data ) {
		return (int) date( 'd' );
	}

	/**
	 * @return array
	 */
	public static function get_validation_data() {
		return [
			'min' => 1,
			'max' => 31,
		];
	}

	/**
	 * @return int
	 */
	public static function get_display_order() {
		return 15;
	}
}
