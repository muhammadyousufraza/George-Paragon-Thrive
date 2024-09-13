<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVE\Architect\ConditionalDisplay\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class User_Wpfusion_Tags extends \TCB\ConditionalDisplay\Field {
	/**
	 * @return string
	 */
	public static function get_key() {
		return 'user_wpfusion_tags';
	}

	public static function get_label() {
		return __( 'Has WP Fusion tags', 'thrive-dash' );
	}

	public static function get_conditions() {
		return [ 'autocomplete_hidden' ];
	}

	/**
	 * @return string
	 */
	public static function get_entity() {
		return 'user_data';
	}

	public function get_value( $user_data ) {
		$tags = '';
		if ( ! empty( $user_data ) && ! empty( $user_data->ID ) ) {
			$tags = wp_fusion()->user->get_tags( $user_data->ID );
		}

		return empty( $tags ) ? '' : $tags;
	}

	public static function get_options( $selected_values = [], $searched_keyword = '' ) {
		$tag_options = [];

		foreach ( wp_fusion()->settings->get_available_tags_flat() as $key => $tag ) {
			if ( static::filter_options( $key, $tag, $selected_values, $searched_keyword ) ) {
				$tag_options[] = [
					'value' => (string) $key,
					'label' => $tag,
				];
			}
		}

		return $tag_options;
	}

	public static function get_autocomplete_placeholder() {
		return __( 'Search tags', 'thrive-dash' );
	}
}
