<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_Api_MailerLiteV2_RestClient {

	public $http_client;

	public $api_key;

	public $base_url;

	/**
	 * @param string $base_url
	 * @param string $api_key
	 */
	public function __construct( $base_url, $api_key ) {
		$this->base_url = $base_url;
		$this->api_key  = $api_key;
	}

	/**
	 * Execute GET request
	 *
	 * @param       $endpoints
	 * @param array $query_string
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_MailerLite_MailerLiteSdkException
	 */
	public function get( $endpoints, $query_string = array() ) {
		return $this->send( 'GET', $endpoints . '?' . http_build_query( $query_string ) );
	}

	/**
	 * Execute POST request
	 *
	 * @param       $endpoints
	 * @param array $data
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_MailerLite_MailerLiteSdkException
	 */
	public function post( $endpoints, $data = array() ) {
		return $this->send( 'POST', $endpoints, $data );
	}

	/**
	 * Execute PUT request
	 *
	 * @param       $endpoints
	 * @param array $put_data
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_MailerLite_MailerLiteSdkException
	 */
	public function put( $endpoints, $put_data = array() ) {
		return $this->send( 'PUT', $endpoints, http_build_query( $put_data ) );
	}

	/**
	 * Execute DELETE request
	 *
	 * @param $endpoints
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_MailerLite_MailerLiteSdkException
	 */
	public function delete( $endpoints ) {
		return $this->send( 'DELETE', $endpoints );
	}

	/**
	 * Execute HTTP request
	 *
	 * @param       $method
	 * @param       $endpoints
	 * @param null  $body
	 * @param array $headers
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_MailerLite_MailerLiteSdkException
	 */
	protected function send( $method, $endpoints, $body = null, array $headers = array() ) {
		$headers      = array_merge( $headers, $this->get_default_headers() );
		$endpoint_url = $this->base_url . $endpoints;

		$fn = ( $method === 'GET' ) ? 'tve_dash_api_remote_get' : 'tve_dash_api_remote_post';

		$response = $fn( $endpoint_url, array(
			'body'      => $body,
			'timeout'   => 15,
			'headers'   => $headers,
			'sslverify' => false,
		) );

		return $this->handle_response( $response );
	}

	/**
	 * Handle HTTP response
	 *
	 * @param $response
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_MailerLite_MailerLiteSdkException
	 */
	protected function handle_response( $response ) {

		if ( $response instanceof WP_Error ) {
			throw new Thrive_Dash_Api_MailerLite_MailerLiteSdkException( 'Failed connecting: ' . $response->get_error_message() );
		}

		if ( isset( $response['response']['code'] ) ) {
			switch ( $response['response']['code'] ) {
				case 200:
					$response_obj = json_decode( $response['body'] );
					$result       = (array) $response_obj;

					return $result;
					break;
				case 400:

					throw new Thrive_Dash_Api_MailerLite_MailerLiteSdkException( 'Missing a required parameter or calling invalid method' );
					break;
				case 401:
					throw new Thrive_Dash_Api_MailerLite_MailerLiteSdkException( 'Invalid API key provided!' );
					break;
				case 404:
					throw new Thrive_Dash_Api_MailerLite_MailerLiteSdkException( 'Can\'t find requested items' );
					break;
			}
		}

		return $response['body'];
	}

	/**
	 * @return array
	 */
	protected function get_default_headers() {
		return array(
			'User-Agent'          => Thrive_Dash_Api_MailerLiteV2_ApiConstants::SDK_USER_AGENT . '/' . Thrive_Dash_Api_MailerLiteV2_ApiConstants::SDK_VERSION,
			'X-MailerLite-ApiKey' => $this->api_key,
			'Authorization'       => 'Bearer ' . $this->api_key,
		);
	}
}