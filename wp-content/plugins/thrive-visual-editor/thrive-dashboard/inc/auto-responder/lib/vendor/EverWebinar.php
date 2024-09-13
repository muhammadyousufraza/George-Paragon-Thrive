<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_Api_EverWebinar {

	/**
	 * @var $apiKey
	 */
	protected $_api_key;

	/**
	 * @var array
	 */
	protected $_headers = array();

	/**
	 * @var array
	 */
	protected $_body = array();

	/**
	 * @var string
	 */
	protected $_host = 'https://api.webinarjam.com/everwebinar/';

	/**
	 * Thrive_Dash_Api_EverWebinar constructor.
	 *
	 * @param $options
	 *
	 * @throws Thrive_Dash_Api_EverWebinar_Exception
	 */
	public function __construct( $options ) {
		if ( ! isset( $options['apiKey'] ) || ( isset( $options['apiKey'] ) && empty( $options['apiKey'] ) ) ) {
			throw new Thrive_Dash_Api_EverWebinar_Exception( 'API Key is required' );
		}

		$this->_api_key = $options['apiKey'];

		$this->_set_headers();
		$this->_set_body();
	}

	/**
	 * @return array
	 * @throws Thrive_Dash_Api_EverWebinar_Exception
	 */
	public function get_webinars() {
		$webinars     = array();
		$url          = $this->endpoint_url( 'webinars' );
		$raw_webinars = $this->send( 'post', $url );

		if ( isset( $raw_webinars['webinars'] ) ) {
			foreach ( $raw_webinars['webinars'] as $webinar ) {
				$webinars[] = array(
					'id'   => $webinar['webinar_id'],
					'name' => $webinar['name'],
				);
			}
		}

		return $webinars;
	}

	/**
	 * @param $arguments
	 *
	 * @return array|mixed|object
	 * @throws Thrive_Dash_Api_EverWebinar_Exception
	 */
	public function get_webinar( $arguments ) {
		$url       = $this->endpoint_url( 'webinar' );
		$subscribe = $this->send( 'post', $url, $arguments );

		return $subscribe;
	}

	/**
	 * @param $arguments
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_EverWebinar_Exception
	 */
	public function get_webinar_schedules( $arguments ) {
		$schedules    = array();
		$webinar_data = (array) $this->get_webinar( $arguments );

		if ( ! empty( $webinar_data ) && isset( $webinar_data['webinar']['schedules'] ) ) {

			if ( isset( $webinar_data['webinar']['webinar_id'] ) ) {
				$schedules['webinar_id'] = $webinar_data['webinar']['webinar_id'];
			}

			if ( isset( $webinar_data['webinar']['timezone'] ) ) {
				$schedules['timezone'] = $webinar_data['webinar']['timezone'];
			}

			foreach ( $webinar_data['webinar']['schedules'] as $schedule ) {
				if ( isset( $schedule['schedule'] ) ) {
					$schedules['schedules'][ $schedule['schedule'] ] = array(
						'schedule_id' => $schedule['schedule'],
						'date'        => $schedule['date'],
					);
				}
			}
		}

		return $schedules;
	}

	/**
	 * @param       $webinar_id
	 * @param array $args
	 *
	 * @return array|bool|mixed|object
	 * @throws Thrive_Dash_Api_EverWebinar_Exception
	 */
	public function register_to_webinar( $webinar_id, $args = array() ) {
		if ( ! $webinar_id ) {
			return false;
		}

		$arguments = array(
			'webinar_id' => $webinar_id,
		);

		if ( is_array( $args ) ) {
			$arguments = array_merge( $arguments, $args );
		}

		$url       = $this->endpoint_url( 'register' );
		$subscribe = $this->send( 'post', $url, $arguments );

		return $subscribe;
	}

	/**
	 * @param       $method
	 * @param       $endpoint
	 * @param array $args
	 *
	 * @return array|mixed|object
	 * @throws Thrive_Dash_Api_EverWebinar_Exception
	 */
	public function send( $method, $endpoint, $args = array() ) {

		switch ( strtoupper( $method ) ) {
			case 'GET':
				$fn = 'tve_dash_api_remote_get';
				break;
			default:
				$fn = 'tve_dash_api_remote_post';
				break;
		}

		if ( ! empty( $args ) && is_array( $args ) ) {
			$this->_body = array_merge( $this->_body, $args );
		}

		$response = $fn( $endpoint, array(
			'body'      => isset( $this->_body ) ? json_encode( $this->_body ) : null,
			'timeout'   => 15,
			'headers'   => $this->_headers,
			'sslverify' => false,
		) );

		return $this->_handle_response( $response );
	}

	/**
	 * Set headers
	 */
	private function _set_headers() {
		$this->_headers = array(
			'Content-type' => 'application/json',
			'Accept'       => 'application/json',
		);
	}

	/**
	 * Set body
	 */
	private function _set_body() {
		$this->_body = array(
			'api_key' => $this->_api_key,
		);
	}

	/**
	 * Build enpoint URL
	 *
	 * @param $endpoint
	 *
	 * @return string
	 */
	public function endpoint_url( $endpoint ) {
		return $this->_host . $endpoint;
	}

	/**
	 * @param $response
	 *
	 * @return array|mixed|object
	 * @throws Thrive_Dash_Api_EverWebinar_Exception
	 */
	protected function _handle_response( $response ) {
		if ( $response instanceof WP_Error ) {
			throw new Thrive_Dash_Api_EverWebinar_Exception( __( 'Failed connecting: ', 'thrive' ) . $response->get_error_message() );
		}

		if ( isset( $response['response']['code'] ) ) {
			switch ( $response['response']['code'] ) {
				case 200:
					return json_decode( $response['body'], true );
					break;
				case 400:
					throw new Thrive_Dash_Api_EverWebinar_Exception( 'Missing a required parameter or calling invalid method' );
					break;
				case 401:
					throw new Thrive_Dash_Api_EverWebinar_Exception( 'Invalid API key provided!' );
					break;
				case 404:
					throw new Thrive_Dash_Api_EverWebinar_Exception( 'Can\'t find requested items' );
					break;
			}
		}

		return json_decode( $response['body'], true );
	}
}
