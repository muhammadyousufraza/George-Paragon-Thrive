<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_List_Connection_WebinarJamStudio extends Thrive_Dash_List_Connection_Abstract {
	/**
	 * Return the connection type
	 *
	 * @return String
	 */
	public static function get_type() {
		return 'webinar';
	}

	/**
	 * @return string the API connection title
	 */
	public function get_title() {
		return 'WebinarJamStudio';
	}

	/**
	 * these are called webinars, not lists
	 *
	 * @return string
	 */
	public function get_list_sub_title() {
		return 'Choose from the following upcoming webinars';
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {
		$this->output_controls_html( 'webinarjamstudio' );
	}

	/**
	 * should handle: read data from post / get, test connection and save the details
	 *
	 * on error, it should register an error message (and redirect?)
	 *
	 * @return mixed
	 */
	public function read_credentials() {
		$key = ! empty( $_POST['connection']['key'] ) ? sanitize_text_field( $_POST['connection']['key'] ) : '';

		if ( empty( $key ) ) {
			return $this->error( __( 'You must provide a valid WebinarJamStudio key', 'thrive-dash' ) );
		}

		$this->set_credentials( $this->post( 'connection' ) );
		$result = $this->test_connection();

		if ( $result !== true ) {
			return $this->error( sprintf( __( 'Could not connect to WebinarJamStudio using the provided key (<strong>%s</strong>)', 'thrive-dash' ), $result ) );
		}

		/**
		 * finally, save the connection details
		 */
		$this->save();

		return $this->success( __( 'WebinarJamStudio connected successfully', 'thrive-dash' ) );

	}

	/**
	 * test if a connection can be made to the service using the stored credentials
	 *
	 * @return bool|string true for success or error message for failure
	 */
	public function test_connection() {
		/** @var Thrive_Dash_Api_WebinarJamStudio $api */
		$api = $this->get_api();

		/**
		 * just try getting the list of the webinars as a connection test
		 */
		try {
			$api->getUpcomingWebinars(); // this will throw the exception if there is a connection problem
		} catch ( Thrive_Dash_Api_WebinarJamStudio_Exception $e ) {
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
	 * @return mixed
	 */
	public function add_subscriber( $list_identifier, $arguments ) {
		/** @var Thrive_Dash_Api_WebinarJamStudio $api */
		$api = $this->get_api();

		try {
			$name  = empty( $arguments['name'] ) ? '' : $arguments['name'];
			$phone = ! isset( $arguments['phone'] ) || empty( $arguments['phone'] ) ? '' : $arguments['phone'];

			$webinar  = $api->getWebinar( $list_identifier );
			$schedule = 0;

			if ( 4 === $api->getWebinarJamApiVersion() ) {
				$schedule = isset( $webinar['webinar']['schedules'][0]['schedule'] ) ? $webinar['webinar']['schedules'][0]['schedule'] : $schedule;
			}

			$api->registerToWebinar( $list_identifier, $name, $arguments['email'], $phone, $schedule );

			return true;
		} catch ( Thrive_Dash_Api_WebinarJamStudio_Exception $e ) {
			return $e->getMessage();
		} catch ( Exception $e ) {
			return $e->getMessage();
		}
	}

	/**
	 * instantiate the API code required for this connection
	 *
	 * @return mixed
	 */
	protected function get_api_instance() {
		return new Thrive_Dash_Api_WebinarJamStudio( $this->param( 'key' ), $this->param( 'version' ) );
	}

	/**
	 * get all Subscriber Lists from this API service
	 *
	 * @return array|bool for error
	 */
	protected function _get_lists() {
		/** @var Thrive_Dash_Api_WebinarJamStudio $api */
		$api = $this->get_api();
		try {
			$lists    = array();
			$webinars = $api->getUpcomingWebinars();
			foreach ( $webinars as $key => $item ) {
				$lists [] = array(
					'id'   => $item['webinar_id'],
					'name' => $item['name'],
				);
			}

			return $lists;
		} catch ( Thrive_Dash_Api_WebinarJamStudio_Exception $e ) {
			$this->_error = $e->getMessage();

			return false;
		}
	}


}
