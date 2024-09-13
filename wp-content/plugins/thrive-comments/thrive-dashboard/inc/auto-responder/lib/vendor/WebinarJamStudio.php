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
 * API wrapper for WebinarJamStudio
 */
class Thrive_Dash_Api_WebinarJamStudio {
	const NEW_API_URL = 'https://webinarjam.genndi.com/api/';
	const OLD_API_URL = 'https://app.webinarjam.com/api/v2/';
	const API_V4_URL = 'https://api.webinarjam.com/webinarjam/';

	protected $apiKey;
	protected $apiUrl;
	protected $apiVersion;

	/**
	 * @param string $apiKey always required
	 *
	 * @throws Thrive_Dash_Api_WebinarJamStudio_Exception
	 */
	public function __construct( $apiKey, $apiVersion ) {
		if ( empty( $apiKey ) ) {
			throw new Thrive_Dash_Api_WebinarJamStudio_Exception( 'API Key is required' );
		}

		$this->apiKey = $apiKey;
		$this->setWebinarJamApiVersion( $apiVersion );
		$this->setWebinarJamApiUrl();
	}

	/**
	 * Set api version
	 *
	 * @param $version
	 */
	public function setWebinarJamApiVersion( $version ) {
		$this->apiVersion = $version;
	}

	/**
	 * Return api version
	 *
	 * @return int
	 */
	public function getWebinarJamApiVersion() {
		return (int) $this->apiVersion;
	}

	/**
	 * Set api url
	 *
	 * @param $url
	 */
	public function setWebinarJamApiUrl() {

		switch ( (int) $this->apiVersion ) {

			case 4:
				$this->apiUrl = self::API_V4_URL;
				break;

			case 1:
				$this->apiUrl = self::NEW_API_URL;
				break;

			default;
				$this->apiUrl = self::OLD_API_URL;
				break;
		}
	}

	/**
	 * get the list of webinars scheduled for the future
	 *
	 * @return mixed
	 * @throws Thrive_Dash_Api_WebinarJamStudio_Exception
	 */
	public function getUpcomingWebinars() {
		$params = array(
			'api_key' => $this->apiKey,
		);
		$data   = $this->_call( 'webinars', $params, 'POST' );

		return is_array( $data ) && isset( $data['webinars'] ) ? $data['webinars'] : array();
	}

	/**
	 * register a new user to a webinar
	 *
	 * @param $webinarKey
	 * @param $name
	 * @param $email
	 * @param $schedule
	 *
	 * @return bool
	 * @throws Thrive_Dash_Api_WebinarJamStudio_Exception
	 */
	public function registerToWebinar( $webinarKey, $name, $email, $phone, $schedule ) {
		$params = array(
			'api_key'    => $this->apiKey,
			'webinar_id' => $webinarKey,
			'email'      => $email,
			'schedule'   => $schedule,
			'phone'      => $phone ? $phone : ' ',
		);

		if ( $this->apiVersion == 1 || $this->apiVersion == 4 ) {
			list( $params['first_name'], $params['last_name'] ) = $this->_getNameParts( $name );
		} else {
			$params['name'] = $name ? $name : ' ';
		}

		$this->_call( 'register', $params, 'POST' );

		return true;
	}

	protected function _getNameParts( $full_name ) {
		if ( empty( $full_name ) ) {
			return array( '', '' );
		}
		$parts = explode( ' ', $full_name );

		if ( count( $parts ) == 1 ) {
			return array(
				$parts[0],
				'',
			);
		}
		$last_name  = array_pop( $parts );
		$first_name = implode( ' ', $parts );

		return array(
			sanitize_text_field( $first_name ),
			sanitize_text_field( $last_name ),
		);
	}

	/**
	 * retrieve info for a specific webinar
	 *
	 * @param string $webinar_id
	 *
	 * @return array
	 *
	 * @throws Thrive_Dash_Api_WebinarJamStudio_Exception
	 */
	public function getWebinar( $webinar_id ) {
		$params = array(
			'api_key'    => $this->apiKey,
			'webinar_id' => $webinar_id,
		);

		return $this->_call( 'webinar', $params, 'POST' );
	}

	/**
	 * perform a webservice call
	 *
	 * @param string $path api path
	 * @param array $params request parameters
	 * @param string $method GET or POST
	 *
	 * @throws Thrive_Dash_Api_WebinarJamStudio_Exception
	 */
	protected function _call( $path, $params = array(), $method = 'GET' ) {
		$url = $this->apiUrl . ltrim( $path, '/' );

		$args = array(
			'headers' => array(
				'Content-type' => 'application/json',
				'Accept'       => 'application/json',
			),
		);

		switch ( $method ) {
			case 'POST':
				$args['body'] = json_encode( $params );
				$result       = tve_dash_api_remote_post( $url, $args );
				break;
			case 'GET':
			default:
				$query_string = '';
				foreach ( $params as $k => $v ) {
					$query_string .= $query_string ? '&' : '';
					$query_string .= $k . '=' . $v;
				}
				if ( $query_string ) {
					$url .= ( strpos( $url, '?' ) !== false ? '&' : '?' ) . $query_string;
				}

				$result = tve_dash_api_remote_get( $url, $args );
				break;
		}

		if ( $result instanceof WP_Error ) {
			throw new Thrive_Dash_Api_WebinarJamStudio_Exception( 'Failed connecting to WebinarJamStudio: ' . $result->get_error_message() );
		}

		$body = trim( wp_remote_retrieve_body( $result ) );

		$data = json_decode( $body, true );

		if ( ! is_array( $data ) ) {
			throw new Thrive_Dash_Api_WebinarJamStudio_Exception( 'API call error. Response was: ' . $body );
		}

		if ( empty( $data['status'] ) || $data['status'] != 'success' ) {
			$message = isset( $data['message'] ) ? $data['message'] : '';
			if ( empty( $message ) ) {
				$message = 'Raw response was: ' . $body;
			}
			throw new Thrive_Dash_Api_WebinarJamStudio_Exception( 'API call error: ' . $message );
		}

		return $data;
	}
}