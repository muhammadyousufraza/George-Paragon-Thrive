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

$author             = apply_filters( 'tcb_post_author', ( empty( $post ) ? null : $post->post_author ) );
$author_description = get_the_author_meta( 'description', $author );

if ( empty( $author_description ) ) {
	echo TCB_Editor()->is_inner_frame() || TCB_Utils::is_rest() ? esc_html__( 'No Author Description', 'thrive-cb' ) : '';
} else {
	echo $author_description; //phpcs:ignore
}
