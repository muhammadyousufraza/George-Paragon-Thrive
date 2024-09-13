<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class Thrive_Dash_List_Connection_SendOwl extends Thrive_Dash_List_Connection_Abstract {

	const sendowl_url = 'https://www.sendowl.com';

	/**
	 * Return the connection type
	 *
	 * @return String
	 */
	public static function get_type() {
		return 'sellings';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return 'SendOwl';
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {
		$this->output_controls_html( 'sendowl' );
	}

	/**
	 * just save the key in the database
	 *
	 * @return mixed|void
	 */
	public function read_credentials() {
		$key = ! empty( $_POST['connection']['key'] ) ? sanitize_text_field( $_POST['connection']['key'] ) : '';

		if ( empty( $key ) ) {
			return $this->error( __( 'You must provide a valid SendOwl key', 'thrive-dash' ) );
		}

		$url = self::sendowl_url;

		$this->set_credentials( $this->post( 'connection' ) );

		$result = $this->test_connection();

		if ( $result !== true ) {
			return $this->error( sprintf( __( 'Could not connect to SendOwl using the provided key (<strong>%s</strong>)', 'thrive-dash' ), $result ) );
		}

		/**
		 * finally, save the connection details
		 */
		$this->save();

		return $this->success( __( 'SendOwl connected successfully', 'thrive-dash' ) );
	}

	/**
	 * test if a connection can be made to the service using the stored credentials
	 *
	 * @return bool|string true for success or error message for failure
	 */
	public function test_connection() {
		/** @var Thrive_Dash_Api_SendOwl $so */

		$so = $this->get_api();

		try {
			$so->getProducts();
		} catch ( Thrive_Dash_Api_SendOwl_Exception $e ) {
			return $e->getMessage();
		}

		return true;
	}

	/**
	 * add a contact to a list
	 *
	 * @param mixed $list_identifier
	 * @param array $arguments
	 *
	 * @return bool|string true for success or string error message for failure
	 */
	public function add_subscriber( $list_identifier, $arguments ) {
	}

	/**
	 * instantiate the API code required for this connection
	 *
	 * @return mixed
	 */
	protected function get_api_instance() {

		if ( empty( $this->param( 'key' ) ) || empty( $this->param( 'secret' ) ) ) {
			return null;
		}

		return new Thrive_Dash_Api_SendOwl( array(
				'apiKey'    => $this->param( 'key' ),
				'secretKey' => $this->param( 'secret' ),
			)
		);
	}

	/**
	 * get all Subscriber Lists from this API service
	 *
	 * @return array
	 */
	protected function _get_lists() {
		return array();
	}
}
