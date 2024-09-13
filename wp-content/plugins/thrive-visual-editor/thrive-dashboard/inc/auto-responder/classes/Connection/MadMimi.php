<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_List_Connection_MadMimi extends Thrive_Dash_List_Connection_Abstract {
	/**
	 * Return the connection type
	 *
	 * @return String
	 */
	public static function get_type() {
		return 'autoresponder';
	}

	/**
	 * @return string the API connection title
	 */
	public function get_title() {
		return 'MadMimi';
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {
		$this->output_controls_html( 'madmimi' );
	}

	/**
	 * should handle: read data from post / get, test connection and save the details
	 *
	 * on error, it should register an error message (and redirect?)
	 *
	 * @return mixed
	 */
	public function read_credentials() {
		$key      = ! empty( $_POST['connection']['key'] ) ? sanitize_text_field( $_POST['connection']['key'] ) : '';
		$username = ! empty( $_POST['connection']['username'] ) ? sanitize_text_field( $_POST['connection']['username'] ) : '';

		if ( empty( $key ) || empty( $username ) ) {
			return $this->error( __( 'Username and API Key are required', 'thrive-dash' ) );
		}

		$this->set_credentials( array( 'key' => $key, 'username' => $username ) );

		$result = $this->test_connection();

		if ( $result !== true ) {
			return $this->error( sprintf( __( 'Could not connect to MadMimi using the provided data', 'thrive-dash' ), $result ) );
		}

		/**
		 * finally, save the connection details
		 */
		$this->save();

		return $this->success( __( 'MadMimi connected successfully', 'thrive-dash' ) );
	}

	/**
	 * test if a connection can be made to the service using the stored credentials
	 *
	 * @return bool|string true for success or error message for failure
	 */
	public function test_connection() {
		/** @var Thrive_Dash_Api_MadMimi $api */
		$api = $this->get_api();
		/**
		 * just try getting the list of the promotions as a connection test
		 */
		try {
			$api->getAudienceLists(); // this will throw the exception if there is a connection problem
		} catch ( Thrive_Dash_Api_MadMimi_Exception $e ) {
			return $e->getMessage();
		}

		return true;
	}

	/**
	 * instantiate the API code required for this connection
	 *
	 * @return mixed
	 */
	protected function get_api_instance() {
		return new Thrive_Dash_Api_MadMimi( $this->param( 'key' ), $this->param( 'username' ) );
	}

	/**
	 * get all Subscriber Lists from this API service
	 *
	 * @return array|bool for error
	 */
	protected function _get_lists() {
		/** @var Thrive_Dash_Api_MadMimi $api */
		$api = $this->get_api();
		try {
			$lists        = array();
			$audienceList = $api->getAudienceLists();
			foreach ( $audienceList as $key => $item ) {
				$lists [] = array(
					'id'   => $item['name'],
					'name' => $item['name'],
				);
			}

			return $lists;
		} catch ( Thrive_Dash_Api_MadMimi_Exception $e ) {
			$this->_error = $e->getMessage();

			return false;
		}
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
		/** @var Thrive_Dash_Api_MadMimi $api */
		$api = $this->get_api();

		try {
			$api->registerToAudienceList( $list_identifier, $arguments['name'], $arguments['email'] );

			return true;
		} catch ( Thrive_Dash_Api_MadMimi_Exception $e ) {
			return $e->getMessage();
		} catch ( Exception $e ) {
			return $e->getMessage();
		}
	}

	/**
	 * Return the connection email merge tag
	 *
	 * @return String
	 */
	public static function get_email_merge_tag() {
		return '(email)';
	}
}
