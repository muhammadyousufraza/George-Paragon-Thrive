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

class Post_Id extends \TCB\ConditionalDisplay\Field {
	/*
	 * This used to be 3 and I decided to increase it to 5 because there were support cases where there were not enough fetched posts from the query.
	 * Basically the user had a lot of content that was matching the keyword in some way in the WP search.
	 * For reference see SUPP-15081.
	 */
	const SEARCHED_KEYWORD_COUNT_MULTIPLIER = 5;

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
		return 'post_id';
	}

	public static function get_label() {
		return __( 'Title', 'thrive-theme' );
	}

	public static function get_conditions() {
		return [ 'autocomplete' ];
	}

	public function get_value( $post ) {
		return empty( $post ) ? '' : $post->ID;
	}

	public static function get_options( $selected_values = [], $searched_keyword = '' ) {
		$post_types          = array_keys( \Thrive_Utils::get_content_types() );
		$excluded_post_types = apply_filters( 'tcb_conditional_display_post_excluded_types', [] );

		$post_types = array_diff( $post_types, $excluded_post_types );

		$query = [
			'posts_per_page' => empty( $selected_values ) ? min( 100, max( 20, strlen( $searched_keyword ) * static::SEARCHED_KEYWORD_COUNT_MULTIPLIER ) ) : - 1,
			'post_type'      => $post_types,
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

	/**
	 * @return string
	 */
	public static function get_autocomplete_placeholder() {
		return __( 'Search posts', 'thrive-theme' );
	}

	/**
	 * Determines the display order in the modal field select
	 *
	 * @return int
	 */
	public static function get_display_order() {
		return 0;
	}
}
