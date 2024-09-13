<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_Api_Zapier {

	/**
	 * @var string
	 */
	protected $_api_key;

	/**
	 * @var string
	 */
	protected $_blog_url;

	/**
	 * @var string
	 */
	protected $_rest_prefix;

	/**
	 * Thrive_Dash_Api_Zapier constructor.
	 *
	 * @param string $_api_key
	 * @param string $_blog_url
	 */
	public function __construct( $_api_key, $_blog_url ) {

		$this->_api_key  = $_api_key;
		$this->_blog_url = $_blog_url;

		$rest_controller    = new TD_REST_Controller();
		$this->_rest_prefix = trailingslashit( $rest_controller->get_namespace() );
	}

	/**
	 * @return bool|WP_Error
	 */
	public function authenticate() {

		$response = $this->_request( 'authenticate' );

		$body    = wp_remote_retrieve_body( $response );
		$params  = json_decode( $body );
		$success = new WP_Error( 'authentication_failed', __( 'Authentication failed', 'thrive-dash' ) );

		if ( $params instanceof stdClass ) {
			$success = $params->connected;
		}

		return $success;
	}

	/**
	 * Does a wp_remote_post()
	 *
	 * @param string $route
	 * @param array  $args
	 *
	 * @return array|WP_Error
	 * @see tve_dash_api_remote_post()
	 *
	 */
	protected function _request( $route, $args = array() ) {

		$url = rest_url() . $this->_rest_prefix . $route;

		$args = array_merge(
			array(
				'body' => array(
					'api_key' => $this->_api_key,
				),
			),
			$args
		);

		return tve_dash_api_remote_post( $url, $args );
	}

	/**
	 * Call to Zapier hook with different URLs
	 *
	 * @param       $url
	 * @param array $args
	 *
	 * @return array|mixed|object
	 * @throws Thrive_Dash_Api_Zapier_Exception
	 */
	protected function _zap_request( $url, $args = array() ) {

		$arguments              = array();
		$arguments['body']      = json_encode( $args );
		$arguments['timeout']   = 15;
		$arguments['sslverify'] = false;

		$response = tve_dash_api_remote_post( $url, $arguments );

		return $this->_handle_response( $response );
	}

	/**
	 * Call to the zapier request method with error handling
	 *
	 * @param $url
	 * @param $params
	 *
	 * @return bool|string
	 */
	public function trigger_subscribe( $url, $params ) {

		$return = true;
		try {
			$this->_zap_request( $url, $params );

		} catch ( Thrive_Dash_Api_Zapier_Exception $e ) {
			$return = $e->getMessage();
		}

		return $return;
	}

	/**
	 * @param $response
	 *
	 * @return array|mixed|object
	 * @throws Thrive_Dash_Api_Zapier_Exception
	 */
	protected function _handle_response( $response ) {

		if ( $response instanceof WP_Error ) {
			throw new Thrive_Dash_Api_Zapier_Exception( __( 'Failed connecting: ', 'thrive' ) . $response->get_error_message() );
		}

		if ( isset( $response['response'] ) && isset( $response['response']['code'] ) ) {
			switch ( $response['response']['code'] ) {
				case 200:
					return json_decode( $response['body'], true );
					break;
				case 400:
					throw new Thrive_Dash_Api_Zapier_Exception( 'Missing a required parameter or calling invalid method' );
					break;
				case 401:
					throw new Thrive_Dash_Api_Zapier_Exception( 'Unauthorized' );
					break;
				case 404:
					throw new Thrive_Dash_Api_Zapier_Exception( 'Can\'t find requested items' );
					break;
				case 410:
					throw new Thrive_Dash_Api_Zapier_Exception( 'Unsubscribed hook, please activate Zap' );
					break;
			}
		}

		return json_decode( $response['body'], true );
	}
}

