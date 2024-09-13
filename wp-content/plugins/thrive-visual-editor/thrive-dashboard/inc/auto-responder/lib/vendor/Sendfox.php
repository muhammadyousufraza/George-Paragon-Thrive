<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_Api_Sendfox {

	/**
	 * Api url
	 */
	const API_URL = 'https://api.sendfox.com/';

	/**
	 * @var string
	 */
	protected $_api_key;

	/**
	 * Thrive_Dash_Api_Sendfox constructor.
	 *
	 * @param $api_key
	 *
	 * @throws Exception
	 */
	public function __construct( $api_key ) {

		if ( empty( $api_key ) ) {
			throw new Exception( 'Api key is required!' );
		}

		$this->_api_key = $api_key;
	}

	/**
	 * Makes a request to API
	 *
	 * @param string $route
	 * @param string $method
	 * @param array  $data
	 *
	 * @return array
	 * @throws Exception
	 */
	protected function _request( $route, $method = 'get', $data = array() ) {

		$method = strtoupper( $method );
		$body   = $data;
		$route  = '/' . trim( $route, '/' );
		$url    = untrailingslashit( self::API_URL ) . untrailingslashit( $route );

		switch ( $method ) {
			case 'GET':
				$fn   = 'tve_dash_api_remote_get';
				$url  = add_query_arg( $data, $url );
				$body = '';
				break;
			default:
				$fn = 'tve_dash_api_remote_post';
				break;
		}

		$args = array(
			'body'      => $body,
			'timeout'   => 15,
			'headers'   => array(
				'Authorization' => 'Bearer ' . $this->_api_key,
			),
			'method'    => $method,
			'sslverify' => false,
		);

		$response = $fn( $url, $args );

		return $this->handle_response( $response );
	}

	/**
	 * Processes the response got from API
	 *
	 * @param WP_Error|array $response
	 *
	 * @return array
	 * @throws Exception
	 */
	protected function handle_response( $response ) {

		if ( $response instanceof WP_Error ) {
			throw new Exception( 'Failed connecting: ' . $response->get_error_message() );
		}

		if ( isset( $response['response']['code'] ) ) {
			switch ( $response['response']['code'] ) {
				case 200:
					$result = json_decode( $response['body'], true );

					return $result;
					break;
				case 400:
					throw new Exception( 'Missing a required parameter or calling invalid method' );
					break;
				case 401:
					throw new Exception( 'Invalid API key provided!' );
					break;
				case 404:
					throw new Exception( "Can't find requested items" );
					break;
			}
		}

		return json_decode( $response['body'], true );
	}

	/**
	 * Get lists
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getLists() {

		return $this->_request( '/lists' );
	}
	/**
	 * Get lists from a certain page
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getListsOnPage( $page ) {

		return $this->_request( '/lists?page=' . $page );
	}

	/**
	 * @param string $list_id
	 * @param array  $args
	 *
	 * @return array
	 * @throws Exception
	 */
	public function add_subscriber( $list_id, $args ) {

		return $this->_request( '/contacts', 'post', array_merge( array( 'lists' => array( $list_id ) ), $args ) );
	}
}
