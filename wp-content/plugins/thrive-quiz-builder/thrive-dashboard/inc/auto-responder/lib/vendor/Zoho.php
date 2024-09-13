<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_Api_Zoho {

	/**
	 * Api url
	 */
	const API_PATH = '/api/v1.1';

	protected $_data;

	private $_oauth;

	/**
	 * Thrive_Dash_Api_Zoho constructor.
	 *
	 * @param array $data
	 *
	 * @throws Exception
	 */
	public function __construct( $data ) {

		if ( empty( $data ) ) {
			throw new Exception( 'No data provided' );
		}

		$this->_data = $data;
	}

	/**
	 * @return Thrive_Dash_Api_Zoho_Oauth
	 * @throws Exception
	 */
	public function getOauth() {

		if ( true !== $this->_oauth instanceof Thrive_Dash_Api_Zoho_Oauth ) {
			$this->_oauth = new Thrive_Dash_Api_Zoho_Oauth( $this->_data );
		}

		return $this->_oauth;
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

		$url    = str_replace( 'accounts', 'campaigns', $this->_data['account_url'] );
		$url    = untrailingslashit( $url ) . untrailingslashit( self::API_PATH );
		$method = strtoupper( $method );
		$route  = '/' . trim( $route, '/' );
		$tokens = $this->getOauth()->getTokens();

		if ( ! empty( $data['resfmt'] ) ) {
			$url .= '/' . $data['resfmt'];
			unset( $data['resfmt'] );
		}

		$url .= untrailingslashit( $route );

		switch ( $method ) {
			case 'GET':
				$data['resfmt'] = 'JSON';

				$fn   = 'tve_dash_api_remote_get';
				$url  = add_query_arg( $data, $url );
				$body = '';
				break;
			default:
				$body = http_build_query( $data );
				$fn   = 'tve_dash_api_remote_post';
				break;
		}

		/**
		 * Generate a new access token if needed
		 */
		if ( ! $this->getOauth()->isAccessTokenValid() ) {
			! empty( $tokens['access_token'] )
				? $this->getOauth()->refreshToken()
				: $this->getOauth()->generateTokens();

			$tokens = $this->getOauth()->getTokens();
		}

		$args = array(
			'body'      => $body,
			'timeout'   => 15,
			'headers'   => array(
				'Authorization' => 'Zoho-oauthtoken ' . $tokens['access_token'],
				'Content-Type'  => 'application/x-www-form-urlencoded',
			),
			'method'    => $method,
			'sslverify' => false,
		);

		$response = $fn( $url, $args );

		return $this->handleResponse( $response );
	}

	/**
	 * Processes the response got from API
	 *
	 * @param WP_Error|array $response
	 *
	 * @return array
	 * @throws Exception
	 */
	protected function handleResponse( $response ) {

		if ( $response instanceof WP_Error ) {
			throw new Exception( sprintf( __( 'Failed connecting: %s', 'thrive-dash' ), $response->get_error_message() ) );
		}

		if ( isset( $response['response']['code'] ) ) {
			switch ( $response['response']['code'] ) {
				case 200:
					$result = json_decode( $response['body'], true );

					return $result;
					break;
				case 400:
					throw new Exception( __( 'Missing a required parameter or calling invalid method', 'thrive-dash' ) );
					break;
				case 401:
					throw new Exception( __( 'Invalid API key provided!', 'thrive-dash' ) );
					break;
				case 404:
					throw new Exception( __( "Can't find requested items", 'thrive-dash' ) );
					break;
			}
		}

		return json_decode( $response['body'], true );
	}

	/**
	 * Get Lists
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getLists() {

		return $this->_request( '/getmailinglists' );
	}

	/**
	 * @param $args
	 *
	 * @return array
	 * @throws Exception
	 */
	public function add_subscriber( $args ) {

		$args['resfmt'] = 'json';

		return $this->_request( '/listsubscribe', 'post', $args );
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	public function getCustomFields() {

		return $this->_request( '/contact/allfields', 'get', array( 'type' => 'json' ) );
	}
}
