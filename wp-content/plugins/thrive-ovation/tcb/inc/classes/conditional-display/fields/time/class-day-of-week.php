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

class Day_Of_Week extends Field {
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
		return 'day_of_week';
	}

	public static function get_label() {
		return esc_html__( 'Day of week', 'thrive-cb' );
	}

	public static function get_conditions() {
		return [ 'checkbox' ];
	}

	public function get_value( $data ) {
		return strtolower( date( 'l' ) );
	}

	public static function get_options( $selected_values = [], $search = '' ) {
		return [
			[
				'value' => 'monday',
				'label' => __( 'Monday' ),
			],
			[
				'value' => 'tuesday',
				'label' => __( 'Tuesday' ),
			],
			[
				'value' => 'wednesday',
				'label' => __( 'Wednesday' ),
			],
			[
				'value' => 'thursday',
				'label' => __( 'Thursday' ),
			],
			[
				'value' => 'friday',
				'label' => __( 'Friday' ),
			],
			[
				'value' => 'saturday',
				'label' => __( 'Saturday' ),
			],
			[
				'value' => 'sunday',
				'label' => __( 'Sunday' ),
			],
		];
	}

	/**
	 * @return int
	 */
	public static function get_display_order() {
		return 10;
	}
}
