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
 * Class Thrive_Frontend_REST
 */
class Thrive_Frontend_REST {

	public static $namespace = TTB_REST_NAMESPACE;
	public static $route     = '/frontend';

	const ALLOWED_USER_OPTIONS = [
		'sidebar_visibility',
	];

	public static function register_routes() {
		register_rest_route( static::$namespace, static::$route . '/user_options', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ __CLASS__, 'update_user_option' ],
				'permission_callback' => static function () {
					return is_user_logged_in();
				},
			],
		] );
	}

	/**
	 * Update user meta data
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function update_user_option( $request ) {
		$option  = $request->get_param( 'option' );
		$user_id = get_current_user_id();
		$updated = false;

		if ( ! empty( $user_id ) && in_array( $option, static::ALLOWED_USER_OPTIONS, true ) ) {

			$value       = $request->get_param( 'value' );
			$template_id = $request->get_param( 'template' );

			switch ( $option ) {
				case 'sidebar_visibility':
					$sidebar_visibility = get_user_option( $option, $user_id );

					$sidebar_visibility[ $template_id ] = $value;

					$updated = update_user_option( $user_id, $option, $sidebar_visibility );
					break;
				default:
					$updated = update_user_option( $user_id, $option, $value );
					break;
			}
		}

		return new WP_REST_Response( $updated );
	}
}
