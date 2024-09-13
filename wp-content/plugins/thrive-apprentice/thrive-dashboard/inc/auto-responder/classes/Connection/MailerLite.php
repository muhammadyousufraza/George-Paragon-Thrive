<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_List_Connection_MailerLite extends Thrive_Dash_List_Connection_Abstract {
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
		return 'MailerLite';
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {
		$this->output_controls_html( 'mailerlite' );
	}

	/**
	 * just save the key in the database
	 *
	 * @return mixed|void
	 */
	public function read_credentials() {
		$connection = $this->post( 'connection' );
		$key        = ! empty( $connection['key'] ) ? ( $connection['key'] ) : '';

		if ( empty( $key ) ) {
			return $this->error( __( 'You must provide a valid MailerLite key', 'thrive-dash' ) );
		}

		$this->set_credentials( $connection );

		$result = $this->test_connection();

		if ( $result !== true ) {
			return $this->error( sprintf( __( 'Could not connect to MailerLite using the provided key (<strong>%s</strong>)', 'thrive-dash' ), $result ) );
		}

		/**
		 * finally, save the connection details
		 */
		$this->save();

		return $this->success( __( 'MailerLite connected successfully', 'thrive-dash' ) );
	}

	/**
	 * test if a connection can be made to the service using the stored credentials
	 *
	 * @return bool|string true for success or error message for failure
	 */
	public function test_connection() {
		$mailer = $this->get_api();
		/**
		 * just try getting a list as a connection test
		 */

		try {
			$groupsApi = $mailer->groups();
			$groupsApi->get();
		} catch ( Thrive_Dash_Api_MailerLite_MailerLiteSdkException $e ) {
			return $e->getMessage();
		}

		return true;
	}

	/**
	 * instantiate the API code required for this connection
	 *
	 * @return mixed
	 * @throws Thrive_Dash_Api_MailerLite_MailerLiteSdkException
	 */
	protected function get_api_instance() {
		$key     = $this->param( 'key' );
		$version = $this->param( 'version' );

		if ( ! empty( $version ) && (int) $version === 2 ) {
			return new Thrive_Dash_Api_MailerLiteV2( $key );
		}

		return new Thrive_Dash_Api_MailerLite( $key );
	}

	/**
	 * get all Subscriber Lists from this API service
	 *
	 * @return array
	 */
	protected function _get_lists() {
		$api = $this->get_api();

		try {
			$groups_api = $api->groups();
			$groups_api->limit( 10000 );
			$lists_obj = $groups_api->get();

			$lists = array();
			foreach ( $lists_obj as $item ) {
				$lists [] = array(
					'id'   => $item->id,
					'name' => $item->name,
				);
			}

			return $lists;
		} catch ( Exception $e ) {
			$this->_error = $e->getMessage() . ' ' . __( 'Please re-check your API connection details.', 'thrive-dash' );

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
		$api     = $this->get_api();
		$version = (int) $this->param( 'version' );

		if ( isset( $arguments['name'] ) ) {
			list( $first_name, $last_name ) = $this->get_name_parts( $arguments['name'] );
		}

		if ( isset( $arguments['phone'] ) ) {
			$phone = $arguments['phone'];
		}

		$args['fields'] = array();
		$args['email']  = $arguments['email'];

		if ( ! empty( $first_name ) ) {
			$args['fields']['name'] = $first_name;
			$args['name']           = $first_name;
		}

		if ( ! empty( $last_name ) ) {
			$args['fields']['last_name'] = $last_name;
		}

		if ( ! empty( $phone ) ) {
			$args['fields']['phone'] = $phone;
		}

		$args['resubscribe'] = 1;

		try {
			$groupsApi = $api->groups();
			if ( empty( $arguments['automator_custom_fields'] ) ) {
				$args['fields'] = array_merge( $args['fields'], $this->_generateCustomFields( $arguments ) );
			} else {
				$args['fields'] = array_merge( $args['fields'], $arguments['automator_custom_fields'] );
			}


			$groupsApi->add_subscriber( $list_identifier, $args );

			return true;
		} catch ( Thrive_Dash_Api_MailerLite_MailerLiteSdkException $e ) {
			return $e->getMessage() ? $e->getMessage() : __( 'Unknown MailerLite Error', 'thrive-dash' );
		} catch ( Exception $e ) {
			return $e->getMessage() ? $e->getMessage() : __( 'Unknown Error', 'thrive-dash' );
		}

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
	 * @param      $params
	 * @param bool $force
	 * @param bool $get_all
	 *
	 * @return array|mixed
	 */
	public function get_api_custom_fields( $params, $force = false, $get_all = false ) {
		// Serve from cache if exists and requested
		$cached_data = $this->get_cached_custom_fields();
		if ( false === $force && ! empty( $cached_data ) ) {
			return $cached_data;
		}

		$custom_data   = array();
		$allowed_types = $this->get_api()->fields()->get_allowed_types();

		try {
			$custom_fields = $this->get_api()->fields()->get();

			if ( is_array( $custom_fields ) ) {
				foreach ( $custom_fields as $field ) {
					if ( ! empty( $field->type ) && in_array( $field->type, $allowed_types, true ) ) {
						$custom_data[] = $this->get_api()->fields()->get_normalize_custom_field( $field );
					}
				}
			}
		} catch ( Exception $e ) {
		}

		$this->_save_custom_fields( $custom_data );

		return $custom_data;
	}

	/**
	 * Generate custom fields array
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	private function _generateCustomFields( $args ) {
		$custom_fields = $this->get_api_custom_fields( array() );
		$ids           = $this->buildMappedCustomFields( $args );
		$result        = array();

		foreach ( $ids as $key => $id ) {
			$field = array_filter(
				$custom_fields,
				function ( $item ) use ( $id ) {
					return (int) $item['id'] === (int) $id['value'];
				}
			);

			$field = array_values( $field );

			if ( ! isset( $field[0] ) ) {
				continue;
			}
			$_name        = $field[0]['key'] ?: $field[0]['name'];
			$chunks       = explode( ' ', $_name );
			$chunks       = array_map( 'strtolower', $chunks );
			$field_key    = implode( '_', $chunks );
			$name         = strpos( $id['type'], 'mapping_' ) !== false ? $id['type'] . '_' . $key : $key;
			$cf_form_name = str_replace( '[]', '', $name );
			$value        = isset( $args[ $cf_form_name ] ) ? $this->process_field( $args[ $cf_form_name ] ) : '';
			if ( $value ) {
				$result[ $field_key ] = $value;
			}
		}

		return $result;
	}

	/**
	 * Build custom fields mapping for automations
	 *
	 * @param $automation_data
	 *
	 * @return array
	 */
	public function build_automation_custom_fields( $automation_data ) {
		$mapped_data = [];
		$fields      = $this->get_api_custom_fields( array() );
		foreach ( $automation_data['api_fields'] as $pair ) {
			$value = sanitize_text_field( $pair['value'] );
			if ( $value ) {
				foreach ( $fields as $field ) {
					if ( (int) $field['id'] === (int) $pair['key'] ) {
						$_name                 = $field['key'] ?: $field['name'];
						$_name                 = explode( ' ', $_name );
						$_name                 = array_map( 'strtolower', $_name );
						$_name                 = implode( '_', $_name );
						$mapped_data[ $_name ] = $value;
					}
				}
			}
		}

		return $mapped_data;
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

			if ( ! empty( $cf_form_fields ) && is_array( $cf_form_fields ) ) {

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
	 * @param array $custom_fields
	 * @param array $extra
	 *
	 * @return false|int
	 */
	public function add_custom_fields( $email, $custom_fields = array(), $extra = array() ) {

		try {
			$api = $this->get_api();

			$groupsApi = $api->groups();

			$subscribersApi = $api->subscribers();

			$list_id = ! empty( $extra['list_identifier'] ) ? $extra['list_identifier'] : null;
			$args    = array(
				'email'  => $email,
				'name'   => ! empty( $extra['name'] ) ? $extra['name'] : '',
				'fields' => array(),
			);

			$this->add_subscriber( $list_id, $args );

			$args['fields'] = $this->prepare_custom_fields_for_api( $custom_fields );

			$groupsApi->add_subscriber( $list_id, $args );

			$subscriber = $subscribersApi->search( $email );

			return ! empty( $subscriber[0] ) ? $subscriber[0]->id : 0;

		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Get available custom fields for this api connection
	 *
	 * @param null $list_id
	 *
	 * @return array
	 */
	public function get_available_custom_fields( $list_id = null ) {

		return $this->get_api_custom_fields( null, true );
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
		$api_fields      = $this->get_api_custom_fields( array( 'list_id' => $list_identifier ), true );

		foreach ( $api_fields as $field ) {
			foreach ( $custom_fields as $key => $custom_field ) {
				if ( (int) $field['id'] === (int) $key && $custom_field ) {

					$_name  = $field['key'] ?: $field['name'];
					$chunks = explode( ' ', $_name );
					$chunks = array_map( 'strtolower', $chunks );
					$cf_key = implode( '_', $chunks );

					$prepared_fields[ $cf_key ] = $custom_field;
				}
			}

			if ( empty( $custom_fields ) ) {
				break;
			}
		}

		return $prepared_fields;
	}


	public function get_automator_add_autoresponder_mapping_fields() {
		return array( 'autoresponder' => array( 'mailing_list', 'api_fields' ) );
	}

	public function has_custom_fields() {
		return true;
	}
}

