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

class Tag extends Field {
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
		return 'referral_tag_id';
	}

	public static function get_label() {
		return esc_html__( 'Tag', 'thrive-cb' );
	}

	public static function get_conditions() {
		return [ 'autocomplete' ];
	}

	public function get_value( $referral_data ) {
		$tags = [];

		if ( ! empty( $referral_data['post'] ) ) {
			$post_tags = get_the_tags( $referral_data['post'] );

			if ( ! empty( $post_tags ) ) {
				foreach ( $post_tags as $tag ) {
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
	public static function get_placeholder_text() {
		return esc_html__( 'Search tags', 'thrive-cb' );
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
