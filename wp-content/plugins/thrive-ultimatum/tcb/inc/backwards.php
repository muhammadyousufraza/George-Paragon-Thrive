<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Search symbols, lightboxes and ct if they have a specific string in their architect content
 */
add_filter( 'tcb_architect_content_has_string', static function ( $has_string, $string, $post_id ) {

	$architect_content = tve_get_post_meta( $post_id, 'tve_updated_post' );

	if ( ! empty( $architect_content ) ) {
		$has_string = $has_string || ( strpos( $architect_content, $string ) !== false );
	}

	if ( ! $has_string ) {
		$posts = get_posts( [
			'posts_per_page' => 1,
			'post_type'      => [ TCB_Symbols_Post_Type::SYMBOL_POST_TYPE, 'tcb_lightbox', TCB_CT_POST_TYPE ],
			'meta_query'     => [
				[
					'key'     => 'tve_updated_post',
					'value'   => $string,
					'compare' => 'LIKE',
				],
			],
		] );

		if ( ! empty( $posts ) ) {
			$has_string = true;
		}
	}

	return $has_string;
}, 10, 3 );
