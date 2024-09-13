<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

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

		$this->set_credentials( $this->post( 'connection' ) );

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
			$sg->client->contactdb()->lists()->get();

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

		$response = $api->client->contactdb()->lists()->get();

		if ( $response->statusCode() != 200 ) {
			$body = $response->body();

			$this->_error = ucwords( $body->errors['0']->message );

			return false;
		}

		$body = $response->body();

		$lists = array();
		foreach ( $body->lists as $item ) {
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

		$args = new stdClass();

		list( $first_name, $last_name ) = $this->get_name_parts( $arguments['name'] );

		/** @var Thrive_Dash_Api_SendGrid $api */
		$api = $this->get_api();

		$args->email = $arguments['email'];

		if ( ! empty( $first_name ) ) {
			$args->first_name = $first_name;
		}
		if ( ! empty( $last_name ) ) {
			$args->last_name = $last_name;
		}

		if ( ! empty( $arguments['phone'] ) ) {

			$custom_field_id = '';

			try {
				$response = $api->client->contactdb()->custom_fields()->get();
				$fields   = $response->body();

				foreach ( $fields->custom_fields as $custom_field ) {
					if ( isset( $custom_field->name ) && ( $custom_field->name === 'phone' ) ) {
						$custom_field_id = $custom_field->id;
					}
				}

				if ( $custom_field_id ) {
					try {
						$response = $api->client->contactdb()->custom_fields()->_( $custom_field_id )->get();

					} catch ( Thrive_Dash_Api_SendGrid_Exception $e ) {
						return $e->getMessage();
					}
					$result = $response->body();

					if ( isset( $result->errors ) ) {
						$request_body = json_decode( '{
							  "name": "phone",
							  "type": "number"
							}' );

						$api->client->contactdb()->custom_fields()->post( $request_body );
					}

					$args->phone = $arguments['phone'];
				}
			} catch ( Thrive_Dash_Api_SendGrid_Exception $e ) {
				return $e->getMessage();
			}
		}

		try {
			$response = $api->client->contactdb()->recipients()->post( array( $args ) );
		} catch ( Thrive_Dash_Api_SendGrid_Exception $e ) {
			return $e->getMessage();
		}

		$body = $response->body();
		if ( $body->error_count == 0 ) {
			$request_body = null;
			$recipient_id = $body->persisted_recipients[0];

			try {
				$api->client->contactdb()->lists()->_( $list_identifier )->recipients()->_( $recipient_id )->post( $request_body );

			} catch ( Thrive_Dash_Api_SendGrid_Exception $e ) {
				return $e->getMessage();
			}

			return true;
		}

		return __( 'Unknown Sendgrid Error', 'thrive-dash' );

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
