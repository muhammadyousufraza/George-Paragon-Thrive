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

class Post_Type extends \TCB\ConditionalDisplay\Field {
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
		return 'post_type';
	}

	public static function get_label() {
		return __( 'Post type', 'thrive-theme' );
	}

	public static function get_conditions() {
		return [ 'autocomplete' ];
	}

	public function get_value( $post ) {
		return empty( $post ) ? '' : $post->post_type;
	}

	public static function get_options( $selected_values = [], $searched_keyword = '' ) {
		$post_types = [];

		foreach ( \Thrive_Utils::get_post_types() as $post_type => $label ) {
			if ( static::filter_options( $post_type, $label, $selected_values, $searched_keyword ) ) {
				$post_types[] = [
					'value' => $post_type,
					'label' => $label,
				];
			}
		}

		return $post_types;
	}

	/**
	 * @return string
	 */
	public static function get_autocomplete_placeholder() {
		return __( 'Search post types', 'thrive-theme' );
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
