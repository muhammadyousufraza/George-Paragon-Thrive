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

class Post_Comment_Count extends \TCB\ConditionalDisplay\Field {
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
		return 'post_comment_count';
	}

	public static function get_label() {
		return __( 'Comment count', 'thrive-theme' );
	}

	public static function get_conditions() {
		return [ 'number_comparison' ];
	}

	public function get_value( $post ) {
		return empty( $post ) ? '' : (int) $post->comment_count;
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
