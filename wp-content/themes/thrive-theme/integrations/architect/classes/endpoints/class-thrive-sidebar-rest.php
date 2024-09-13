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
 * Class Thrive_Sidebar_Rest
 */
class Thrive_Sidebar_Rest {

	public static $namespace = TTB_REST_NAMESPACE;
	public static $route     = '/sidebar';

	public static function register_routes() {
		register_rest_route( static::$namespace, static::$route, [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'get_sidebar' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [ __CLASS__, 'remove_sidebar' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ __CLASS__, 'add_sidebar' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
		] );

		register_rest_route( static::$namespace, static::$route . '/title', [
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ __CLASS__, 'save_title' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
		] );
	}

	/**
	 * Get sidebar content
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response
	 */
	public static function get_sidebar( $request ) {
		$id = $request->get_param( 'id' );

		add_filter( 'tve_leads_lazy_load_forms', '__return_false' );

		ob_start();

		dynamic_sidebar( $id );

		$html = ob_get_clean();

		$response = [
			'html'   => $html,
			'active' => is_active_sidebar( $id ),
		];

		remove_filter( 'tve_leads_lazy_load_forms', '__return_false' );

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * Add new sidebar
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response
	 */
	public static function add_sidebar( $request ) {
		$name       = $request->get_param( 'name' );
		$sidebar_id = 1;

		$sidebars = get_option( THRIVE_SIDEBARS_OPTION, [] );

		if ( empty( $sidebars ) ) {
			$sidebars = [
				[
					'id'   => $sidebar_id,
					'name' => $name,
				],
			];
		} else {
			$sidebar_id = (int) max( array_column( $sidebars, 'id' ) ) + 1;

			$sidebars[] = [
				'id'   => $sidebar_id,
				'name' => $name,
			];
		}

		update_option( THRIVE_SIDEBARS_OPTION, $sidebars );

		return new WP_REST_Response( $sidebar_id, 200 );
	}

	/**
	 * Remove sidebar
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response
	 */
	public static function remove_sidebar( $request ) {

		$id = (int) $request->get_param( 'id' );

		$sidebars = get_option( THRIVE_SIDEBARS_OPTION, [] );

		foreach ( $sidebars as $key => $sidebar ) {
			if ( $sidebar['id'] === $id ) {
				unset( $sidebars[ $key ] );
			}
		}

		update_option( THRIVE_SIDEBARS_OPTION, $sidebars );

		return new WP_REST_Response( $id, 200 );
	}

	/**
	 * Save sidebar title
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response
	 */
	public static function save_title( $request ) {
		$id   = (int) $request->get_param( 'id' );
		$name = $request->get_param( 'name' );

		$sidebars = get_option( THRIVE_SIDEBARS_OPTION, [] );

		foreach ( $sidebars as $key => $sidebar ) {
			if ( $sidebar['id'] === $id ) {
				$sidebars[ $key ]['name'] = $name;
			}
		}

		update_option( THRIVE_SIDEBARS_OPTION, $sidebars );

		return new WP_REST_Response( $id, 200 );
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
}
