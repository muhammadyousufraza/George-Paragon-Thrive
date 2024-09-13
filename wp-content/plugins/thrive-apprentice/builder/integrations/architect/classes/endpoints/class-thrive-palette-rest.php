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
 * Class Thrive_Palette_Rest
 *
 * @project  : thrive-theme
 */
class Thrive_Palette_Rest {
	public static $namespace = TTB_REST_NAMESPACE;

	public static $route = '/palette';

	public static function register_routes() {
		register_rest_route( static::$namespace, static::$route, [
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ __CLASS__, 'update' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
				'args'                => [
					'hsl' => [
						'type'     => 'object',
						'required' => true,
					],
				],
			],
		] );
		register_rest_route( static::$namespace, static::$route . '/update_auxiliary_variable', [
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ __CLASS__, 'update_auxiliary' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
				'args'                => [
					'id'    => [
						'type'     => 'integer',
						'required' => true,
					],
					'color' => [
						'type'     => 'string',
						'required' => true,
					],
				],
			],
		] );

	}

	/**
	 * Check if the user can update settings
	 *
	 * @return bool
	 */
	public static function route_permission() {
		return Thrive_Theme_Product::has_access() && current_user_can( 'manage_options' );
	}

	/**
	 * Update skin palette configuration and master variables settings
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public static function update( $request ) {
		$hsl = $request->get_param( 'hsl' );

		thrive_palettes()->update_master_hsl( $hsl );
		$config = thrive_skin()->get_palettes();

		$active_id = (int) $config['active_id'];

		$config['palettes'][ $active_id ]['modified_hsl']['h'] = (float) $hsl['h'];
		$config['palettes'][ $active_id ]['modified_hsl']['s'] = (float) $hsl['s'];
		$config['palettes'][ $active_id ]['modified_hsl']['l'] = (float) $hsl['l'];

		thrive_skin()->update_palettes( $config, 2 );

		return new WP_REST_Response( [ 'success' => 1 ], 200 );
	}

	/**
	 * Update auxiliary colors callback
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public static function update_auxiliary( $request ) {
		$id    = (int) $request->get_param( 'id' );
		$color = (string) $request->get_param( 'color' );

		thrive_palettes()->update_auxiliary_variable( $id, $color );

		return new WP_REST_Response( [ 'success' => 1 ], 200 );
	}
}
