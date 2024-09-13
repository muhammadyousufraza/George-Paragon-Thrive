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

class Post_Format extends \TCB\ConditionalDisplay\Field {
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
		return 'post_format';
	}

	public static function get_label() {
		return __( 'Post format', 'thrive-theme' );
	}

	public static function get_conditions() {
		return [ 'dropdown' ];
	}

	public function get_value( $post ) {
		$format = get_post_format();

		return empty( $format ) ? 'standard' : $format;
	}

	public static function get_options( $selected_values = [], $search = '' ) {
		$formats = [
			[
				'label' => __( 'Standard', 'thrive-theme' ),
				'value' => 'standard',
			],
		];

		foreach ( \Thrive_Theme::post_formats() as $format ) {
			$formats[] = [
				'label' => ucfirst( $format ),
				'value' => $format,
			];
		}

		return $formats;
	}

	/**
	 * Determines the display order in the modal field select
	 *
	 * @return int
	 */
	public static function get_display_order() {
		return 35;
	}
}
