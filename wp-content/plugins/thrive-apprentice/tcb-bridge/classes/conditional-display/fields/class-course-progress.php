<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Architect\ConditionalDisplay\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Course_Progress extends \TCB\ConditionalDisplay\Field {
	/**
	 * @return string
	 */
	public static function get_entity() {
		return 'course_data';
	}

	/**
	 * @return string
	 */
	public static function get_key() {
		return 'course_progress';
	}

	public static function get_label() {
		return __( 'Course progress', 'thrive-apprentice' );
	}

	public static function get_conditions() {
		return [ 'number_comparison' ];
	}

	public function get_value( $course ) {
		return empty( $course ) ? '' : \TVA\Architect\Dynamic_Actions\tcb_tva_dynamic_actions()->get_progress_by_type( 'course' );
	}

	/**
	 * @return array
	 */
	public static function get_validation_data() {
		return [
			'min' => 0,
			'max' => 100,
		];
	}

	/**
	 * Determines the display order in the modal field select
	 *
	 * @return int
	 */
	public static function get_display_order() {
		return 30;
	}
}
