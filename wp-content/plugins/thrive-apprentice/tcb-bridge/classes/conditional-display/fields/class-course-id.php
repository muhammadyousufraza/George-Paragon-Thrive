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

class Course_Id extends \TCB\ConditionalDisplay\Field {
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
		return 'course_id';
	}

	public static function get_label() {
		return __( 'Course title', 'thrive-apprentice' );
	}

	public static function get_conditions() {
		return [ 'autocomplete' ];
	}

	/**
	 * @param \TVA_Course_V2 $course
	 *
	 * @return string
	 */
	public function get_value( $course ) {
		return empty( $course ) ? '' : $course->get_id();
	}

	public static function get_options( $selected_values = [], $searched_keyword = '' ) {
		$courses = [];

		$query = [];

		if ( ! empty( $selected_values ) ) {
			$query['include'] = $selected_values;
		}
		if ( ! empty( $searched_keyword ) ) {
			$query['search'] = $searched_keyword;
		}

		$count = empty( $selected_values ) ? min( 100, max( 20, strlen( $searched_keyword ) * 3 ) ) : - 1;

		foreach ( \TVA_Course_V2::get_items( $query, $count ) as $course ) {
			if ( static::filter_options( $course->get_id(), $course->name, $selected_values, $searched_keyword ) ) {
				$courses[] = [
					'value' => (string) $course->get_id(),
					'label' => $course->name,
				];
			}
		}

		return $courses;
	}

	/**
	 * @return string
	 */
	public static function get_autocomplete_placeholder() {
		return __( 'Search courses', 'thrive-apprentice' );
	}

	/**
	 * Determines the display order in the modal field select
	 *
	 * @return int
	 */
	public static function get_display_order() {
		return 0;
	}
}
