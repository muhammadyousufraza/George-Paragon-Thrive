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

class Post extends Field {

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
		return 'referral_post_id';
	}

	public static function get_label() {
		return esc_html__( 'Post', 'thrive-cb' );
	}

	public static function get_conditions() {
		return [ 'autocomplete' ];
	}

	public function get_value( $referral_data ) {
		return empty( $referral_data['post'] ) ? '' : $referral_data['post']->ID;
	}

	public static function get_options( $selected_values = [], $searched_keyword = '' ) {
		$query = [
			'posts_per_page' => empty( $selected_values ) ? min( 100, max( 20, strlen( $searched_keyword ) * 3 ) ) : - 1,
			'post_type'      => static::get_post_type(),
			'orderby'        => 'title',
			'order'          => 'ASC',
		];

		if ( ! empty( $searched_keyword ) ) {
			$query['s'] = $searched_keyword;
		}
		if ( ! empty( $selected_values ) ) {
			$query['include'] = $selected_values;
		}

		$posts = [];

		foreach ( get_posts( $query ) as $post ) {
			if ( static::filter_options( $post->ID, $post->post_title, $selected_values, $searched_keyword ) ) {
				$posts[] = [
					'value' => (string) $post->ID,
					'label' => $post->post_title,
				];
			}
		}

		return $posts;
	}

	public static function get_post_type() {
		return 'post';
	}

	/**
	 * @return string
	 */
	public static function get_placeholder_text() {
		return esc_html__( 'Search posts', 'thrive-cb' );
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
