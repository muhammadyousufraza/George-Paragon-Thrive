<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Dashboard\Metrics;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Rest_Controller extends WP_REST_Controller {
	const REST_NAMESPACE = 'td-metrics/v1';

	public function register_routes() {
		register_rest_route( static::REST_NAMESPACE, '/track_deactivate', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'track_deactivate' ),
				'permission_callback' => array( __CLASS__, 'admin_permissions_check' ),
				'args'                => array(
					'reason'         => Utils::get_rest_string_arg_data(),
					'reason_id'      => Utils::get_rest_string_arg_data(),
					'plugin_name'    => Utils::get_rest_string_arg_data(),
					'plugin_version' => [
						'type' => 'string',
					],
					'extra_message'  => [
						'type' => 'string',
					],
					'nonce'          => Utils::get_rest_string_arg_data(),
				),
			],
		] );

		register_rest_route( static::REST_NAMESPACE, '/settings/tracking_consent', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'tracking_consent' ),
				'permission_callback' => array( __CLASS__, 'admin_permissions_check' ),
				'args'                => array(
					'tracking_enabled' => [
						'type'     => 'boolean',
						'required' => true,
					],
				),
			],
		] );
	}

	/**
	 * Endpoint for tracking plugin deactivation
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public static function track_deactivate( WP_REST_Request $request ) {
		$nonce = $request->get_param( 'nonce' );
		if ( ! wp_verify_nonce( $nonce, Main::NONCE ) ) {
			return new WP_REST_Response( [ 'message' => 'Invalid nonce' ], 404 );
		}
		Deactivate::log_data( [
			'reason'         => $request->get_param( 'reason' ),
			'reason_id'      => $request->get_param( 'reason_id' ),
			'extra_message'  => $request->get_param( 'extra_message' ),
			'plugin_name'    => $request->get_param( 'plugin_name' ),
			'plugin_version' => $request->get_param( 'plugin_version' ),
		] );

		return new WP_REST_Response( [ 'success' => true ], 200 );
	}

	/**
	 * Check if current user has TD permissions
	 *
	 * @return bool
	 */
	public static function admin_permissions_check() {
		return current_user_can( TVE_DASH_CAPABILITY );
	}

	public static function tracking_consent( \WP_REST_Request $request ) {
		$consent = (int) $request->get_param( 'tracking_enabled' );

		Tracking::set_tracking_allowed( $consent );

		return new WP_REST_Response( true, 200 );
	}
}
