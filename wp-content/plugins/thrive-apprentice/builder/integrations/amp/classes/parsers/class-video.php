<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Theme\AMP\Parsers;

use Thrive\Theme\AMP\Parser;
use Thrive\Theme\AMP\Video\Youtube;

use Thrive_DOM_Helper as DOM_Helper;

use DOMDocument;
use DOMElement;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Video
 * @package Thrive\Theme\AMP\Parsers
 */
class Video {

	const VIDEO_CLASS = 'thrv_responsive_video';

	/* Hooks into the main parse action. Called dynamically from class-parser.php */
	public static function init() {
		add_action( Parser::PARSE_HOOK, [ __CLASS__, 'parse_videos' ] );
	}

	/**
	 * @param DOMDocument $dom
	 */
	public static function parse_videos( $dom ) {
		$video_nodes = DOM_Helper::get_all_nodes_for_tag_and_class( $dom, 'div', static::VIDEO_CLASS );

		foreach ( $video_nodes as $video_node ) {
			static::replace_video( $video_node, $dom );
		}
	}

	/**
	 * @param DOMElement  $container
	 * @param DOMDocument $dom
	 */
	public static function replace_video( $container, $dom ) {
		$new_video_html = '';

		$url = static::get_video_url_from_node( $container );

		$type = $container->getAttribute( 'data-type' );

		if ( $type === 'dynamic' ) {
			$type = static::get_type_from_iframe( $container );
		}

		switch ( $type ) {
			case Youtube::KEY:
				$new_video_html = Youtube::get_amp_video( $url );
				break;
			//todo cover more video types here
			default:
				break;
		}

		if ( empty( $new_video_html ) ) {
			DOM_Helper::delete_node( $container );
		} else {
			DOM_Helper::replace_node_with_string( $container, $new_video_html, $dom );
		}
	}

	/**
	 * Get the video URL from the dataset. If we don't find it in the container, look for it in the iframe.
	 *
	 * @param DOMElement $video_container
	 *
	 * @return string
	 */
	public static function get_video_url_from_node( $video_container ) {
		$url = $video_container->getAttribute( 'data-url' );

		/* if the url is empty, get it from the iframe tag */
		if ( empty( $url ) ) {
			/* @var DOMElement $iframe */
			$iframe = static::get_iframe_node( $video_container );

			if ( $iframe !== null ) {
				$url = $iframe->getAttribute( 'data-src' );
			}
		}

		return empty( $url ) ? '' : $url;
	}

	/**
	 * Used when the video type is dynamic
	 *
	 * @param DOMElement $video_container
	 *
	 * @return mixed
	 */
	public static function get_type_from_iframe( $video_container ) {
		$iframe = static::get_iframe_node( $video_container );

		return $iframe === null ? '' : $iframe->getAttribute( 'data-provider' );
	}

	/**
	 * @param DOMElement $video_container
	 *
	 * @return mixed
	 */
	public static function get_iframe_node( $video_container ) {
		return $video_container->getElementsByTagName( 'iframe' )->item( 0 );
	}
}
