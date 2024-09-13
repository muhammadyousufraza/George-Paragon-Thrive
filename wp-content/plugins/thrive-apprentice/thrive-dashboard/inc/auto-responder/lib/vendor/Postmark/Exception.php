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
 * The exception thrown when the Postmark Client recieves an error from the API.
 */
class Thrive_Dash_Api_Postmark_Exception extends Exception {
	var $message;
	var $httpStatusCode;
	var $postmarkApiErrorCode;
}

?>