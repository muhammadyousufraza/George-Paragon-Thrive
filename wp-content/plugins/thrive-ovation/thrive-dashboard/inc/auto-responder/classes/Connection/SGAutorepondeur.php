<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class Thrive_Dash_List_Connection_SGAutorepondeur extends Thrive_Dash_List_Connection_Abstract {
	/**
	 * Return the connection type
	 *
	 * @return String
	 */
	public static function get_type() {
		return 'autoresponder';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return 'SG Autorepondeur';
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {
		$this->output_controls_html( 'sg-autorepondeur' );
	}

	/**
	 * just save the key in the database
	 *
	 * @return mixed|void
	 */
	public function read_credentials() {

		if ( empty( $_POST['connection']['memberid'] ) ) {
			return $this->error( __( 'You must provide a valid SG-Autorepondeur Member ID', 'thrive-dash' ) );
		}

		if ( empty( $_POST['connection']['key'] ) ) {
			return $this->error( __( 'You must provide a valid SG-Autorepondeur key', 'thrive-dash' ) );
		}

		$this->set_credentials( $this->post( 'connection' ) );

		$result = $this->test_connection();

		if ( $result !== true ) {
			return $this->error( sprintf( __( 'Could not connect to SG-Autorepondeur using the provided key (<strong>%s</strong>)', 'thrive-dash' ), $result ) );
		}

		/**
		 * finally, save the connection details
		 */
		$this->save();

		return $this->success( __( 'SG Autorepondeur connected successfully', 'thrive-dash' ) );
	}

	/**
	 * test if a connection can be made to the service using the stored credentials
	 *
	 * @return bool|string true for success or error message for failure
	 */
	public function test_connection() {
		/**
		 * just try getting a list as a connection test
		 */

		try {
			/** @var Thrive_Dash_Api_SGAutorepondeur $sg */
			$sg = $this->get_api();
			$sg->call( 'get_list' );

		} catch ( Exception $e ) {
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
		return new Thrive_Dash_Api_SGAutorepondeur( $this->param( 'memberid' ), $this->param( 'key' ) );
	}

	/**
	 * get all Subscriber Lists from this API service
	 *
	 * @return array|bool
	 */
	protected function _get_lists() {

		try {
			/** @var Thrive_Dash_Api_SGAutorepondeur $sg */
			$sg = $this->get_api();

			$sg->set( 'limite', array( 0, 9999 ) );
			$raw = $sg->call( 'get_list' );

			$lists = array();

			if ( empty( $raw->reponse ) ) {
				return array();
			}
			foreach ( $raw->reponse as $item ) {
				$lists [] = array(
					'id'   => $item->listeid,
					'name' => $item->nom,
				);
			}

			return $lists;
		} catch ( Exception $e ) {
			$this->_error = $e->getMessage() . ' ' . __( "Please re-check your API connection details.", 'thrive-dash' );

			return false;
		}
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
		list( $first_name, $last_name ) = $this->get_name_parts( $arguments['name'] );

		/** @var Thrive_Dash_Api_SGAutorepondeur $api */
		$api = $this->get_api();

		$email = strtolower( $arguments['email'] );

		$api->set( 'listeid', $list_identifier );
		$api->set( 'email', $email );

		/**
		 * The names are inversed for a reason, SG will not accept
		 * sending only the first_name, so the first name needs to be set as the name
		 */
		if ( ! empty( $first_name ) && empty( $last_name ) ) {
			$api->set( 'name', $first_name );
		} elseif ( ! empty( $first_name ) && ! empty( $last_name ) ) {
			$api->set( 'first_name', $first_name );
			$api->set( 'name', $last_name );
		}

		if ( isset( $arguments['phone'] ) && ! empty( $arguments['phone'] ) ) {
			$api->set( 'telephone', $arguments['phone'] );
		}

		try {
			$api->call( 'set_subscriber' );

			return true;
		} catch ( Exception $e ) {
			return $e->getMessage() ? $e->getMessage() : __( 'Unknown SG-Autorepondeur Error', 'thrive-dash' );
		}

	}

	/**
	 * Return the connection email merge tag
	 *
	 * @return String
	 */
	public static function get_email_merge_tag() {
		return '++email++';
	}

	/**
	 * disconnect (remove) this API connection
	 */
	public function disconnect() {

		$this->set_credentials( array() );
		Thrive_Dash_List_Manager::save( $this );

		return $this;
	}


}
