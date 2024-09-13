<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Theme\AMP\Video;

use TCB_Utils;

use DOMElement;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Youtube
 * @package Thrive\Theme\AMP\Video
 */
class Youtube {

	const KEY = 'youtube';
	const TAG = 'amp-youtube';

	/* typical youtube video ratio */
	const RATIO = 0.5625;

	const SIZE_WIDTH = 400;

	/**
	 * @param string $url
	 *
	 * @return string
	 */
	public static function get_amp_video( $url ) {
		$video_id = static::get_video_id( $url );

		if ( empty( $video_id ) ) {
			$video_html = '';
		} else {
			$video_html = TCB_Utils::wrap_content( '', static::TAG, '', '', [
				'width'        => static::SIZE_WIDTH,
				'height'       => static::SIZE_WIDTH * static::RATIO,
				'data-videoid' => $video_id,
				'layout'       => 'responsive',
			] );
		}


		return $video_html;
	}

	/**
	 * Extract the video ID from the youtube URL
	 *
	 * @param string $url
	 *
	 * @return mixed|string
	 */
	public static function get_video_id( $url ) {
		$parsed_url = parse_url( $url );

		if ( isset( $parsed_url['query'] ) ) {
			/* https://www.youtube.com/watch?v=ScMzIvxBSi4 -> v=ScMzIvxBSi4 -> result */
			parse_str( $parsed_url['query'], $query_params );

			$id = isset( $query_params['v'] ) ? $query_params['v'] : '';
		} else {
			/* https://youtu.be/ScMzIvxBSi4 -> /ScMzIvxBSi4 -> result */
			$id = isset( $parsed_url['path'] ) ? ltrim( $parsed_url['path'], '/' ) : '';
		}

		return $id;
	}
}
