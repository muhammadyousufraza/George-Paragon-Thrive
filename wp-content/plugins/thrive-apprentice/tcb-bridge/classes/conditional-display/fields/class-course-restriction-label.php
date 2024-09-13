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

class Course_Restriction_Label extends \TCB\ConditionalDisplay\Field {
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
		return 'course_restriction_label';
	}

	public static function get_label() {
		return __( 'Restricted content level', 'thrive-apprentice' );
	}

	public static function get_conditions() {
		return [ 'checkbox' ];
	}

	public function get_value( $course ) {
		return empty( $course ) ? '' : $course->get_label_id();
	}

	public static function get_options( $selected_values = [], $searched_keyword = '' ) {
		$options = [];

		foreach ( tva_get_labels() as $label ) {
			if ( static::filter_options( $label['ID'], $label['title'], $selected_values, $searched_keyword ) ) {
				$options[] = [
					'value' => (string) $label['ID'],
					'label' => $label['title'],
				];
			}
		}

		return $options;
	}

	/**
	 * Determines the display order in the modal field select
	 *
	 * @return int
	 */
	public static function get_display_order() {
		return 20;
	}
}
