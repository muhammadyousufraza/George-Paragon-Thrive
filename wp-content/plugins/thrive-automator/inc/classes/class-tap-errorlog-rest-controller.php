<?php

namespace Thrive\Automator;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Response;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Errorlog_Rest_Controller extends WP_REST_Controller {

	const NAMESPACE = 'tap/v1';

	const NO_FIELD = 'No field was found!';

	/**
	 * Registers routes for basic controller
	 */
	public function register_routes() {
		/**
		 * Error log for specific automation
		 */
		register_rest_route( static::NAMESPACE, '/error_log/(?P<id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_error_log' ),
				'permission_callback' => array( __CLASS__, 'admin_permissions_check' ),
				'args'                => array(
					'id' => Utils::get_rest_integer_arg_data(),
				),
			),
			/**
			 * Delete specific error log
			 */
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( __CLASS__, 'delete_error_log' ),
				'permission_callback' => array( __CLASS__, 'admin_permissions_check' ),
				'args'                => array(
					'id' => Utils::get_rest_integer_arg_data(),
				),
			),
		) );

		/**
		 * Error log for all automation
		 */
		register_rest_route( static::NAMESPACE, '/error_log', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_error_log' ),
				'permission_callback' => array( __CLASS__, 'admin_permissions_check' ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( __CLASS__, 'clear_log' ),
				'permission_callback' => array( __CLASS__, 'admin_permissions_check' ),
			),
		) );


		/**
		 * Error log for all automation
		 */
		register_rest_route( static::NAMESPACE, '/error_log/settings', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( __CLASS__, 'modify_error_log_settings' ),
				'permission_callback' => array( __CLASS__, 'admin_permissions_check' ),
			),
		) );
	}

	/**
	 * Handle error log settings
	 */
	public static function modify_error_log_settings( $request ): WP_REST_Response {
		$settings = $request->get_param( 'settings' );
		Error_Log_Handler::save_log_settings( $settings );

		return new WP_REST_Response( Error_Log_Handler::get_error_log( 0, 'all', 0 ), 200 );
	}

	public static function clear_log( $request ): WP_REST_Response {

		Error_Log_Handler::clear_log();

		return new WP_REST_Response( true, 200 );
	}

	/**
	 * Get error log for specific automation
	 */
	public static function delete_error_log( $request ) {
		$id = $request->get_param( 'id' );


		if ( ! empty( $id ) ) {
			return new WP_REST_Response( Error_Log_Handler::delete_error_log( $id ), 200 );
		}

		return new WP_Error( 'no-results', static::NO_FIELD );
	}

	/**
	 * Get error log for specific automation
	 */
	public static function get_error_log( $request ): WP_REST_Response {
		$id       = $request->get_param( 'id' );
		$interval = $request->get_param( 'interval' ) ?: '';
		$page     = $request->get_param( 'page' ) ?: 0;

		return new WP_REST_Response( Error_Log_Handler::get_error_log( $id, $interval, $page ), 200 );
	}

	/**
	 * Setup basic permission callback
	 */
	public static function admin_permissions_check(): bool {
		return current_user_can( Admin::get_capability() );
	}
}
