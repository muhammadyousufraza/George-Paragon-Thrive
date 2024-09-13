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
$author_id   = get_user_by( 'id', $author );
$author_role = empty( $author_id->roles ) ? '' : $author_id->roles[0];

if ( empty( $author_role ) ) {
	echo TCB_Editor()->is_inner_frame() || TCB_Utils::is_rest() ? esc_html__( 'No Author Role', 'thrive-cb' ) : '';
} else {
	echo esc_html( $author_role );
}
