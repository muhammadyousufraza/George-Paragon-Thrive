<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_Api_Sendlane {

	/**
	 * @var string API URL - taken from the sendlane API tab
	 */
	protected $_apiUrl;

	/**
	 * @var string API KEY - taken from the sendlane API tab
	 */
	protected $_apiKey;

	/**
	 * @var string API KEY - taken from the sendlane API tab
	 */
	protected $_hashKey;

	/**
	 * @var bool connection status
	 */
	protected $connectionStatus = false;

	/**
	 * Thrive_Dash_Api_Sendlane constructor.
	 *
	 * @param $apiKey
	 * @param $hashKey
	 * @param $apiUrl
	 *
	 * @throws Thrive_Dash_Api_Sendlane_Exception
	 */
	public function __construct( $apiKey, $hashKey, $apiUrl ) {
		if ( empty( $apiKey ) || empty( $hashKey ) || empty( $apiUrl ) ) {
			throw new Thrive_Dash_Api_Sendlane_Exception( 'Both API Key and Hash Key are required' );
		}

		$this->_apiKey  = $apiKey;
		$this->_hashKey = $hashKey;
		$this->setApiUrl( $apiUrl );
	}

	/**
	 * Set Api url, always as https://domain.sendlane.com
	 *
	 * @param $apiUrl
	 */
	protected function setApiUrl( $apiUrl ) {
		$this->_apiUrl = $apiUrl . '/api/v1/';

		if ( preg_match( '/http/', $this->_apiUrl ) ) {
			$this->_apiUrl = preg_replace( "/^http:/i", "https:", $this->_apiUrl );
		}

		if ( ! preg_match( '/https/', $this->_apiUrl ) ) {
			$this->_apiUrl = 'https://' . $this->_apiUrl;
		}
	}

	/**
	 * Do a call to an api endpoint
	 * Sendlane requires all api requests to be made via POST method: https://help.sendlane.com/knowledgebase/api-docs
	 *
	 * @param $api_action
	 * @param array $args
	 *
	 * @return array
	 */
	public function call( $api_action, $args = array() ) {
		$args['api']  = $this->_apiKey;
		$args['hash'] = $this->_hashKey;
		$url          = $this->_apiUrl . $api_action . '?' . http_build_query( $args );
		$result       = tve_dash_api_remote_post( $url );
		$body         = trim( wp_remote_retrieve_body( $result ) );

		return array(
			'data'   => json_decode( $body, true ),
			'status' => trim( wp_remote_retrieve_response_code( $result ) ),
		);
	}

	/**
	 * Set connection status
	 *
	 * @param $status
	 */
	public function setConnectionStatus( $status ) {
		$this->connectionStatus = $status;
	}

	/**
	 * Get connection status
	 *
	 * @return bool
	 */
	public function getConnectionStatus() {
		return $this->connectionStatus;
	}
}
