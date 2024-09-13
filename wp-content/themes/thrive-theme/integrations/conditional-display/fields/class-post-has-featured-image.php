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

class Post_Has_Featured_Image extends \TCB\ConditionalDisplay\Field {
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
		return 'post_has_featured_image';
	}

	public static function get_label() {
		return __( 'Has featured image', 'thrive-theme' );
	}

	public static function get_conditions() {
		return [];
	}

	public function get_value( $post ) {
		return empty( $post ) || empty( get_the_post_thumbnail( $post ) ) ? 0 : 1;
	}

	public static function is_boolean() {
		return true;
	}

	/**
	 * Determines the display order in the modal field select
	 *
	 * @return int
	 */
	public static function get_display_order() {
		return 40;
	}
}
