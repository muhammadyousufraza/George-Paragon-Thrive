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

class Post_Status extends \TCB\ConditionalDisplay\Field {
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
		return 'post_status';
	}

	public static function get_label() {
		return __( 'Post status', 'thrive-theme' );
	}

	public static function get_conditions() {
		return [ 'dropdown' ];
	}

	public function get_value( $post ) {
		return empty( $post ) ? '' : $post->post_status;
	}

	public static function get_options( $selected_values = [], $search = '' ) {
		$statuses = [];

		foreach ( get_post_stati() as $status ) {
			$statuses[] = [
				'label' => ucfirst( $status ),
				'value' => $status,
			];
		}

		return $statuses;
	}

	/**
	 * Determines the display order in the modal field select
	 *
	 * @return int
	 */
	public static function get_display_order() {
		return 30;
	}
}
