<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

$comments_number = get_comments_number();

/**
 * $data['url'] values:
 * 0 / empty - no url
 * 1 - post url
 * 2 - post comments url
 */
if ( empty( $data['url'] ) ) {
	echo esc_html( $comments_number );
} else {
	global $post;

	$post_url  = get_permalink( $post );
	$link_attr = array(
		'title'  => __( 'Comments Number', 'thrive-cb' ),
		'target' => '_blank',
	);

	switch ( (int) $data['url'] ) {
		case 1:
			$link_attr['href'] = $post_url;
			break;
		case 2:
			$link_attr['href'] = $post_url . '#comments';
			break;
		default:
			break;
	}

	echo TCB_Utils::wrap_content( $comments_number, 'a', '', '', $link_attr ); // phpcs:ignore
}
