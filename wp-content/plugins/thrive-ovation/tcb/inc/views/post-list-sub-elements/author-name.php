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

$author      = apply_filters( 'tcb_post_author', ( empty( $post ) ? null : $post->post_author ) );
$author_name = get_the_author_meta( 'display_name', $author );

if ( empty( $author ) ) {
	echo TCB_Editor()->is_inner_frame() || TCB_Utils::is_rest() ? esc_html__( 'No Author', 'thrive-cb' ) : '';
} else {
	if ( empty( $data['link'] ) ) {
		echo esc_html( $author_name );
	} else {
		$attrs = array(
			'href'     => get_author_posts_url( $author ),
			'title'    => $author_name,
			'data-css' => empty( $data['css'] ) ? '' : $data['css'],
		);

		if ( ! empty( $data['target'] ) && ( $data['target'] === '1' ) ) {
			$attrs['target'] = '_blank';
		}

		if ( ! empty( $data['rel'] ) && ( $data['rel'] === '1' ) ) {
			$attrs['rel'] = 'nofollow';
		}
		echo TCB_Utils::wrap_content( $author_name, 'a', '', '', $attrs ); // phpcs:ignore
	}
}
