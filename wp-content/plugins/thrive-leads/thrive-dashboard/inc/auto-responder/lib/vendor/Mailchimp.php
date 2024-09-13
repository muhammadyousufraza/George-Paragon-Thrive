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
 * Class Thrive_Dash_Api_Mailchimp
 */
class Thrive_Dash_Api_Mailchimp {

	/**
	 * Endpoint for Mailchimp API v3
	 *
	 * @var string
	 */
	private $endpoint = 'https://us1.api.mailchimp.com/3.0/';

	/**
	 * @var string
	 */
	private $apikey;

	/**
	 * @var array
	 */
	private $allowedMethods = array( 'get', 'head', 'put', 'post', 'patch', 'delete' );

	/**
	 * @var array
	 */
	public $options = array();

	/**
	 * Thrive_Dash_Api_Mailchimp constructor.
	 *
	 * @param string $apikey
	 * @param array $clientOptions
	 *
	 * @throws Thrive_Dash_Api_Mailchimp_Exception
	 */
	public function __construct( $apikey = '', $clientOptions = array() ) {
		$this->apikey = $apikey;

		$this->detectEndpoint( $this->apikey );

		$this->options['headers'] = array(
			'Authorization' => 'apikey ' . $this->apikey,
		);
	}

	/**
	 * @param        $resource
	 * @param array $arguments
	 * @param string $method
	 * @param bool $no_body
	 *
	 * @return mixed
	 * @throws Thrive_Dash_Api_Mailchimp_Exception
	 */
	public function request( $resource, $arguments = array(), $method = 'GET', $no_body = false ) {
		if ( ! $this->apikey ) {
			throw new Thrive_Dash_Api_Mailchimp_Exception( 'Please provide an API key.' );
		}

		return $this->makeRequest( $resource, $arguments, strtolower( $method ), $no_body );
	}

	/**
	 * Enable proxy if needed.
	 *
	 * @param string $host
	 * @param int $port
	 * @param bool $ssl
	 * @param string $username
	 * @param string $password
	 *
	 * @return string
	 */
	public function setProxy(
		$host,
		$port,
		$ssl = false,
		$username = null,
		$password = null
	) {
		$scheme = ( $ssl ? 'https://' : 'http://' );

		if ( ! is_null( $username ) ) {
			return $this->options['proxy'] = sprintf( '%s%s:%s@%s:%s', $scheme, $username, $password, $host, $port );
		}

		return $this->options['proxy'] = sprintf( '%s%s:%s', $scheme, $host, $port );
	}

	/**
	 * @return string
	 */
	public function getEndpoint() {
		return $this->endpoint;
	}

	/**
	 * @param $apikey
	 *
	 * @throws Thrive_Dash_Api_Mailchimp_Exception
	 */
	public function detectEndpoint( $apikey ) {
		if ( ! strstr( $apikey, '-' ) ) {
			throw new Thrive_Dash_Api_Mailchimp_Exception( 'There seems to be an issue with your apikey. Please consult Mailchimp' );
		}

		list( , $dc ) = explode( '-', $apikey );
		$this->endpoint = str_replace( 'us1', $dc, $this->endpoint );
	}

	/**
	 * @param $apikey
	 *
	 * @throws Thrive_Dash_Api_Mailchimp_Exception
	 */
	public function setApiKey( $apikey ) {
		$this->detectEndpoint( $apikey );

		$this->apikey = $apikey;
	}

	/**
	 * @param      $resource
	 * @param      $arguments
	 * @param      $method
	 * @param bool $no_body
	 *
	 * @return array|mixed|object
	 * @throws Thrive_Dash_Api_Mailchimp_Exception
	 */
	private function makeRequest( $resource, $arguments, $method, $no_body = false ) {

		$options      = $this->getOptions( $method, $arguments );
		$query_string = '';

		$args = array(
			'timeout'   => 15,
			'headers'   => $options['headers'],
			'sslverify' => false,
		);
		switch ( $method ) {
			case 'get':
				$fn           = 'tve_dash_api_remote_get';
				$body         = isset( $options['query'] ) ? $options['query'] : '';
				$args['body'] = $body;
				$query_string = isset( $options['query'] ) ? '?' . http_build_query( $options['query'] ) : '';
				break;
			case 'delete':
				$args['method']                     = 'DELETE';
				$fn      = 'tve_dash_api_remote_request';
				$no_body = true;
				break;

			case 'put':
				$args['method']                     = 'PUT';
				$fn                                 = 'tve_dash_api_remote_request';
				$body                               = json_encode( $options['json'] );
				$options['headers']['Content-type'] = 'application/json';
				$args['body']                       = $body;
				break;
			default:
				$fn                                 = 'tve_dash_api_remote_post';
				$body                               = json_encode( $options['json'] );
				$options['headers']['Content-type'] = 'application/json';
				$args['body']                       = $body;
				break;
		}
		if ( $no_body ) {
			unset( $args['body'] );
		}

		$url      = $this->endpoint . $resource . $query_string;
		$response = $fn( $url, $args );

		if ( $response instanceof WP_Error ) {
			throw new Thrive_Dash_Api_Mailchimp_Exception( $response->get_error_message() );
		}

		$response_code    = $response['response']['code'];
		$response_message = $this->get_error_message( $response );


		if ( $response_code >= 300 ) {
			throw new Thrive_Dash_Api_Mailchimp_Exception( $response_message );
		}

		$response_body = json_decode( $response['body'] );

		return $response_body;
	}

	/**
	 * @param string $method
	 * @param array $arguments
	 *
	 * @return array
	 */
	private function getOptions( $method, $arguments ) {
		unset( $this->options['json'], $this->options['query'] );

		if ( count( $arguments ) < 1 ) {
			return $this->options;
		}

		if ( $method == 'get' ) {
			$this->options['query'] = $arguments;
		} else {
			$this->options['json'] = $arguments;
		}

		return $this->options;
	}

	/**
	 * @param $method
	 * @param $arguments
	 *
	 * @return string
	 * @throws Thrive_Dash_Api_Mailchimp_Exception
	 */
	public function __call( $method, $arguments ) {
		if ( count( $arguments ) < 1 ) {
			throw new Thrive_Dash_Api_Mailchimp_Exception( 'Magic request methods require a URI and optional options array' );
		}

		if ( ! in_array( $method, $this->allowedMethods ) ) {
			throw new Thrive_Dash_Api_Mailchimp_Exception( 'Method "' . $method . '" is not supported.' );
		}

		$resource = $arguments[0];
		$options  = isset( $arguments[1] ) ? $arguments[1] : array();

		return $this->request( $resource, $options, $method );
	}

	/**
	 * @param array $response
	 *
	 * @return string
	 */
	protected function get_error_message( $response ) {
		$body = wp_remote_retrieve_body( $response );

		$error_data = json_decode( $body, true );

		if ( empty( $error_data ) || empty( $error_data['title'] ) ) {
			return $response['response']['message'];
		}

		return implode( ': ', array_filter( array(
			isset( $error_data['title'] ) ? $error_data['title'] : '',
			isset( $error_data['detail'] ) ? $error_data['detail'] : '',
		) ) );
	}
}
