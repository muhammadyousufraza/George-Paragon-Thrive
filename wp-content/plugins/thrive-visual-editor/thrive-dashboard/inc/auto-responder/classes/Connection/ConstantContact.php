<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_List_Connection_ConstantContact extends Thrive_Dash_List_Connection_Abstract {
	/**
	 * Return the connection type
	 *
	 * @return String
	 */
	public static function get_type() {
		return 'autoresponder';
	}

	/**
	 * Application API Key
	 */
	const TOKEN_URL = 'https://api.constantcontact.com/mashery/account/';

	/**
	 * @return string the API connection title
	 */
	public function get_title() {
		return 'Constant Contact';
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {
		$this->output_controls_html( 'constant-contact' );
	}

	public function getTokenUrl() {
		return self::TOKEN_URL;
	}

	/**
	 * should handle: read data from post / get, test connection and save the details
	 *
	 * on error, it should register an error message (and redirect?)
	 *
	 * @return mixed
	 */
	public function read_credentials() {
		$api_key   = ! empty( $_POST['connection']['api_key'] ) ? sanitize_text_field( $_POST['connection']['api_key'] ) : '';
		$api_token = ! empty( $_POST['connection']['api_token'] ) ? sanitize_text_field( $_POST['connection']['api_token'] ) : '';

		if ( empty( $api_key ) || empty( $api_token ) ) {
			return $this->error( __( 'You must provide a valid Constant Contact API Key and API token', 'thrive-dash' ) );
		}

		$this->set_credentials( array( 'api_key' => $api_key, 'api_token' => $api_token ) );

		$result = $this->test_connection();

		if ( $result !== true ) {
			return $this->error( sprintf( __( 'Could not connect to Constant Contact using the provided API Key and API Token (<strong>%s</strong>)', 'thrive-dash' ), $result ) );
		}

		/**
		 * finally, save the connection details
		 */
		$this->save();

		return $this->success( __( 'Constant Contact connected successfully', 'thrive-dash' ) );
	}

	/**
	 * test if a connection can be made to the service using the stored credentials
	 *
	 * @return bool|string true for success or error message for failure
	 */
	public function test_connection() {
		try {
			/** @var Thrive_Dash_Api_ConstantContact $api */
			$api = $this->get_api();

			$api->getLists();

			return true;

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
		return new Thrive_Dash_Api_ConstantContact( $this->param( 'api_key' ), $this->param( 'api_token' ) );
	}

	/**
	 * get all Subscriber Lists from this API service
	 *
	 * @return array|bool for error
	 */
	protected function _get_lists() {
		try {
			$lists = array();

			/** @var Thrive_Dash_Api_ConstantContact $api */
			$api = $this->get_api();

			foreach ( $api->getLists() as $item ) {
				$lists[] = array(
					'id'   => $item['id'],
					'name' => $item['name'],
				);
			}

			return $lists;

		} catch ( Exception $e ) {
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
		try {
			/** @var Thrive_Dash_Api_ConstantContact $api */
			$api = $this->get_api();

			$user = array(
				'email' => $arguments['email'],
			);

			list( $first_name, $last_name ) = explode( " ", ! empty( $arguments['name'] ) ? $arguments['name'] . " " : ' ' );

			if ( ! empty( $arguments['phone'] ) ) {
				$user['work_phone'] = $arguments['phone'];
			}

			if ( ! empty( $first_name ) ) {
				$user['first_name'] = $first_name;
			}

			if ( ! empty( $last_name ) ) {
				$user['last_name'] = $last_name;
			}

			$api->addContact( $list_identifier, $user );

		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		return true;
	}

	/**
	 * Return the connection email merge tag
	 *
	 * @return String
	 */
	public static function get_email_merge_tag() {
		return '{Email Address}';
	}
}
