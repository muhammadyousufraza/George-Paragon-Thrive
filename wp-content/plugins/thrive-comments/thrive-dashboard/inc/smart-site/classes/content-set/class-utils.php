<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVD\Content_Sets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Set_Utils
 *
 * @package TVD\Content_Sets
 * @project : thrive-dashboard
 */
class Utils {
	/**
	 * Currently, only supports taxonomy terms and posts
	 *
	 * @return bool
	 */
	public static function is_context_supported() {
		return is_singular() || is_tax() || is_category() || is_home() || is_search() || is_author();
	}

	/**
	 * @param \WP_Post| \WP_Term $post_or_term
	 *
	 * @return array
	 */
	public static function get_post_or_term_parts( $post_or_term ) {
		$type = '';
		$id   = 0;

		if ( $post_or_term instanceof \WP_Post ) {
			$type = $post_or_term->post_type;
			$id   = $post_or_term->ID;
		} else if ( $post_or_term instanceof \WP_Term ) {
			$type = $post_or_term->taxonomy;
			$id   = $post_or_term->term_id;
		}

		return array(
			$type,
			$id,
		);
	}
}