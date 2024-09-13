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

class Post_Tag extends \TCB\ConditionalDisplay\Field {
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
		return 'post_tag';
	}

	public static function get_label() {
		return __( 'Tag', 'thrive-theme' );
	}

	public static function get_conditions() {
		return [ 'autocomplete' ];
	}

	public function get_value( $post ) {
		$tags = [];

		if ( ! empty( $post ) ) {
			$post_tags = get_the_tags();

			if ( ! empty( $post_tags ) ) {
				foreach ( get_the_tags() as $tag ) {
					$tags[] = $tag->term_id;
				}
			}
		}

		return $tags;
	}

	public static function get_options( $selected_values = [], $searched_keyword = '' ) {
		$tags = [];

		foreach ( get_tags() as $tag ) {
			if ( static::filter_options( $tag->term_id, $tag->name, $selected_values, $searched_keyword ) ) {
				$tags[] = [
					'value' => (string) $tag->term_id,
					'label' => $tag->name,
				];
			}
		}

		return $tags;
	}

	/**
	 * @return string
	 */
	public static function get_autocomplete_placeholder() {
		return __( 'Search tags', 'thrive-theme' );
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
