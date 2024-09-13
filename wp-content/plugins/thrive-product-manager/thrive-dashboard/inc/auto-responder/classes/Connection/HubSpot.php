<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_List_Connection_HubSpot extends Thrive_Dash_List_Connection_Abstract {
	/**
	 * @return string the API connection title
	 */
	public function get_title() {
		return 'HubSpot';
	}

	/**
	 * @return string
	 */
	public function get_list_sub_title() {
		return __( 'Choose from the following contact lists', 'thrive-dash' );
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {
		$this->output_controls_html( 'hubspot' );
	}

	/**
	 * should handle: read data from post / get, test connection and save the details
	 *
	 * on error, it should register an error message (and redirect?)
	 *
	 * @return mixed
	 */
	public function read_credentials() {
		$connection = $this->post( 'connection' );
		$key        = $connection['key'];

		if ( empty( $key ) ) {
			return $this->error( __( 'You must provide a valid HubSpot key', 'thrive-dash' ) );
		}

		$this->set_credentials( $connection );

		$result = $this->test_connection();

		if ( $result !== true ) {
			return $this->error( sprintf( __( 'Could not connect to HubSpot using the provided key (<strong>%s</strong>)', 'thrive-dash' ), $result ) );
		}

		/**
		 * finally, save the connection details
		 */
		$this->save();

		return $this->success( __( 'HubSpot connected successfully', 'thrive-dash' ) );
	}

	/**
	 * test if a connection can be made to the service using the stored credentials
	 *
	 * @return bool|string true for success or error message for failure
	 */
	public function test_connection() {
		$api = $this->get_api();
		/**
		 * just try getting the static contact lists as a connection test
		 */
		try {
			$api->getContactLists(); // this will throw the exception if there is a connection problem
		} catch ( Thrive_Dash_Api_HubSpot_Exception $e ) {
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
		$key     = $this->param( 'key' );
		$version = $this->param( 'version' );

		if ( ! empty( $version ) && $version == 2 ) {
			return new Thrive_Dash_Api_HubSpotV2( $key );
		}

		return new Thrive_Dash_Api_HubSpot( $key );
	}

	/**
	 * get all Subscriber Lists from this API service
	 *
	 * @return array|bool for error
	 */
	protected function _get_lists() {
		$api = $this->get_api();

		try {
			$lists        = array();
			$contactLists = $api->getContactLists();
			foreach ( $contactLists as $key => $item ) {
				$lists [] = array(
					'id'      => $item['listId'],
					'dynamic' => ! empty( $item['dynamic'] ),
					'name'    => $item['name'],
				);
			}

			return $lists;
		} catch ( Thrive_Dash_Api_HubSpot_Exception $e ) {
			$this->_error = $e->getMessage();

			return false;
		}
	}

	/**
	 * add a contact to a static list
	 *
	 * @param mixed $list_identifier
	 * @param array $arguments
	 *
	 * @return mixed
	 */
	public function add_subscriber( $list_identifier, $arguments ) {
		$api = $this->get_api();

		try {
			$name  = empty( $arguments['name'] ) ? '' : $arguments['name'];
			$phone = empty( $arguments['phone'] ) ? '' : $arguments['phone'];
			$api->registerToContactList( $list_identifier, $name, $arguments['email'], $phone );

			return true;
		} catch ( Thrive_Dash_Api_HubSpot_Exception $e ) {
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
		return '{{contact.email}}';
	}
}
