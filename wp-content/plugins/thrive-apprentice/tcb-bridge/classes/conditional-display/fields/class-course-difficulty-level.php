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

class Course_Difficulty_Level extends \TCB\ConditionalDisplay\Field {
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
		return 'course_difficulty_level';
	}

	public static function get_label() {
		return __( 'Difficulty level', 'thrive-apprentice' );
	}

	public static function get_conditions() {
		return [ 'checkbox' ];
	}

	public function get_value( $course ) {
		return empty( $course ) ? '' : $course->get_level_id();
	}

	public static function get_options( $selected_values = [], $searched_keyword = '' ) {
		$levels = [];

		foreach ( \TVA_Level::get_items() as $level ) {
			if ( static::filter_options( $level->id, $level->name, $selected_values, $searched_keyword ) ) {
				$levels[] = [
					'value' => (string) $level->id,
					'label' => $level->name,
				];
			}
		}

		return $levels;
	}

	/**
	 * Determines the display order in the modal field select
	 *
	 * @return int
	 */
	public static function get_display_order() {
		return 25;
	}
}
