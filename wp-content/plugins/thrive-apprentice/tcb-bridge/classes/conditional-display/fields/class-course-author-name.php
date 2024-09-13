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

class Course_Author_Name extends \TCB\ConditionalDisplay\Field {
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
		return 'course_author_name';
	}

	public static function get_label() {
		return __( 'Author name', 'thrive-apprentice' );
	}

	public static function get_conditions() {
		return [ 'autocomplete' ];
	}

	/**
	 * @param $course
	 *
	 * @return string
	 */
	public function get_value( $course ) {
		return empty( $course ) ? '' : $course->author->get_user()->ID;
	}

	public static function get_options( $selected_values = [], $searched_keyword = '' ) {
		$authors = [];

		foreach ( get_users() as $user ) {
			if (
				static::filter_options( $user->ID, $user->data->display_name, $selected_values, $searched_keyword ) &&
				user_can( $user, 'publish_posts' )
			) {
				$authors[] = [
					'value' => (string) $user->ID,
					'label' => $user->data->display_name,
				];
			}
		}

		return $authors;
	}

	/**
	 * @return string
	 */
	public static function get_autocomplete_placeholder() {
		return __( 'Search authors', 'thrive-apprentice' );
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
