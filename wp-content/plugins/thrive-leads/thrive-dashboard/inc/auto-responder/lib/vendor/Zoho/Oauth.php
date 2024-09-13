<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_Api_Zoho_Oauth {

	const OAUTH_PATH = '/oauth/v2/token/';

	private $_data;

	private $_response;

	private $_is_access_token_new = false;

	private $_tokens = array(
		'access_token'        => '',
		'refresh_token'       => '', // never expires
		'acctk_validity_time' => '', // refers to access_token
	);

	/**
	 * Thrive_Dash_Api_Zoho_Oauth constructor.
	 *
	 * @param array $data
	 *
	 * @throws Exception
	 */
	public function __construct( $data ) {

		if ( empty( $data ) ) {
			throw new Exception( __( 'No data provided', 'thrive-dash' ) );
		}

		$this->_data = $data;

		$this->_setTokens( $this->_data );
	}

	/**
	 * @param array $data
	 */
	private function _setTokens( $data = array() ) {
		foreach ( $this->_tokens as $key => $token ) {
			if ( ! empty( $data[ $key ] ) ) {
				$this->_tokens[ $key ] = $data[ $key ];
			}
		}
	}

	/**
	 * Set validity time for access token
	 */
	private function _setValidityTime() {

		if ( empty( $this->_tokens['access_token'] ) ) {
			return;
		}

		/**
		 * Access token is valid 1h but I think a 10s offset would be good, to avoid it's usage just before would expire
		 */
		$this->_tokens['acctk_validity_time'] = time() + 3590;
	}

	/**
	 * @return array
	 */
	public function getTokens() {

		return $this->_tokens;
	}

	/**
	 * Check if access token is valid
	 *
	 * @return bool
	 */
	public function isAccessTokenValid() {

		return ! empty( $this->_tokens['acctk_validity_time'] ) && time() < (int) $this->_tokens['acctk_validity_time'];
	}

	/**
	 * whether or not access token is new
	 *
	 * @return bool
	 */
	public function isAccessTokenNew() {

		return $this->_is_access_token_new;
	}

	/**
	 * @param $data
	 *
	 * @return array
	 * @throws Exception
	 */
	private function _request( $data ) {

		$url  = untrailingslashit( $this->_data['account_url'] ) . untrailingslashit( self::OAUTH_PATH );
		$args = array( 'body' => $data );

		$response = tve_dash_api_remote_post( $url, $args );

		$this->_response = $this->handleResponse( $response );

		return $this->_response;
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
	 * Generate access token and refresh token
	 *
	 * @throws Exception
	 */
	public function generateTokens() {

		$args = array(
			'grant_type'    => 'authorization_code',
			'client_id'     => isset( $this->_data['client_id'] ) ? $this->_data['client_id'] : '',
			'client_secret' => isset( $this->_data['client_secret'] ) ? $this->_data['client_secret'] : '',
			'code'          => isset( $this->_data['access_code'] ) ? $this->_data['access_code'] : '',
			'redirect_uri'  => '',
		);

		$this->_fetchTokens( $args );
	}

	/**
	 * Refresh access token
	 *
	 * @throws Exception
	 */
	public function refreshToken() {

		$args = array(
			'grant_type'    => 'refresh_token',
			'client_id'     => isset( $this->_data['client_id'] ) ? $this->_data['client_id'] : '',
			'client_secret' => isset( $this->_data['client_secret'] ) ? $this->_data['client_secret'] : '',
			'refresh_token' => isset( $this->_data['refresh_token'] ) ? $this->_data['refresh_token'] : '',
		);

		$this->_fetchTokens( $args );
	}

	/**
	 * @param $args
	 *
	 * @throws Exception
	 */
	private function _fetchTokens( $args ) {

		$this->_request( $args );
		$this->_setTokens( $this->_response );
		$this->_setValidityTime();
		$this->_is_access_token_new = true;
	}
}
