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
 * Class Thrive_Options_Rest
 */
class Thrive_Options_Rest {

	public static $namespace = TTB_REST_NAMESPACE;
	public static $route     = '/options';

	public static $editable_user_meta = [
		'ttb_dismissed_tooltips',
		'ttb_logo_tooltip',
	];

	public static function register_routes() {
		register_rest_route( static::$namespace, static::$route, [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'get_option' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
		] );

		register_rest_route( static::$namespace, static::$route . '/global_colors', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'get_global_colors' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
		] );

		register_rest_route( static::$namespace, static::$route, [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ __CLASS__, 'update_option' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
			],
		] );

		register_rest_route( static::$namespace, static::$route . '/user-option', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ __CLASS__, 'update_user_meta' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
				'args'                => [
					'meta_key' => [
						'type'     => 'string',
						'required' => true,
						'enum'     => static::$editable_user_meta,
					],
				],
			],
		] );
	}

	/**
	 * Get an option.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response
	 */
	public static function get_option( $request ) {
		$option_name = $request->get_param( 'name' );

		$value = get_option( $option_name );

		return new WP_REST_Response( $value, 200 );
	}

	/**
	 * Get the global colors.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function get_global_colors( $request ) {
		$option_name = $request->get_param( 'name' );

		if ( $option_name !== 'thrv_global_colours' ) {
			return new WP_Error( 'cant-get', __( "Option name is not 'thrv_global_colours'.", 'thrive-theme' ), [ 'status' => 500 ] );
		}

		$value = Thrive_Utils::get_used_global_colors();

		return new WP_REST_Response( $value, 200 );
	}

	/**
	 * Creates/updates an option.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function update_option( $request ) {
		$option_name  = $request->get_param( 'name' );
		$option_value = $request->get_param( 'value' );

		$old_value = get_option( $option_name );

		/* If the new value is the same with the old one, return true and don't update.
		 * If the values differ, update.
		 */
		if ( $old_value === $option_value || update_option( $option_name, $option_value ) ) {
			$response = new WP_REST_Response( 'Success', 200 );
		} else {
			$response = new WP_Error( 'cant-update', __( "Couldn't add/update the fields in the database.", 'thrive-theme' ), [ 'status' => 500 ] );
		}

		return $response;
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
	 * Update user meta field
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public static function update_user_meta( $request ) {
		$response = [];

		$user = wp_get_current_user();
		/* double check, just to be sure */
		if ( $user ) {
			$meta_key   = $request->get_param( 'meta_key' );
			$meta_value = $request->get_param( 'meta_value' );
			update_user_meta( $user->ID, $meta_key, $meta_value );
			$response[ $meta_key ] = $meta_value;
		}

		return new WP_REST_Response( $response );
	}
}
