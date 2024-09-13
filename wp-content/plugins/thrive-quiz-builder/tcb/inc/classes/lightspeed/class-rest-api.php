<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace TCB\Lightspeed;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Rest_Api
 *
 * @package TCB\Lightspeed
 */
class Rest_Api {

	const  REST_NAMESPACE = 'tcb/v1';
	const  REST_ROUTE = 'lightspeed/';

	public static function register_routes() {
		register_rest_route( static::REST_NAMESPACE, static::REST_ROUTE . 'analyze', [
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'analyze' ],
				'permission_callback' => [ __CLASS__, 'has_admin_access' ],
				'args'                => [
					'to_analyze' => [
						'type'     => 'array',
						'required' => false,
					],
				],
			],
		] );

		register_rest_route( static::REST_NAMESPACE, static::REST_ROUTE . 'options', [
			[
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => [ __CLASS__, 'options' ],
				'permission_callback' => [ __CLASS__, 'has_admin_access' ],
				'args'                => [
					'key'   => [
						'type'              => 'string',
						'required'          => true,
						'validate_callback' => static function ( $key ) {
							return in_array( $key, [
								Main::ENABLE_LIGHTSPEED_OPTION,
								Fonts::ENABLE_ASYNC_FONTS_LOAD,
								Fonts::ENABLE_FONTS_OPTIMIZATION,
								Fonts::DISABLE_GOOGLE_FONTS,
								Gutenberg::DISABLE_GUTENBERG,
								Gutenberg::DISABLE_GUTENBERG_LP,
								Woocommerce::DISABLE_WOOCOMMERCE,
								Woocommerce::DISABLE_WOOCOMMERCE_LP,
								Emoji::DISABLE_EMOJI,
							], true );
						},
					],
					'value' => [
						'type'              => 'int',
						'required'          => true,
						'validate_callback' => static function ( $value ) {
							return is_numeric( $value );
						},
					],
				],
			],
		] );

		register_rest_route( static::REST_NAMESPACE, static::REST_ROUTE . 'optimize', [
			[
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => [ __CLASS__, 'optimize' ],
				'permission_callback' => [ __CLASS__, 'can_edit_posts' ],
			],
		] );
	}

	/**
	 * Analyze posts/pages/templates ... to see what is optimized and what not
	 *
	 * @return \WP_REST_Response
	 */
	public static function analyze( $request ) {
		/* make sure there's enough memory to analyze lots of content */
		@ini_set( 'memory_limit', TVE_EXTENDED_MEMORY_LIMIT );
		$groups     = [];
		$to_analyze = $request->get_param( 'to_analyze' );

		if ( ! empty( $to_analyze ) ) {
			if ( in_array( 'lp', $to_analyze ) ) {
				$groups = Main::get_lp_for_optimize();
			}
			if ( in_array( 'ttb', $to_analyze ) ) {
				$groups = Main::get_content_for_optimization( $request );
			}

		} else {
			/**
			 * returns an array of items to be optimized
			 *
			 * @return array
			 */
			$groups = Main::get_architect_posts_for_optimization();
		}

		$groups = apply_filters( 'tve_lightspeed_items_to_optimize', $groups, $request );

		return new \WP_REST_Response( $groups );
	}

	/**
	 * Enable/disable lightspeed
	 *
	 * @param \WP_REST_Request $request data about the request.
	 *
	 * @return \WP_REST_Response
	 */
	public static function options( $request ) {
		$key   = $request->get_param( 'key' );
		$value = (int) $request->get_param( 'value' );

		update_option( $key, $value );

		return new \WP_REST_Response( $value );
	}

	/**
	 * Optimize assets
	 *
	 * @param \WP_REST_Request $request data about the request.
	 *
	 * @return \WP_REST_Response
	 */
	public static function optimize( $request ) {
		$post_id = (int) $request->get_param( 'id' );
		$key     = empty( $request->get_param( 'key' ) ) ? '' : '_' . $request->get_param( 'key' );

		Main::handle_optimize_saves( $post_id, $request, $key );

		/**
		 * Action called after an item has been optimized
		 *
		 * @param int              $post_id
		 * @param int|string       $key
		 * @param \WP_REST_Request $request
		 */
		do_action( 'tcb_lightspeed_item_optimized', $post_id, $request->get_param( 'key' ), $request );

		return new \WP_REST_Response( [ 'success' => 1 ] );
	}

	/**
	 * Allow only admins to access rest routes
	 *
	 * @return bool
	 */
	public static function has_admin_access() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Allow only users that can edit posts on this route
	 *
	 * @param \WP_REST_Request $request data about the request.
	 *
	 * @return bool
	 */
	public static function can_edit_posts( $request ) {
		return current_user_can( 'edit_post', $request->get_param( 'id' ) );
	}
}
