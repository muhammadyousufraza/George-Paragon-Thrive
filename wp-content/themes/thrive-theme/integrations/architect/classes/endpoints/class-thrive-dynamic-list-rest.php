<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Dynamic_List_Rest
 */
class Thrive_Dynamic_List_Rest {

	public static $namespace = TTB_REST_NAMESPACE;
	public static $route     = '/list';

	public static function register_routes() {

		register_rest_route( static::$namespace, static::$route, [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'get_list' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
		] );

		register_rest_route( static::$namespace, static::$route . '/terms', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'get_terms' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
		] );
	}

	/**
	 * Check if a given request has access to route
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public static function route_permission( $request ) {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get dynamic list content by type
	 *
	 * @param $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function get_list( $request ) {
		$args             = $request->get_param( 'args' );
		$use_demo_content = ! empty( $request->get_param( 'demo-content' ) );

		if ( $use_demo_content ) {
			/* initialize demo content data */
			Thrive_Demo_Content::init( true );
		}

		$args = Thrive_Shortcodes::parse_attr( $args, 'thrive_dynamic_list' );

		$content = Thrive_Shortcodes::dynamic_list( $args, $use_demo_content );

		return new WP_REST_Response( [
			'success' => 1,
			'content' => $content,
		] );

	}

	/**
	 * Get dynamic list items
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return array|mixed
	 */
	public static function get_terms( $request ) {
		$terms = [];
		$args  = $request->get_params();

		$theme_list = new Thrive_Theme_List( [ 'query' => $args ], false );
		$items      = $theme_list->get_items();

		foreach ( $items as $id => $item ) {
			$terms[] = [
				'value' => $id,
				'label' => $item['name'],
			];
		}

		return new WP_REST_Response( $terms );
	}
}
