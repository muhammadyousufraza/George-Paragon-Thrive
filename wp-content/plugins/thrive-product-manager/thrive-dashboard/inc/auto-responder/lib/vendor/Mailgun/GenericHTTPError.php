<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_Api_Mailgun_GenericHTTPError extends Exception {
	protected $httpResponseCode;
	protected $httpResponseBody;

	public function __construct( $message = null, $response_code = null, $response_body = null, $code = 0, \Exception $previous = null ) {
		parent::__construct( $message, $code, $previous );

		$this->httpResponseCode = $response_code;
		$this->httpResponseBody = $response_body;
	}

	public function getHttpResponseCode() {
		return $this->httpResponseCode;
	}

	public function getHttpResponseBody() {
		return $this->httpResponseBody;
	}
}

?>
