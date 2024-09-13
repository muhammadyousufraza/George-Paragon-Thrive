<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Check if a specific string can be found in a template or section content
 *
 * @param boolean $has_string
 * @param string  $string
 * @param int     $post_id
 *
 * @return boolean
 */
add_filter( 'tcb_architect_content_has_string', static function ( $has_string, $string, $post_id ) {

	if ( ! $has_string ) {
		$posts = get_posts( [
			'posts_per_page' => 1,
			'post_type'      => THRIVE_TEMPLATE,
			'meta_query'     => [
				[
					'key'     => 'sections',
					'value'   => $string,
					'compare' => 'LIKE',
				],
			],
		] );

		if ( empty( $posts ) ) {
			$posts = get_posts( [
				'posts_per_page' => 1,
				'post_type'      => THRIVE_SECTION,
				'meta_query'     => [
					[
						'key'     => 'content',
						'value'   => $string,
						'compare' => 'LIKE',
					],
				],
			] );

			if ( ! empty( $posts ) ) {
				$has_string = true;
			}
		} else {
			$has_string = true;
		}
	}

	return $has_string;
}, 11, 3 );
