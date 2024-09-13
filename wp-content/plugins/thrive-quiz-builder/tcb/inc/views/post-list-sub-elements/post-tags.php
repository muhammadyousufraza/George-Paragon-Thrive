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
	$tags = get_the_tags( $post->ID );
}

if ( empty( $tags ) ) {

	if ( TCB_Editor()->is_inner_frame() || TCB_Utils::is_rest() ) {
		echo esc_html__( 'No Tags', 'thrive-cb' );
	} elseif ( ! empty( $data['default'] ) ) {
		echo esc_html( $data['default'] );
	}

} else {
	$tags = array_map( function ( $tag ) use ( $data, $post ) {
		$url = get_tag_link( $tag->term_id );

		$attrs = array(
			'href'     => $url,
			'title'    => $tag->name,
			'data-css' => empty( $data['css'] ) ? '' : $data['css'],
		);

		if ( ! empty( $data['target'] ) && ( $data['target'] === '1' ) ) {
			$attrs['target'] = '_blank';
		}

		if ( ! empty( $data['rel'] ) && ( $data['rel'] === '1' ) ) {
			$attrs['rel'] = 'nofollow';
		}

		return empty( $url ) || empty( $data['link'] )
			? $tag->name
			: TCB_Utils::wrap_content( $tag->name, 'a', '', '', $attrs );
	}, $tags );

	echo implode( ', ', $tags ); //phpcs:ignore
}
