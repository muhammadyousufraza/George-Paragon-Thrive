<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_List_Connection_ConvertKit extends Thrive_Dash_List_Connection_Abstract {
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
		return 'ConvertKit / Seva';
	}

	/**
	 * @return bool
	 */
	public function has_tags() {

		return true;
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {
		$this->output_controls_html( 'convertkit' );
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
			return $this->error( __( 'You must provide a valid ConvertKit API Key', 'thrive-dash' ) );
		}

		$this->set_credentials( array( 'key' => $key ) );

		$result = $this->test_connection();

		if ( $result !== true ) {
			return $this->error( sprintf( __( 'Could not connect to ConvertKit: %s', 'thrive-dash' ), $this->_error ) );
		}

		/**
		 * finally, save the connection details
		 */
		$this->save();

		return $this->success( __( 'ConvertKit connected successfully', 'thrive-dash' ) );
	}

	/**
	 * test if a connection can be made to the service using the stored credentials
	 *
	 * @return bool|string true for success or error message for failure
	 */
	public function test_connection() {
		return is_array( $this->_get_lists() );
	}

	/**
	 * instantiate the API code required for this connection
	 *
	 * @return Thrive_Dash_Api_ConvertKit
	 */
	protected function get_api_instance() {
		return new Thrive_Dash_Api_ConvertKit( $this->param( 'key' ) );
	}

	/**
	 * get all Subscriber Lists from this API service
	 *
	 * ConvertKit has both sequences and forms
	 *
	 * @return array|string for error
	 */
	protected function _get_lists() {
		/**
		 * just try getting the lists as a connection test
		 */
		try {

			/** @var $api Thrive_Dash_Api_ConvertKit */
			$api = $this->get_api();

			$lists = array();

			$data = $api->get_forms();
			if ( ! empty( $data ) ) {
				foreach ( $data as $form ) {
					if ( ! empty( $form['archived'] ) ) {
						continue;
					}
					$lists[] = array(
						'id'   => $form['id'],
						'name' => $form['name'],
					);
				}
			}

			return $lists;

		} catch ( Thrive_Dash_Api_ConvertKit_Exception $e ) {
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
			/** @var $api Thrive_Dash_Api_ConvertKit */
			$api = $this->get_api();

			$arguments['custom_fields_ids'] = $this->buildMappedCustomFields( $arguments );
			$arguments['fields'] = new stdClass();

			if ( ! empty( $arguments['custom_fields_ids'] ) ) {
				$arguments['fields'] = $this->_generateCustomFields( $arguments );
			} else if ( ! empty( $arguments['automator_custom_fields'] ) ) {
				$arguments['fields'] = $arguments['automator_custom_fields'];
				unset( $arguments['automator_custom_fields'] );
			}

			$api->subscribeForm( $list_identifier, $arguments );

		} catch ( Exception $e ) {

			return $e->getMessage();
		}

		return true;
	}

	/**
	 * Get custom fields
	 *
	 * @return array
	 */
	public function getCustomFields() {
		/**  @var Thrive_Dash_Api_ConvertKit $api */
		$api    = $this->get_api();
		$fields = $api->getCustomFields();

		return isset( $fields['custom_fields'] ) ? $fields['custom_fields'] : array();
	}

	/**
	 * @param array $args
	 *
	 * @return object
	 * @throws Thrive_Dash_Api_ConvertKit_Exception
	 */
	protected function _generateCustomFields( $args ) {
		/**  @var Thrive_Dash_Api_ConvertKit $api */
		$api      = $this->get_api();
		$fields   = $this->_getCustomFields( false );
		$response = array();
		$ids      = $this->buildMappedCustomFields( $args );

		foreach ( $fields as $field ) {
			foreach ( $ids as $key => $id ) {
				if ( (int) $field['id'] === (int) $id['value'] ) {

					/**
					 * Ex cf: ck_field_84479_first_custom_field
					 * Needed Result: first_custom_field
					 */
					$_name = $field['name'];
					$_name = str_replace( 'ck_field_', '', $_name );
					$_name = explode( '_', $_name );

					unset( $_name[0] );

					$_name        = implode( '_', $_name );
					$name         = strpos( $id['type'], 'mapping_' ) !== false ? $id['type'] . '_' . $key : $key;
					$cf_form_name = str_replace( '[]', '', $name );
					if ( ! empty( $args[ $cf_form_name ] ) ) {
						$response[ $_name ] = $this->process_field( $args[ $cf_form_name ] );
					}
				}
			}
		}

		if ( ! empty( $args['phone'] ) ) {
			$phone_fields      = $api->phoneFields( $args['phone'] );
			$response['phone'] = isset( $phone_fields['phone'] ) ? $phone_fields['phone'] : '';
		}

		return (object) $response;
	}

	/**
	 * Build custom fields mapping for automations
	 *
	 * @param $automation_data
	 *
	 * @return object
	 */
	public function build_automation_custom_fields( $automation_data ) {
		$mapped_data = [];
		$fields      = $this->_getCustomFields( false );
		foreach ( $automation_data['api_fields'] as $pair ) {
			$value = sanitize_text_field( $pair['value'] );
			if ( $value ) {
				foreach ( $fields as $field ) {
					if ( (int) $field['id'] === (int) $pair['key'] ) {
						/**
						 * Ex cf: ck_field_84479_first_custom_field
						 * Needed Result: first_custom_field
						 */
						$_name = $field['name'];
						$_name = str_replace( 'ck_field_', '', $_name );
						$_name = explode( '_', $_name );
						unset( $_name[0] );
						$_name                 = implode( '_', $_name );
						$mapped_data[ $_name ] = $value;
					}
				}

			}
		}

		return (object) $mapped_data;
	}


	/**
	 * Return the connection email merge tag
	 *
	 * @return String
	 */
	public static function get_email_merge_tag() {
		return '{{subscriber.email_address}}';
	}

	/**
	 * @param $force
	 *
	 * @return array
	 */
	protected function _getCustomFields( $force ) {

		// Serve from cache if exists and requested
		$cached_data = $this->get_cached_custom_fields();
		if ( false === $force && ! empty( $cached_data ) ) {
			return $cached_data;
		}

		/** @var $api Thrive_Dash_Api_ConvertKit */
		$api = $this->get_api();

		$fields = $api->getCustomFields();
		$fields = isset( $fields['custom_fields'] ) ? $fields['custom_fields'] : array();

		foreach ( $fields as $key => $field ) {
			$fields[ $key ] = $this->normalize_custom_field( $field );
		}

		$this->_save_custom_fields( $fields );

		return $fields;
	}

	/**
	 * @param      $params
	 * @param bool $force
	 * @param bool $get_all
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_ConvertKit_Exception
	 */
	public function get_api_custom_fields( $params, $force = false, $get_all = false ) {

		$response = $this->_getCustomFields( $force );

		return is_array( $response ) ? $response : array();
	}

	protected function normalize_custom_field( $data ) {
		$data['type'] = 'text';

		return parent::normalize_custom_field( $data );
	}

	/**
	 * Build mapped custom fields array based on form params
	 *
	 * @param $args
	 *
	 * @return array
	 */
	public function buildMappedCustomFields( $args ) {
		$mapped_data = array();

		// Should be always base_64 encoded of a serialized array
		if ( empty( $args['tve_mapping'] ) || ! tve_dash_is_bas64_encoded( $args['tve_mapping'] ) || ! is_serialized( base64_decode( $args['tve_mapping'] ) ) ) {
			return $mapped_data;
		}

		$form_data = thrive_safe_unserialize( base64_decode( $args['tve_mapping'] ) );

		$mapped_fields = $this->get_mapped_field_ids();

		foreach ( $mapped_fields as $mapped_field_name ) {

			// Extract an array with all custom fields (siblings) names from form data
			// {ex: [mapping_url_0, .. mapping_url_n] / [mapping_text_0, .. mapping_text_n]}
			$cf_form_fields = preg_grep( "#^{$mapped_field_name}#i", array_keys( $form_data ) );

			// Matched "form data" for current allowed name
			if ( ! empty( $cf_form_fields ) && is_array( $cf_form_fields ) ) {

				// Pull form allowed data, sanitize it and build the custom fields array
				foreach ( $cf_form_fields as $cf_form_name ) {
					if ( empty( $form_data[ $cf_form_name ][ $this->_key ] ) ) {
						continue;
					}

					$field_id = str_replace( $mapped_field_name . '_', '', $cf_form_name );

					$mapped_data[ $field_id ] = array(
						'type'  => $mapped_field_name,
						'value' => $form_data[ $cf_form_name ][ $this->_key ],
					);
				}
			}
		}


		return $mapped_data;
	}

	/**
	 * @param       $email
	 * @param array $custom_fields ex array( 'cf_name' => 'some nice cf value' )
	 * @param array $extra
	 *
	 * @return false|int|mixed
	 */
	public function add_custom_fields( $email, $custom_fields = array(), $extra = array() ) {

		if ( empty( $extra['list_identifier'] ) ) {
			return false;
		}

		try {
			/** @var $api Thrive_Dash_Api_ConvertKit */
			$api  = $this->get_api();
			$args = array(
				'fields' => (object) $this->prepare_custom_fields_for_api( $custom_fields ),
				'email'  => $email,
				'name'   => ! empty( $extra['name'] ) ? $extra['name'] : '',
			);

			$subscriber = $api->subscribeForm( $extra['list_identifier'], $args );

			return $subscriber['subscriber']['id'];

		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * delete a contact from the list
	 *
	 * @param string $email
	 * @param array  $arguments
	 *
	 * @return mixed
	 */
	public function delete_subscriber( $email, $arguments = array() ) {
		$api    = $this->get_api();
		$result = $api->unsubscribeUser( $email, $arguments );

		return isset( $result['subscriber']['id'] );

	}

	/**
	 * Get available custom fields for this api connection
	 *
	 * @param null $list_id
	 *
	 * @return array
	 */
	public function get_available_custom_fields( $list_id = null ) {

		return $this->_getCustomFields( true );
	}

	/**
	 * Prepare custom fields for api call
	 *
	 * @param array $custom_fields
	 * @param null  $list_identifier
	 *
	 * @return array
	 */
	public function prepare_custom_fields_for_api( $custom_fields = array(), $list_identifier = null ) {

		$prepared_fields = array();
		$cf_prefix       = 'ck_field_';
		$api_fields      = $this->get_api_custom_fields( null, true );

		foreach ( $api_fields as $field ) {
			foreach ( $custom_fields as $key => $custom_field ) {
				if ( (int) $field['id'] === (int) $key && $custom_field ) {
					$str_to_replace = $cf_prefix . $field['id'] . '_';
					$cf_key         = str_replace( $str_to_replace, '', $field['name'] );

					$prepared_fields[ $cf_key ] = $custom_field;

					unset( $custom_fields[ $key ] ); // avoid unnecessary loops
				}
			}

			if ( empty( $custom_fields ) ) {
				break;
			}
		}

		return $prepared_fields;
	}

	public function get_automator_add_autoresponder_mapping_fields() {
		return array( 'autoresponder' => array( 'mailing_list' => array( 'api_fields' ), 'tag_input' => array() ) );
	}

	public function has_custom_fields() {
		return true;
	}
}
