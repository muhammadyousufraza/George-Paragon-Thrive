<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Integrations\SmashBalloon;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Rest_Api
 *
 * @package Thrive\Theme\Integrations\SmashBalloon
 */
class Rest_Api {
	public static $namespace = 'tcb/v1';
	public static $route = '/smash-balloon';

	public static function register_routes() {
		register_rest_route(
			static::$namespace,
			static::$route,
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ __CLASS__, 'get_feeds' ],
					'permission_callback' => '__return_true',
				]
			]
		);

		register_rest_route(
			static::$namespace,
			static::$route . '/shortcode',
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ __CLASS__, 'shortcode_html' ],
					'permission_callback' => '__return_true',
				],
			]
		);
	}

	/**
	 * Get the variations of a product
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_REST_Response
	 */
	public static function get_feeds( $request ) {

		$type = $request->get_param( 'type' );

		switch ( $type ) {
			case 'custom-facebook-feed':
				$feeds = Main::sb_available_feeds( 'facebook' );
				break;

			case 'instagram-feed':
				$feeds = Main::sb_available_feeds( 'instagram' );
				break;

			case 'custom-twitter-feeds':
				$feeds = Main::sb_available_feeds( 'twitter' );
				break;

			case 'youtube-feed':
				$feeds = Main::sb_available_feeds( 'youtube' );
				break;

			case 'tiktok-feeds':
				$feeds = Main::sb_available_feeds( 'tiktok' );
				break;

			case 'social-wall':
				$feeds = Main::sb_available_feeds( 'social-wall' );
				break;

			default:
				// Do nothing.
				break;
		}

		return new \WP_REST_Response( $feeds, 200 );
	}

	/**
	 * Get the variations of a product
	 *
	 * @param \WP_REST_Request $request Full data about the request.
	 *
	 * @return \WP_REST_Response
	 */
	public static function shortcode_html( $request ) {
		$shortcode = $request->get_param( 'shortcode' );

		$html = do_shortcode( $shortcode );

		return new \WP_REST_Response( [ 'html' => $html ], 200 );
	}
}
