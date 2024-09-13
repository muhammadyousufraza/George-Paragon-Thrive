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

class Lesson_Type extends \TCB\ConditionalDisplay\Field {
	/**
	 * @return string
	 */
	public static function get_entity() {
		return 'lesson_data';
	}

	/**
	 * @return string
	 */
	public static function get_key() {
		return 'lesson_type';
	}

	public static function get_label() {
		return __( 'Lesson type', 'thrive-apprentice' );
	}

	public static function get_conditions() {
		return [ 'checkbox' ];
	}

	/**
	 * @param \TVA_Lesson $lesson
	 *
	 * @return string
	 */
	public function get_value( $lesson ) {
		return empty( $lesson ) ? '' : $lesson->get_type();
	}


	public static function get_options( $selected_values = [], $searched_keyword = '' ) {
		$types = [];

		foreach ( [ 'text', 'audio', 'video' ] as $type ) {
			$types[] = [
				'value' => $type,
				'label' => ucfirst( $type ) . ' lesson',
			];
		}

		return $types;
	}

	/**
	 * Determines the display order in the modal field select
	 *
	 * @return int
	 */
	public static function get_display_order() {
		return 5;
	}
}
