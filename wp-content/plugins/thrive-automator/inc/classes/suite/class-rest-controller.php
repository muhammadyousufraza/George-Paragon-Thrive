<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace Thrive\Automator\Suite;

use Thrive\Automator\Internal_Rest_Controller;
use Thrive\Automator\Utils;
use WP_REST_Response;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Rest_Controller extends Internal_Rest_Controller {
	/**
	 * Registers routes for basic controller
	 */
	public function register_routes() {


		register_rest_route( static::NAMESPACE, '/upload_plugin', [
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ __CLASS__, 'upload_plugin' ],
				'permission_callback' => [ __CLASS__, 'admin_permissions_check' ],
			],
		] );
		register_rest_route( static::NAMESPACE, '/activate_plugin', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ __CLASS__, 'activate_plugin' ],
				'permission_callback' => [ __CLASS__, 'admin_permissions_check' ],
			],
		] );
		register_rest_route( static::NAMESPACE, '/verify_plugin', [
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ __CLASS__, 'verify_plugin' ],
				'permission_callback' => [ __CLASS__, 'admin_permissions_check' ],
			],
		] );
		register_rest_route( static::NAMESPACE, '/register', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ __CLASS__, 'register' ],
				'permission_callback' => [ __CLASS__, 'admin_permissions_check' ],
				'args'                => [
					'email'    => Utils::get_rest_string_arg_data(),
					'password' => Utils::get_rest_string_arg_data(),
					'name'     => Utils::get_rest_string_arg_data(),
				],
			],
		] );
	}

	/**
	 * Handle user registration
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public static function register( \WP_REST_Request $request ) {
		$email = $request->get_param( 'email' );

		if ( ! is_email( $email ) ) {
			return new WP_REST_Response( [
				'error' => __( 'Invalid email', 'thrive-automator' ),
			], 400 );
		}

		$password = $request->get_param( 'password' );
		$name     = $request->get_param( 'name' );

		list( $first_name, $last_name ) = Utils::get_name_parts( $name );


		$created = TTW::register_user( $email, $password, $first_name, $last_name );
		if ( is_wp_error( $created ) ) {
			return new WP_REST_Response( [
				'error' => $created->get_error_message(),
			], 403 );
		}

		return new WP_REST_Response( [
			'success' => true,
		] );
	}

	/**
	 * Handle verify plugin request
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public static function verify_plugin( \WP_REST_Request $request ) {
		$nonce = $request->get_param( 'zip_upload_nonce' );

		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'zip_upload_nonce' ) ) {
			return new WP_REST_Response( [
				'error' => __( 'Invalid nonce', 'thrive-automator' ),
			], 403 );
		}

		$data     = $request->get_file_params();
		$is_valid = Plugin_Handler::verify_archive( $data['file'] );
		if ( is_wp_error( $is_valid ) ) {
			return new WP_REST_Response( [
				'error' => $is_valid->get_error_message(),
			], 403 );
		}

		return new WP_REST_Response( [
			'success' => true,
		] );
	}

	/**
	 * Handle activate TPM request
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public static function activate_plugin( \WP_REST_Request $request ): WP_REST_Response {
		$result = Plugin_Handler::activate_plugin();
		$nonce  = $request->get_param( 'zip_upload_nonce' );

		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'zip_upload_nonce' ) ) {
			return new WP_REST_Response( [
				'error' => __( 'Invalid nonce', 'thrive-automator' ),
			], 403 );
		}

		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response( [
				'error' => $result->get_error_message(),
			], 403 );
		}

		return new WP_REST_Response( [
			'success' => $result,
			'ttw'     => TTW::localize(),
		], $result ? 200 : 404 );
	}


	/**
	 * Handle upload TPM request
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public static function upload_plugin( \WP_REST_Request $request ): WP_REST_Response {
		$nonce = $request->get_param( 'zip_upload_nonce' );

		if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, 'zip_upload_nonce' ) ) {
			$file      = $request->get_file_params();
			$installer = new Plugin_Handler();

			$result = $installer->upload_file( $file );
			if ( is_wp_error( $result ) ) {
				return new WP_REST_Response( [
					'error' => $result->get_error_message(),
				], 403 );
			}

			return new WP_REST_Response( [ 'success' => true ], 200 );
		}

		return new WP_REST_Response( [], 404 );
	}
}
