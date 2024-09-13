<?php

/**
 * Class TVA_Logs_Controller
 */
class TVA_Logs_Controller extends TVA_REST_Controller {

	/**
	 * @var string
	 */
	public $base = 'logs';

	/**
	 * Register rest routes
	 */
	public function register_routes() {

		/**
		 * @deprecated
		 */
		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/get_logs/', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'get_logs' ),
				'permission_callback' => array( $this, 'get_logs_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'fetch_logs' ),
				'permission_callback' => array( $this, 'get_logs_permissions_check' ),
				'args'                => array(),
			),
		) );
	}

	/**
	 * Used for fetching the logs
	 *
	 * @param $request
	 *
	 * @return WP_REST_Response
	 */
	public function fetch_logs( $request ) {

		$filters = array(
			'offset' => (int) sanitize_text_field( $request->get_param( 'offset' ) ),
			'limit'  => (int) sanitize_text_field( $request->get_param( 'limit' ) ),
			's'      => sanitize_text_field( $request->get_param( 's' ) ),
			'types'  => $request->get_param( 'types' ),
		);

		return new WP_REST_Response( array(
			'items' => TVA_Logger::fetch_logs( $filters ),
			'total' => TVA_Logger::fetch_logs( $filters, true ),
		), 200 );
	}

	/**
	 * @param $request
	 *
	 * @return WP_Error|WP_REST_Response
	 * @deprecated
	 */
	public function get_logs( $request ) {
		/** @var WP_REST_Request $request */
		$settings = $request->get_param( 'settings' );
		$logs     = TVA_Logger::get_logs( $settings );

		if ( empty( $logs ) ) {
			$logs['all'] = true;
		}

		return new WP_REST_Response( $logs, 200 );
	}

	/**
	 * @param $request
	 *
	 * @return bool
	 */
	public function get_logs_permissions_check( $request ) {
		return TVA_Product::has_access();
	}
}
