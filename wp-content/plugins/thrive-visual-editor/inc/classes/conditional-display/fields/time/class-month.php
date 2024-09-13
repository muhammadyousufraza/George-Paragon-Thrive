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

class Month extends Field {
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
		return 'month';
	}

	public static function get_label() {
		return esc_html__( 'Month', 'thrive-cb' );
	}

	public static function get_conditions() {
		return [ 'checkbox' ];
	}

	public function get_value( $data ) {
		return (int) date( 'n' );
	}

	public static function get_options( $selected_values = [], $search = '' ) {
		return [
			[
				'value' => '1',
				'label' => __( 'January' ),
			],
			[
				'value' => '2',
				'label' => __( 'February' ),
			],
			[
				'value' => '3',
				'label' => __( 'March' ),
			],
			[
				'value' => '4',
				'label' => __( 'April' ),
			],
			[
				'value' => '5',
				'label' => __( 'May' ),
			],
			[
				'value' => '6',
				'label' => __( 'June' ),
			],
			[
				'value' => '7',
				'label' => __( 'July' ),
			],
			[
				'value' => '8',
				'label' => __( 'August' ),
			],
			[
				'value' => '9',
				'label' => __( 'September' ),
			],
			[
				'value' => '10',
				'label' => __( 'October' ),
			],
			[
				'value' => '11',
				'label' => __( 'November' ),
			],
			[
				'value' => '12',
				'label' => __( 'December' ),
			],
		];
	}

	/**
	 * @return int
	 */
	public static function get_display_order() {
		return 20;
	}
}
