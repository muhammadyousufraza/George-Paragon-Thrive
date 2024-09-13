<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

global $post;

if ( ! empty( $post ) ) {
	$categories = get_the_category( $post->ID );
}

if ( empty( $categories ) ) {
	echo TCB_Editor()->is_inner_frame() || TCB_Utils::is_rest() ? esc_html__( 'No Categories', 'thrive-cb' ) : ''; // phpcs:ignore
} else {
	$categories = array_map( function ( $category ) use ( $data, $post ) {
		$url = get_category_link( $category->term_id );

		$attrs = array(
			'href'     => $url,
			'title'    => $category->name,
			'data-css' => empty( $data['css'] ) ? '' : $data['css'],
		);

		if ( ! empty( $data['target'] ) && ( $data['target'] === '1' ) ) {
			$attrs['target'] = '_blank';
		}

		if ( ! empty( $data['rel'] ) && ( $data['rel'] === '1' ) ) {
			$attrs['rel'] = 'nofollow';
		}

		return empty( $url ) || empty( $data['link'] )
			? $category->name
			: TCB_Utils::wrap_content( $category->name, 'a', '', '', $attrs );

	}, $categories );

	echo implode( ', ', $categories ); //phpcs:ignore
}
