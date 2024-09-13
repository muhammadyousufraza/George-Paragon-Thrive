<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class Thrive_Dash_List_Connection_SendGrid
 * Version 2 of the SendGrid wrapper
 * - instead of "contactdb" endpoint it uses "marketing"
 */
class Thrive_Dash_List_Connection_SendGrid extends Thrive_Dash_List_Connection_Abstract {

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
		return 'SendGrid';
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {

		$related_api = Thrive_Dash_List_Manager::connection_instance( 'sendgridemail' );
		if ( $related_api->is_connected() ) {
			$this->set_param( 'new_connection', 1 );
		}

		$this->output_controls_html( 'sendgrid' );
	}

	/**
	 * just save the key in the database
	 *
	 * @return mixed|void
	 */
	public function read_credentials() {

		$key = ! empty( $_POST['connection']['key'] ) ? sanitize_text_field( $_POST['connection']['key'] ) : '';

		if ( empty( $key ) ) {
			return $this->error( __( 'You must provide a valid SendGrid key', 'thrive-dash' ) );
		}

		$this->set_credentials( map_deep( $_POST['connection'], 'sanitize_text_field' ) );

		$result = $this->test_connection();

		if ( $result !== true ) {
			return $this->error( sprintf( __( 'Could not connect to SendGrid using the provided key (<strong>%s</strong>)', 'thrive-dash' ), $result ) );
		}

		/**
		 * finally, save the connection details
		 */
		$this->save();

		/** @var Thrive_Dash_List_Connection_SendGridEmail $related_api */
		$related_api = Thrive_Dash_List_Manager::connection_instance( 'sendgridemail' );

		if ( isset( $_POST['connection']['new_connection'] ) && intval( $_POST['connection']['new_connection'] ) === 1 ) {
			/**
			 * Try to connect to the email service too
			 */
			$r_result = true;
			if ( ! $related_api->is_connected() ) {
				$r_result = $related_api->read_credentials();
			}

			if ( $r_result !== true ) {
				$this->disconnect();

				return $this->error( $r_result );
			}
		} else {
			/**
			 * let's make sure that the api was not edited and disconnect it
			 */
			$related_api->set_credentials( array() );
			Thrive_Dash_List_Manager::save( $related_api );
		}

		return $this->success( __( 'SendGrid connected successfully', 'thrive-dash' ) );
	}

	/**
	 * test if a connection can be made to the service using the stored credentials
	 *
	 * @return bool|string true for success or error message for failure
	 */
	public function test_connection() {

		/** @var Thrive_Dash_Api_SendGrid $sg */
		$sg = $this->get_api();

		try {
			$sg->client->marketing()->lists()->get();

		} catch ( Thrive_Dash_Api_SendGrid_Exception $e ) {
			return $e->getMessage();
		}

		return true;
	}

	/**
	 * instantiate the API code required for this connection
	 *
	 * @return Thrive_Dash_Api_SendGrid
	 */
	protected function get_api_instance() {

		return new Thrive_Dash_Api_SendGrid( $this->param( 'key' ) );
	}

	/**
	 * get all Subscriber Lists from this API service
	 *
	 * @return array|bool
	 */
	protected function _get_lists() {

		/** @var Thrive_Dash_Api_SendGrid $api */
		$api = $this->get_api();

		$response = $api->client->marketing()->lists()->get();

		if ( $response->statusCode() != 200 ) {
			$body         = $response->body();
			$this->_error = ucwords( $body->errors['0']->message );

			return false;
		}

		$body = $response->body();

		$lists = array();
		foreach ( $body->result as $item ) {
			$lists [] = array(
				'id'   => $item->id,
				'name' => $item->name,
			);
		}

		return $lists;
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

		$contact = new stdClass();

		list( $first_name, $last_name ) = $this->get_name_parts( $arguments['name'] );

		/** @var Thrive_Dash_Api_SendGrid $api */
		$api = $this->get_api();

		$contact->email = $arguments['email'];

		if ( ! empty( $first_name ) ) {
			$contact->first_name = $first_name;
		}

		if ( ! empty( $last_name ) ) {
			$contact->last_name = $last_name;
		}

		if ( ! empty( $arguments['phone'] ) ) {
			$contact->phone_number = $arguments['phone'];
		}

		$contact->list_ids = array( $list_identifier );

		try {

			$args = array(
				'list_ids' => array( $list_identifier ),
				'contacts' => array( $contact ),
			);

			$api->client->marketing()->contacts()->put( $args );

		} catch ( Thrive_Dash_Api_SendGrid_Exception $e ) {
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
		return '{$email}';
	}

	/**
	 * disconnect (remove) this API connection
	 */
	public function disconnect() {

		$this->set_credentials( array() );
		Thrive_Dash_List_Manager::save( $this );

		/**
		 * disconnect the email service too
		 */
		$related_api = Thrive_Dash_List_Manager::connection_instance( 'sendgridemail' );
		$related_api->set_credentials( array() );
		Thrive_Dash_List_Manager::save( $related_api );

		return $this;
	}
}
