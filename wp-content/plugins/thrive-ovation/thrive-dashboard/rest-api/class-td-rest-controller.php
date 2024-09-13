<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TD_REST_Controller
 *
 * - Base REST controller for TD
 */
class TD_REST_Controller extends WP_REST_Controller {

	/**
	 * The base of this controller's route.
	 *
	 * @since 4.7.0
	 * @var string
	 */
	protected $rest_base;
	protected $namespace    = 'td/v1';
	protected $webhook_base = '/webhook/trigger';

	public function __construct() {
	}

	public function get_namespace() {

		return $this->namespace;
	}

	public function get_webhook_base() {

		return $this->webhook_base;
	}

	/**
	 * Registers routes for basic controller
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			$this->rest_base . '/authenticate',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'authenticate' ),
				'permission_callback' => array( $this, 'permission_callback' ),
				'args'                => $this->route_args(),
			)
		);

		register_rest_route( $this->namespace, $this->webhook_base . '/(?P<api>\S+)/(?P<id>\d+)/(?P<code>\S+)', array(
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'webhook_trigger' ),
				'permission_callback' => '__return_true',
			),
		) );

		register_rest_route( $this->namespace, $this->rest_base . '/license_warning', array(
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'license_warning' ),
				'permission_callback' => array( $this, 'permission_callback_license_warning' ),
			),
		) );
	}

	/**
	 * Args required by routes that need permission
	 * @return array[]
	 */
	public function route_args() {
		return array(
			'api_key' => array(
				'type'              => 'string',
				'required'          => true,
				'validate_callback' => static function ( $param ) {
					return ! empty( $param );
				},
			),
		);
	}

	public static function license_warning( $request ) {
		$product = $request->get_param( 'product' );

		$transient = 'tve_license_warning_lightbox_' . $product;

		set_transient( $transient, true, DAY_IN_SECONDS );

		return $product;
	}

	/**
	 * callback function
	 *
	 * @param WP_REST_Response
	 */
	public static function webhook_trigger( $request ) {
		$id   = $request->get_param( 'id' );
		$api  = $request->get_param( 'api' );
		$code = $request->get_param( 'code' );

		$data = array();
		if ( $api === 'general' ) {
			$data = tve_dash_get_general_webhook_data( $request );
		} else {
			$api_instance = Thrive_Dash_List_Manager::connection_instance( $api );
			if ( $api_instance ) {
				$data = $api_instance->get_webhook_data( $request );
			}
		}

		if ( empty( $data['email'] ) ) {
			global $wpdb;

			$log_data = array(
				'date'          => gmdate( 'Y-m-d H:i:s' ),
				'error_message' => 'No email inside webhook payload',
				'api_data'      => serialize( tve_sanitize_data_recursive( $request ) ),
				'connection'    => $api,
				'list_id'       => 'asset',
			);

			$wpdb->insert( $wpdb->prefix . 'tcb_api_error_log', $log_data );
		}

		return apply_filters( 'tve_dash_webhook_trigger', $id, $code, $data );
	}

	/**
	 * @return mixed|WP_REST_Response
	 */
	public function authenticate() {

		return rest_ensure_response(
			array(
				'connected' => true,
			)
		);
	}

	/**
	 * Verifies each call to TD REST API
	 *
	 * @param $request
	 *
	 * @return bool|WP_Error
	 */
	public function permission_callback( $request ) {
		return $this->validate_api_key( $request->get_param( 'api_key' ) );
	}

	/**
	 * Verifies each call to TD REST API
	 *
	 * @param $request
	 *
	 * @return bool|WP_Error
	 */
	public function permission_callback_license_warning( $request ) {
		return $this->validate_license_warning( $request->get_param( 'product' ) );
	}

	/**
	 * Checks if the api_key sent as parameter is the same with the one generated in DB
	 *
	 * @param $api_key
	 *
	 * @return bool|WP_Error
	 */
	protected function validate_api_key( $api_key = '' ) {

		$generated_api_key = get_option( 'td_api_key', null );

		/* make sure we don't send an empty api_key */
		if ( ! empty( $api_key ) && $generated_api_key === $api_key ) {
			$result = true;
		} else {
			$result = new WP_Error(
				'wrong_api_key_provided',
				__( 'Provided API Key is wrong', 'thrive-dash' ),
				array(
					'api_key' => $api_key,
				)
			);
		}

		return $result;
	}

	/**
	 * Checks if the product is sent as parameter
	 *
	 * @param $api_key
	 *
	 * @return bool|WP_Error
	 */
	protected function validate_license_warning( $product = '' ) {

		$result   = true;
		$products = [
			'tcb',
			'tl',
			'tu',
			'tvo',
			'tqb',
			'tcm',
			'tva',
			'tab',
			'ttb'
		];
		if ( empty( $product ) || ! in_array( $product, $products, true ) ) {

			$result = new WP_Error(
				'no_product_provided',
				__( 'No product identifier provided', 'thrive-dash' ),
				array(
					'product' => $product,
				)
			);
		}

		return $result;
	}
}
