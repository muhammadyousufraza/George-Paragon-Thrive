<?php

namespace Thrive\Automator;

use Thrive\Automator\Items\Action;
use Thrive\Automator\Items\Automation_Data;
use WP_REST_Controller;
use WP_REST_Response;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Integrations_Rest_Controller extends WP_REST_Controller {

	const NAMESPACE = 'tap/v1';

	const NO_ACTION = 'No action was found!';

	/**
	 * Registers routes for basic controller
	 */
	public function register_routes() {
		/**
		 * Send test request for webhook action
		 */
		register_rest_route( static::NAMESPACE, '/automator/webhook/(?P<webhook_id>[\S]+)', [
			[
				'methods'             => WP_REST_Server::ALLMETHODS,
				'callback'            => array( __CLASS__, 'trigger_webhook' ),
				'permission_callback' => '__return_true',
			],
		] );

		register_rest_route( static::NAMESPACE, '/webhook_listener', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'webhook_listener' ),
				'permission_callback' => array( __CLASS__, 'admin_permissions_check' ),
			],
		] );

		/**
		 * Send test request for webhook action
		 */
		register_rest_route( static::NAMESPACE, '/actions/webhook', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'actions_webhook' ),
				'permission_callback' => array( __CLASS__, 'admin_permissions_check' ),
			],
		] );

		register_rest_route( static::NAMESPACE, '/track_deactivate', [
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'track_deactivate' ),
				'permission_callback' => array( __CLASS__, 'admin_permissions_check' ),
				'args'                => array(
					'reason'        => Utils::get_rest_string_arg_data(),
					'reason_id'     => Utils::get_rest_string_arg_data(),
					'extra_message' => [
						'type' => 'string',
					],
					'nonce'         => Utils::get_rest_string_arg_data(),
				),
			],
		] );

		register_rest_route( static::NAMESPACE, '/settings/tracking_consent', [
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

		register_rest_route( static::NAMESPACE, '/limitations/(?P<id>[\d]+)', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_limitations' ),
				'permission_callback' => array( __CLASS__, 'admin_permissions_check' ),
				'args'                => array(
					'id' => Utils::get_rest_integer_arg_data(),
				),
			],
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( __CLASS__, 'delete_limitations' ),
				'permission_callback' => array( __CLASS__, 'admin_permissions_check' ),
				'args'                => array(
					'id' => Utils::get_rest_integer_arg_data(),
				),
			],
		] );
	}

	/**
	 * Handle plugin deactivation reason requests
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public static function track_deactivate( \WP_REST_Request $request ): WP_REST_Response {
		$nonce = $request->get_param( 'nonce' );

		if ( ! wp_verify_nonce( $nonce, 'tap_deactivate_nonce' ) ) {
			return new WP_REST_Response( [ 'message' => 'Invalid nonce' ], 404 );
		}

		Deactivate::log_data( [
			'reason'        => sanitize_text_field( $request->get_param( 'reason' ) ),
			'reason_id'     => sanitize_text_field( $request->get_param( 'reason_id' ) ),
			'extra_message' => sanitize_textarea_field( $request->get_param( 'extra_message' ) ),
		] );

		return new WP_REST_Response( [ 'success' => true ], 200 );
	}

	/**
	 * Handle editor webhook fields listener
	 */
	public static function webhook_listener( $request ): WP_REST_Response {
		$hash = $request->get_param( 'webhook_id' );

		if ( ! empty( $hash ) ) {
			$listener = get_option( TAP_TEMP_WEBHOOK_DATA );

			if ( empty( $listener ) || ( ! empty( $listener['is_listener'] ) && $listener['webhook_id'] !== $hash ) ) {
				update_option( TAP_TEMP_WEBHOOK_DATA, [ 'webhook_id' => $hash, 'is_listener' => true ] );
			} elseif ( empty( $listener['is_listener'] ) ) {
				delete_option( TAP_TEMP_WEBHOOK_DATA );

				return new WP_REST_Response( json_decode( $listener ), 200 );
			}
		}

		return new WP_REST_Response( [ 'is_listener' => true ], 200 );
	}


	/**
	 * Handle outside webhook triggers
	 */
	public static function trigger_webhook( $request ): WP_REST_Response {
		$hash = $request->get_param( 'webhook_id' );
		$data = $request->get_params();
		if ( ! empty( $hash ) ) {
			$listener = get_option( TAP_TEMP_WEBHOOK_DATA );
			if ( ! empty( $listener['webhook_id'] ) && $listener['webhook_id'] === $hash ) {
				unset( $data['webhook_id'] );
				update_option( TAP_TEMP_WEBHOOK_DATA, json_encode( Utils::process_webhook_structure( $data ) ) );
			} else {
				$headers = Utils::get_automator_webhook_header_fields( $hash );
				foreach ( $headers as $key => $value ) {
					if ( $request->get_header( $key ) !== $value ) {
						return new WP_REST_Response( 'Forbidden', 403 );
					}
				}
				do_action( Utils::create_dynamic_trigger( Items\Wordpress_Webhook_Receive::get_wp_hook(), $hash ), $data );
			}

		}

		return new WP_REST_Response( true, 200 );
	}

	/**
	 * Simulate a running automation
	 */
	public static function actions_webhook( $request ) {
		$data      = $request->get_json_params();
		$action_id = $data['action_id'];
		$classes   = Action::get();
		if ( empty( $action_id ) || empty( $classes[ $action_id ] ) ) {
			return new WP_Error( 'no-results', static::NO_ACTION );
		}

		$class = new $classes[ $action_id ]( [ 'extra_data' => $data['action_data'] ] );
		global $automation_data;
		$automation_data = new Automation_Data( $data['automation_data'] );
		$class->replace_shortcodes( $data['action_data'] );
		$class->prepare_data( $data['action_data'] );

		return $class->do_action();
	}

	/**
	 * Setup basic permission callback
	 */
	public static function admin_permissions_check(): bool {
		return current_user_can( Admin::get_capability() );
	}

	/**
	 * Get all limitation for the current automation
	 *
	 * @param $request
	 *
	 * @return WP_REST_Response
	 */
	public static function get_limitations( $request ): WP_REST_Response {
		$id = $request->get_param( 'id' );

		$limitations = tap_limitations( $id )->get_trigger_runs();

		return new WP_REST_Response( $limitations, 200 );
	}

	/**
	 * Delete a limitation of a specific trigger for the current automation
	 *
	 * @param $request
	 *
	 * @return WP_REST_Response
	 */
	public static function delete_limitations( $request ): WP_REST_Response {
		$id         = $request->get_param( 'id' );
		$trigger_id = $request->get_param( 'trigger_id' );
		$additional = $request->get_param( 'additional' );

		tap_limitations( $id )->delete_automation_entries( $trigger_id, $additional );

		$limitations = tap_limitations( $id )->get_trigger_runs();

		return new WP_REST_Response( $limitations, 200 );
	}

	public static function tracking_consent( \WP_REST_Request $request ): WP_REST_Response {
		$consent = (int) $request->get_param( 'tracking_enabled' );

		Tracking::set_tracking_allowed( $consent );

		return new WP_REST_Response( true, 200 );
	}
}
