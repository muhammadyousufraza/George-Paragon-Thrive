<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

use Thrive\Theme\AMP\Settings as AMP_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Amp_Rest
 */
class Thrive_Amp_Rest {
	public static $namespace = TTB_REST_NAMESPACE;

	public static $route = '/amp';


	public static function register_routes() {
		register_rest_route( static::$namespace, static::$route, [
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ __CLASS__, 'update' ],
				'permission_callback' => [ __CLASS__, 'route_permission' ],
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
	 * Update amp settings
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function update( $request ) {
		$option    = get_option( AMP_Settings::THRIVE_AMP_OPTION );
		$new_value = maybe_serialize( $request->get_params() );
		$response  = true;

		if ( $option !== $new_value ) {
			$response = update_option( AMP_Settings::THRIVE_AMP_OPTION, $new_value );
			$data     = maybe_unserialize( get_option( AMP_Settings::THRIVE_AMP_OPTION ) );
		}

		return $response ? new WP_REST_Response( $data, 200 ) : new WP_Error( 'cant-update', __( "Couldn't update the settings.", 'thrive-theme' ), [ 'status' => 500 ] );
	}
}
