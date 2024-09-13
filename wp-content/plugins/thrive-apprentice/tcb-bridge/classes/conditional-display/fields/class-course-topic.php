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

class Course_Topic extends \TCB\ConditionalDisplay\Field {
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
		return 'course_topic';
	}

	public static function get_label() {
		return __( 'Course topic', 'thrive-apprentice' );
	}

	public static function get_conditions() {
		return [ 'autocomplete' ];
	}

	public function get_value( $course ) {
		return empty( $course ) ? '' : $course->get_topic_id();
	}

	public static function get_options( $selected_values = [], $searched_keyword = '' ) {
		$topics = [];

		foreach ( \TVA_Topic::get_items() as $topic ) {
			if ( static::filter_options( $topic->id, $topic->title, $selected_values, $searched_keyword ) ) {
				$topics[] = [
					'value' => (string) $topic->id,
					'label' => $topic->title,
				];
			}
		}

		return $topics;
	}

	/**
	 * @return string
	 */
	public static function get_autocomplete_placeholder() {
		return __( 'Search topics', 'thrive-apprentice' );
	}

	/**
	 * Determines the display order in the modal field select
	 *
	 * @return int
	 */
	public static function get_display_order() {
		return 10;
	}
}
