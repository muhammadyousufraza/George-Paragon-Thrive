<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

$title = get_the_title();

if ( empty( $title ) ) {
	echo TCB_Editor()->is_inner_frame() || TCB_Utils::is_rest() ? esc_html__( 'No Title', 'thrive-cb' ) : '';
} else {
	$queried_object = get_queried_object();

	$is_inline_shortcode_without_url = ! empty( $data['inline'] ) && empty( $data['url'] ) && empty( $data['link'] );

	/* when the title is on the same page with its post, the link's href attribute should just be '#' */
	$same_page_as_post = ! empty( $queried_object ) && ! empty( $queried_object->ID ) && $queried_object->ID === get_the_ID();

	if ( $is_inline_shortcode_without_url ) {
		echo $title; // phpcs:ignore
	} else {
		$attrs = array(
			'href'     => $same_page_as_post ? '#' : get_the_permalink(),
			'title'    => $title,
			'data-css' => empty( $data['css'] ) ? '' : $data['css'],
		);

		if ( ! empty( $data['target'] ) && ( $data['target'] === '1' ) ) {
			$attrs['target'] = '_blank';
		}

		if ( ! empty( $data['rel'] ) && ( $data['rel'] === '1' ) ) {
			$attrs['rel'] = 'nofollow';
		}

		echo TCB_Utils::wrap_content( $title, 'a', '', '', $attrs ); // phpcs:ignore
	}
}
