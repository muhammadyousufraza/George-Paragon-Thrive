<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_List_Connection_iContact extends Thrive_Dash_List_Connection_Abstract {
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
		return 'iContact';
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {
		$this->output_controls_html( 'iContact' );
	}

	/**
	 * should handle: read data from post / get, test connection and save the details
	 *
	 * on error, it should register an error message (and redirect?)
	 *
	 * @return mixed
	 */
	public function read_credentials() {
		$apiId       = ! empty( $_POST['connection']['appId'] ) ? sanitize_text_field( $_POST['connection']['appId'] ) : '';
		$apiUsername = ! empty( $_POST['connection']['apiUsername'] ) ? sanitize_text_field( $_POST['connection']['apiUsername'] ) : '';
		$apiPassword = ! empty( $_POST['connection']['apiPassword'] ) ? sanitize_text_field( $_POST['connection']['apiPassword'] ) : '';

		if ( empty( $apiId ) || empty( $apiUsername ) || empty( $apiPassword ) ) {
			return $this->error( __( 'You must provide a valid iContact AppID/Username/Password', 'thrive-dash' ) );
		}

		$this->set_credentials( $this->post( 'connection' ) );

		$result = $this->test_connection();

		if ( $result !== true ) {
			return $this->error( sprintf( __( 'Could not connect to iContact: %s', 'thrive-dash' ), $result ) );
		}

		/**
		 * finally, save the connection details
		 */
		$this->save();

		return $this->success( __( 'iContact connected successfully', 'thrive-dash' ) );
	}

	/**
	 * test if a connection can be made to the service using the stored credentials
	 *
	 * @return bool|string true for success or error message for failure
	 */
	public function test_connection() {
		$lists = $this->_get_lists();
		if ( $lists === false ) {
			return $this->_error;
		}

		return true;
	}

	/**
	 * instantiate the API code required for this connection
	 *
	 * @return Thrive_Dash_Api_iContact
	 */
	protected function get_api_instance() {
		return Thrive_Dash_Api_iContact::getInstance()->setConfig( $this->get_credentials() );
	}

	/**
	 * get all Subscriber Lists from this API service
	 *
	 * @return array|bool for error
	 */
	protected function _get_lists() {
		$api   = $this->get_api();
		$lists = array();

		try {
			$data = $api->getLists();
			if ( count( $data ) ) {
				foreach ( $data as $item ) {
					$lists[] = array(
						'id'   => $item->listId,
						'name' => $item->name,
					);
				}
			}
		} catch ( Exception $e ) {
			$this->_error = $e->getMessage();

			return false;
		}

		return $lists;
	}

	/**
	 * add a contact to a list
	 *
	 * @param array $list_identifier
	 * @param array $arguments
	 *
	 * @return mixed true -> success; string -> error;
	 */
	public function add_subscriber( $list_identifier, $arguments ) {
		$sEmail  = $arguments['email'];
		$sStatus = 'normal';
		$sPrefix = null;
		$sPhone  = null;
		list( $sFirstName, $sLastName ) = $this->get_name_parts( $arguments['name'] );
		$sSuffix     = null;
		$sStreet     = null;
		$sStreet2    = null;
		$sCity       = null;
		$sState      = null;
		$sPostalCode = null;
		$sPhone      = empty( $arguments['phone'] ) ? '' : $arguments['phone'];

		try {

			/** @var Thrive_Dash_Api_iContact $api */
			$api = $this->get_api();

			$contact = $api->addContact( $sEmail, $sStatus, $sPrefix, $sFirstName, $sLastName, $sSuffix, $sStreet, $sStreet2, $sCity, $sState, $sPostalCode, $sPhone );
			if ( empty( $contact ) || ! is_object( $contact ) || empty( $contact->contactId ) ) {
				throw new Thrive_Dash_Api_iContact_Exception( 'Unable to save contact' );
			}

			$api->subscribeContactToList( $contact->contactId, $list_identifier );

			return true;

		} catch ( Thrive_Dash_Api_iContact_Exception $e ) {

			return $e->getMessage();

		} catch ( Exception $e ) {

			return $e->getMessage();
		}

	}

}
