<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_List_Connection_ReCaptcha extends Thrive_Dash_List_Connection_Abstract {
	/**
	 * Return the connection type
	 *
	 * @return String
	 */
	public static function get_type() {
		return 'recaptcha';
	}

	/**
	 * @return string the API connection title
	 */
	public function get_title() {
		return 'ReCaptcha';
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {
		$this->output_controls_html( 'recaptcha' );
	}

	/**
	 * should handle: read data from post / get, test connection and save the details
	 *
	 * on error, it should register an error message (and redirect?)
	 *
	 * @return mixed
	 */
	public function read_credentials() {
		$site   = ! empty( $_POST['site_key'] ) ? sanitize_text_field( $_POST['site_key'] ) : '';
		$secret = ! empty( $_POST['secret_key'] ) ? sanitize_text_field( $_POST['secret_key'] ) : '';

		if ( empty( $site ) || empty( $secret ) ) {
			return $this->error( __( 'Both Site Key and Secret Key fields are required', 'thrive-dash' ) );
		}

		//recreate credential object
		$credentials = array(
			'connection' => $this->post( 'connection' ),
			'site_key'   => $site,
			'secret_key' => $secret,
		);

		$this->set_credentials( $credentials );

		$result = $this->test_connection();

		if ( $result !== true ) {
			return $this->error( sprintf( __( 'Incorrect Secret Key.', 'thrive-dash' ), $result ) );
		}

		/**
		 * finally, save the connection details
		 */
		$this->save();

		return $this->success( __( 'ReCaptcha connected successfully!', 'thrive-dash' ) );
	}

	/**
	 * test if the secret key is correct and it exists.
	 *
	 * @return bool|string true for success or error message for failure
	 */
	public function test_connection() {
		$CAPTCHA_URL = 'https://www.google.com/recaptcha/api/siteverify';

		$_capthca_params = array(
			'response' => '',
			'secret'   => $this->param( 'secret_key' ),
		);

		$request  = tve_dash_api_remote_post( $CAPTCHA_URL, array( 'body' => $_capthca_params ) );
		$response = json_decode( wp_remote_retrieve_body( $request ), true );
		if ( ! empty( $response ) && isset( $response['error-codes'] ) && in_array( 'invalid-input-secret', $response['error-codes'] ) ) {
			return false;
		}

		return true;
	}


	public function getSiteKey() {
		$this->get_credentials();

		return $this->param( 'site_key' );
	}

	/**
	 * @return string
	 */
	public function custom_success_message() {
		return ' ';
	}

	/*
	 * Those functions do not apply
	 */
	protected function get_api_instance() {
	}

	protected function _get_lists() {
	}

	public function add_subscriber( $list_identifier, $arguments ) {
	}


}
