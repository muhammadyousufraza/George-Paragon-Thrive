<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class Thrive_Dash_List_Connection_Google extends Thrive_Dash_List_Connection_Abstract {

	protected $_key = 'google';

	/**
	 * Thrive_Dash_List_Connection_Google constructor.
	 */
	public function __construct() {
		$this->set_credentials( Thrive_Dash_List_Manager::credentials( $this->_key ) );
	}

	/**
	 * Return the connection type
	 *
	 * @return String
	 */
	public static function get_type() {
		return 'social';
	}

	/**
	 * @return string the API connection title
	 */
	public function get_title() {
		return 'Google';
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {
		$this->output_controls_html( 'google' );
	}

	/**
	 * should handle: read data from post / get, test connection and save the details
	 *
	 * on error, it should register an error message (and redirect?)
	 *
	 * @return mixed
	 */
	public function read_credentials() {
		$client_id     = ! empty( $_POST['client_id'] ) ? sanitize_text_field( $_POST['client_id'] ) : '';
		$client_secret = ! empty( $_POST['client_secret'] ) ? sanitize_text_field( $_POST['client_secret'] ) : '';
		$api_key       = ! empty( $_POST['api_key'] ) ? sanitize_text_field( $_POST['api_key'] ) : '';

		if ( empty( $client_id ) || empty( $client_secret ) ) {
			return $this->error( __( 'Both Client ID and Client Secret fields are required', 'thrive-dash' ) );
		}

		$this->set_credentials( array( 'client_id' => $client_id, 'client_secret' => $client_secret, 'api_key' => $api_key ) );

		$result = $this->test_connection();

		if ( $result !== true ) {
			return $this->error( sprintf( __( 'Incorrect Client ID.', 'thrive-dash' ), $result ) );
		}

		/**
		 * finally, save the connection details
		 */
		$this->save();

		return $this->success( __( 'Google connected successfully!', 'thrive-dash' ) );
	}

	/**
	 * test if the secret key is correct, and it exists.
	 *
	 * @return bool|string true for success or error message for failure
	 */
	public function test_connection() {
		//TODO: implement testing the connection
		return true;
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
