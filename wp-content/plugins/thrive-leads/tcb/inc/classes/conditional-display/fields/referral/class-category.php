<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\ConditionalDisplay\Fields\Referral;

use TCB\ConditionalDisplay\Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Category extends Field {
	/**
	 * @return string
	 */
	public static function get_entity() {
		return 'referral_data';
	}

	/**
	 * @return string
	 */
	public static function get_key() {
		return 'referral_category_id';
	}

	public static function get_label() {
		return esc_html__( 'Category', 'thrive-cb' );
	}

	public static function get_conditions() {
		return [ 'autocomplete' ];
	}

	public function get_value( $referral_data ) {
		$categories = [];

		if ( ! empty( $referral_data['post'] ) ) {
			foreach ( get_the_category( $referral_data['post'] ) as $category ) {
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
	public static function get_placeholder_text() {
		return esc_html__( 'Search categories', 'thrive-cb' );
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
