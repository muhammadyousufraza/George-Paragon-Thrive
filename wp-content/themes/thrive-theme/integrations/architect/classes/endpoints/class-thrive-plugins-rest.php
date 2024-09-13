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
 * Class Thrive_Plugins_Rest
 */
class Thrive_Plugins_Rest {

	public static $version = 1;
	public static $route = '/plugins';

	public static function register_routes() {
		register_rest_route( TTB_REST_NAMESPACE, static::$route . '/install', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ __CLASS__, 'install' ],
				'permission_callback' => [ __CLASS__, 'install_permission' ],
			],
		] );

		register_rest_route( TTB_REST_NAMESPACE, static::$route . '/activate', [
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ __CLASS__, 'activate' ],
				'permission_callback' => [ __CLASS__, 'activate_permission' ],
			],
		] );

		register_rest_route( TTB_REST_NAMESPACE, static::$route . '/settings', [
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => [ __CLASS__, 'update_settings' ],
				'permission_callback' => [ __CLASS__, 'update_settings_permission' ],
			],
		] );
	}

	/**
	 * Check if the user has permission to install a plugin
	 *
	 * @return bool|WP_Error
	 */
	public static function install_permission() {
		if ( ! current_user_can( 'install_plugins' ) ) {
			return new WP_Error(
				'invalid_permission',
				__( 'Sorry, you are not allowed to install plugins', 'thrive-theme' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return Thrive_Theme_Product::has_access();
	}

	/**
	 * Install plugin
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function install( $request ) {
		$slug = $request->get_param( 'slug' );

		try {
			if ( Thrive_Plugins_Manager::is_installed( $slug ) ) {
				$result = [
					'slug'      => $slug,
					'installed' => true,
				];
			} else {
				$result = Thrive_Plugins_Manager::install( $slug );
			}

			/**
			 * Also allows activating the plugin during the same request
			 */
			if ( $request->get_param( '_and_activate' ) ) {
				$response = static::activate( $request );
			} else {
				$response = new WP_REST_Response( $result, 200 );
			}
		} catch ( Exception $ex ) {
			$response = new WP_Error( 'plugin_install_error', $ex->getMessage() );
		}

		return $response;
	}

	/**
	 * Check to see if the user can activate plugins
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return bool|WP_Error
	 */
	public static function activate_permission( $request ) {
		$error = [];
		$file  = $request->get_param( 'file' );

		if ( ! current_user_can( 'activate_plugin', $file ) ) {
			$error = [
				'code'    => 'invalid_permission',
				'message' => __( 'Sorry, you are not allowed to activate the plugin', 'thrive-theme' ),
			];
		}

		if ( ! empty( $error ) && is_multisite() && ! is_network_admin() && is_network_only_plugin( $file ) ) {
			$error = [
				'code'    => 'invalid_permission',
				'message' => __( 'Sorry, you need to be super admin in order to make this operation', 'thrive-theme' ),
			];
		}

		if ( ! empty( $error ) ) {
			return new WP_Error( $error['code'], $error['message'], [ 'status' => rest_authorization_required_code() ] );
		}

		return Thrive_Theme_Product::has_access();
	}

	/**
	 * Activate plugin
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function activate( $request ) {
		$file       = $request->get_param( 'file' );
		$deactivate = $request->get_param( '_and_deactivate' );

		try {
			$result = Thrive_Plugins_Manager::activate( $file );
			if ( $deactivate ) {
				Thrive_Plugins_Manager::deactivate( $deactivate );
			}

			$response = new WP_REST_Response( $result, 200 );
		} catch ( Exception $ex ) {
			$response = new WP_Error( 'activate_plugin_error', $ex->getMessage() );
		}

		return $response;
	}

	/**
	 * Check to see if the user can update plugin settings
	 *
	 * @return bool|WP_Error
	 */
	public static function update_settings_permission() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'invalid_permission',
				__( 'Sorry, you are not allowed to update plugin options', 'thrive-theme' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return Thrive_Theme_Product::has_access();
	}

	/**
	 * Update plugin settings
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public static function update_settings( $request ) {
		$tag = $request->get_param( 'tag' );

		if ( empty( $tag ) ) {
			return new WP_Error(
				'invalid_parameters',
				__( 'Missing tag parameter', 'thrive-theme' ),
				array( 'status' => 400 )
			);
		}

		//Update settings for the plugin based on it's tag
		$instance = Thrive_Plugins_Manager::plugin_factory( $tag );
		if ( $instance && method_exists( $instance, 'update_settings' ) ) {
			$result = $instance->update_settings();
		}

		//Check the result and send the response
		if ( empty( $result ) ) {
			$response = new WP_Error(
				'plugin_cannot_update',
				__( 'There has been an error during the update process', 'thrive-theme' ),
				array( 'status' => 400 )
			);
		} else {
			$response = new WP_REST_Response(
				[
					'configured' => true,
					'message'    => __( 'Plugin Updated', 'thrive-theme' ),
				], 200 );
		}

		return $response;
	}
}
