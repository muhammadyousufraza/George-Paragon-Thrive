<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

require_once dirname( dirname( __FILE__ ) ) . '/Sendy/Exception.php';

class Thrive_Dash_Api_Sendy {

	protected $url;

	/**
	 * Thrive_Dash_Api_Sendy constructor.
	 *
	 * @param $url string URL there the Sendy is installed
	 */
	public function __construct( $url ) {
		$this->url = $url;
	}

	/**
	 * Test the URL
	 * Makes an POST request to the URL and check the response
	 *
	 * @return bool
	 */
	public function testUrl() {
		$url      = $this->url;
		$response = tve_dash_api_remote_post( $url );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		if ( $response['response']['code'] != 200 ) {
			return false;
		}

		return true;
	}

	/**
	 * @param        $email      string
	 * @param        $list_id    string
	 * @param        $name       string
	 * @param        $api_key    string
	 * @param        $phone      string
	 *
	 * @return bool
	 * @throws Thrive_Dash_Api_Sendy_Exception
	 *
	 */
	public function subscribe( $email, $list_id, $api_key, $name = '', $phone = null ) {
		if ( empty( $email ) ) {
			throw new Thrive_Dash_Api_Sendy_Exception( 'Invalid Email' );
		}

		if ( empty( $list_id ) ) {
			throw new Thrive_Dash_Api_Sendy_Exception( 'List not set' );
		}

		$args = array(
			'email'   => $email,
			'list'    => $list_id,
			'boolean' => 'true',
			'phone'   => $phone,
			'api_key' => $api_key,
		);

		if ( ! empty( $name ) ) {
			$args['name'] = $name;
		}

		$response = tve_dash_api_remote_post( rtrim( $this->url, "/" ) . '/subscribe', array(
			'body' => $args,
		) );

		if ( is_wp_error( $response ) ) {
			throw new Thrive_Dash_Api_Sendy_Exception( 'Error occurred' );
		}

		if ( $response['body'] == 1 ) {
			return true;
		} else {
			throw new Thrive_Dash_Api_Sendy_Exception( $response['body'] );
		}

		return false;
	}
}
