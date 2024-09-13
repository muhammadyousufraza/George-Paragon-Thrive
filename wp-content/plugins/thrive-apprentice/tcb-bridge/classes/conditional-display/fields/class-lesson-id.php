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

class Lesson_Id extends \TCB\ConditionalDisplay\Field {
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
		return 'lesson_id';
	}

	public static function get_label() {
		return __( 'Lesson title', 'thrive-apprentice' );
	}

	public static function get_conditions() {
		return [ 'autocomplete' ];
	}

	/**
	 * @param \TVA_Lesson $lesson
	 *
	 * @return string
	 */
	public function get_value( $lesson ) {
		return empty( $lesson ) ? '' : $lesson->ID;
	}

	public static function get_options( $selected_values = [], $searched_keyword = '' ) {
		$lessons = [];

		$query = [
			'posts_per_page' => empty( $selected_values ) ? min( 100, max( 20, strlen( $searched_keyword ) * 10 ) ) : - 1,
			'post_type'      => array( \TVA_Const::LESSON_POST_TYPE ),
			'post_status'    => \TVA_Post::$accepted_statuses,
			'meta_query'     => [
				[
					'key'     => 'tva_is_demo', /* don't display dummy content */
					'compare' => 'NOT EXISTS',
				],
			],
		];

		if ( ! empty( $searched_keyword ) ) {
			$query['s'] = $searched_keyword;
		}
		if ( ! empty( $selected_values ) ) {
			$query['include'] = $selected_values;
		}

		foreach ( get_posts( $query ) as $lesson ) {
			if ( static::filter_options( $lesson->ID, $lesson->post_title, $selected_values, $searched_keyword ) ) {
				$lessons[] = [
					'value' => (string) $lesson->ID,
					'label' => $lesson->post_title,
				];
			}
		}

		return $lessons;
	}

	/**
	 * @return string
	 */
	public static function get_autocomplete_placeholder() {
		return __( 'Search lessons', 'thrive-apprentice' );
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
