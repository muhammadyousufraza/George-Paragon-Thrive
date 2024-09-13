<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Theme\ConditionalDisplay\Entities;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Post extends \TCB\ConditionalDisplay\Entity {
	/**
	 * @return string
	 */
	public static function get_key() {
		return 'post_data';
	}

	public static function get_label() {
		return esc_html__( 'Post or page', 'thrive-theme' );
	}


	public function create_object( $param ) {
		return get_post();
	}

	/**
	 * Determines the display order in the modal select
	 *
	 * @return int
	 */
	public static function get_display_order() {
		return 5;
	}
}
