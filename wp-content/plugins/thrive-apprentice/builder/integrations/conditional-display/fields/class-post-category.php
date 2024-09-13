<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Theme\ConditionalDisplay\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Post_Category extends \TCB\ConditionalDisplay\Field {
	/**
	 * @return string
	 */
	public static function get_entity() {
		return 'post_data';
	}

	/**
	 * @return string
	 */
	public static function get_key() {
		return 'post_category';
	}

	public static function get_label() {
		return __( 'Category', 'thrive-theme' );
	}

	public static function get_conditions() {
		return [ 'autocomplete' ];
	}

	public function get_value( $post ) {
		$categories = [];

		if ( ! empty( $post ) ) {
			foreach ( get_the_category() as $category ) {
				$categories[] = $category->term_id;
			}
		}

		return $categories;
	}

	public static function get_options( $selected_values = [], $searched_keyword = '' ) {
		$categories = [];

		foreach ( get_categories() as $category ) {
			if ( static::filter_options( $category->term_id, $category->name, $selected_values, $searched_keyword ) ) {
				$categories[] = [
					'value' => (string) $category->term_id,
					'label' => $category->name,
				];
			}
		}

		return $categories;
	}

	/**
	 * @return string
	 */
	public static function get_autocomplete_placeholder() {
		return __( 'Search categories', 'thrive-theme' );
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
