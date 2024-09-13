<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class Thrive_Dash_List_Connection_CampaignMonitor extends Thrive_Dash_List_Connection_Abstract {
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
		return 'Campaign Monitor';
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {
		$related_api = Thrive_Dash_List_Manager::connection_instance( 'campaignmonitoremail' );
		if ( $related_api->is_connected() ) {
			$this->set_param( 'new_connection', 1 );
		}

		$this->output_controls_html( 'campaignmonitor' );
	}

	/**
	 * Just saves the key in the database for optin and email api
	 *
	 * @return string|void
	 */
	public function read_credentials() {
		$key   = ! empty( $_POST['connection']['key'] ) ? sanitize_text_field( $_POST['connection']['key'] ) : '';
		$email = ! empty( $_POST['connection']['email'] ) ? sanitize_email( $_POST['connection']['email'] ) : '';

		if ( empty( $key ) ) {
			return $this->error( __( 'You must provide a valid Campaign Monitor key', 'thrive-dash' ) );
		}
		$this->set_credentials( compact( 'key', 'email' ) );

		$result = $this->test_connection();

		if ( $result !== true ) {
			return $this->error( sprintf( __( 'Could not connect to Campaign Monitor using the provided key (<strong>%s</strong>)', 'thrive-dash' ), $result ) );
		}

		/**
		 * finally, save the connection details
		 */
		$this->save();

		/** @var Thrive_Dash_List_Connection_CampaignMonitorEmail $related_api */
		$related_api = Thrive_Dash_List_Manager::connection_instance( 'campaignmonitoremail' );

		if ( isset( $_POST['connection']['new_connection'] ) && (int) $_POST['connection']['new_connection'] === 1 ) {
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

		return $this->success( __( 'Campaign Monitor connected successfully', 'thrive-dash' ) );
	}

	/**
	 * test if a connection can be made to the service using the stored credentials
	 *
	 * @return bool|string true for success or error message for failure
	 */
	public function test_connection() {

		/** @var Thrive_Dash_Api_CampaignMonitor $cm */
		$cm = $this->get_api();

		try {
			$clients = $cm->get_clients();
			$client  = current( $clients );
			$cm->get_client_lists( $client['id'] );
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
		return new Thrive_Dash_Api_CampaignMonitor( $this->param( 'key' ) );
	}

	/**
	 * get all Subscriber Lists from this API service
	 *
	 * @return array
	 */
	protected function _get_lists() {

		$lists = array();

		try {
			/** @var Thrive_Dash_Api_CampaignMonitor $cm */
			$cm = $this->get_api();

			$clients = $cm->get_clients();
			$client  = current( $clients );

			$lists = $cm->get_client_lists( $client['id'] );
		} catch ( Exception $e ) {

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

		try {
			$subscriber = array();
			/** @var Thrive_Dash_Api_CampaignMonitor $cm */
			$cm = $this->get_api();

			$subscriber['EmailAddress']                           = $arguments['email'];
			$subscriber['Resubscribe']                            = true;
			$subscriber['RestartSubscriptionBasedAutoresponders'] = true;

			if ( ! empty( $arguments['name'] ) ) {
				$subscriber['Name'] = $arguments['name'];
			}

			/** @var Thrive_Dash_Api_CampaignMonitor_List $list */
			$list = $cm->get_list( $list_identifier );

			$subscriber['CustomFields'] = empty( $arguments['CustomFields'] ) ? array() : $arguments['CustomFields'];

			if ( ! empty( $arguments['phone'] ) ) {
				$custom_fields   = $list->get_custom_fields();
				$_list_has_phone = false;
				if ( ! empty( $custom_fields ) ) {
					foreach ( $custom_fields as $field ) {
						if ( isset( $field['name'] ) && $field['name'] === 'Phone' ) {
							$_list_has_phone = true;
							break;
						}
					}
				}

				if ( $_list_has_phone === false ) {
					$custom_field = array(
						'FieldName'                 => 'Phone',
						'DataType'                  => 'Number',
						'Options'                   => array(),
						'VisibleInPreferenceCenter' => true,
					);

					$list->create_custom_field( $custom_field );
				}

				$subscriber['CustomFields'][] = array(
					'Key'   => 'Phone',
					'Value' => (string) $arguments['phone'],
				);
			}

			$_custom_fields = $this->_generate_custom_fields( array_merge( $arguments, array( 'list_id' => $list_identifier ) ) );

			$subscriber['CustomFields'] = array_merge( $subscriber['CustomFields'], $_custom_fields );
			if ( ! empty( $arguments['automator_custom_fields'] ) ) {
				$subscriber['CustomFields'] = array_merge( $subscriber['CustomFields'], $arguments['automator_custom_fields'] );
			}

			$list->add_subscriber( $subscriber );
		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		return true;
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
		$api = $this->get_api();
		if ( ! empty( $arguments['list_identifier'] ) && ! empty( $email ) ) {
			$list = $api->get_list( $arguments['list_identifier'] );
			$list->delete_subscriber( $email );

			return true;
		}

		return false;
	}

	/**
	 * Return the connection email merge tag
	 *
	 * @return String
	 */
	public static function get_email_merge_tag() {
		return '[email]';
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
		$related_api = Thrive_Dash_List_Manager::connection_instance( 'campaignmonitoremail' );
		$related_api->set_credentials( array() );
		Thrive_Dash_List_Manager::save( $related_api );

		return $this;
	}

	/**
	 * @param array $params  which may contain `list_id`
	 * @param bool  $force   make a call to API and invalidate cache
	 * @param bool  $get_all where to get lists with their custom fields
	 *
	 * @return array
	 */
	public function get_api_custom_fields( $params, $force = false, $get_all = true ) {

		$cached_data = $this->get_cached_custom_fields();
		if ( false === $force && ! empty( $cached_data ) ) {
			return $cached_data;
		}

		/** @var Thrive_Dash_Api_CampaignMonitor $cm */
		$cm            = $this->get_api();
		$custom_data   = array();
		$lists         = array();
		$allowed_types = array(
			'Text',
		);

		try {
			$clients = $cm->get_clients();
			$client  = current( $clients );
			$lists   = $cm->get_client_lists( $client['id'] );
		} catch ( Exception $e ) {
		}

		foreach ( $lists as $list ) {
			$custom_data[ $list['id'] ] = array();

			try {
				$custom_fields = $cm->get_list_custom_fields( $list['id'] );

				foreach ( $custom_fields as $item ) {

					if ( isset( $item['DataType'] ) && in_array( $item['DataType'], $allowed_types ) ) {
						$custom_data[ $list['id'] ][] = $this->normalize_custom_field( $item );
					}
				}
			} catch ( Exception $e ) {
			}
		}

		$this->_save_custom_fields( $custom_data );

		return $custom_data;
	}

	/**
	 * @param array $field
	 *
	 * @return array
	 */
	protected function normalize_custom_field( $field = array() ) {

		return array(
			'id'    => ! empty( $field['Key'] ) ? $field['Key'] : '',
			'name'  => ! empty( $field['FieldName'] ) ? $field['FieldName'] : '',
			'type'  => ! empty( $field['DataType'] ) ? $field['DataType'] : '',
			'label' => ! empty( $field['FieldName'] ) ? $field['FieldName'] : '',
		);
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
		foreach ( $automation_data['api_fields'] as $pair ) {
			$mapped_data[] = array(
				'Key'   => $pair['key'],
				'Value' => sanitize_text_field( $pair['value'] ),
			);
		}

		return $mapped_data;
	}


	/**
	 * Generate custom fields array
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	private function _generate_custom_fields( $args ) {
		$custom_fields = $this->get_api_custom_fields( array() );

		if ( empty( $custom_fields[ $args['list_id'] ] ) ) {
			return array();
		}

		$custom_fields = $custom_fields[ $args['list_id'] ];

		$mapped_custom_fields = $this->build_mapped_custom_fields( $args );
		$result               = array();

		foreach ( $mapped_custom_fields as $key => $custom_field ) {

			$field_key = strpos( $custom_field['type'], 'mapping_' ) !== false ? $custom_field['type'] . '_' . $key : $key;

			$field_key = str_replace( '[]', '', $field_key );
			if ( ! empty( $args[ $field_key ] ) ) {
				$args[ $field_key ] = $this->process_field( $args[ $field_key ] );
			}

			$is_in_list = array_filter(
				$custom_fields,
				function ( $field ) use ( $custom_field ) {
					return $custom_field['value'] === $field['id'];
				}
			);

			if ( ! empty( $is_in_list ) && isset( $args[ $field_key ] ) ) {
				$result[] = array(
					'Key'   => $custom_field['value'],
					'Value' => sanitize_text_field( $args[ $field_key ] ),
				);
			}
		}

		return $result;
	}

	/**
	 * Build mapped custom fields array based on form params
	 *
	 * @param $args
	 *
	 * @return array
	 */
	public function build_mapped_custom_fields( $args ) {
		$mapped_data = array();

		// Should be always base_64 encoded of a serialized array
		if ( empty( $args['tve_mapping'] ) || ! tve_dash_is_bas64_encoded( $args['tve_mapping'] ) || ! is_serialized( base64_decode( $args['tve_mapping'] ) ) ) {
			return $mapped_data;
		}

		$form_data = thrive_safe_unserialize( base64_decode( $args['tve_mapping'] ) );

		$mapped_fields = $this->get_mapped_field_ids();

		foreach ( $mapped_fields as $mapped_field_name ) {

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
	 * @return int
	 */
	public function add_custom_fields( $email, $custom_fields = array(), $extra = array() ) {

		try {
			/** @var Thrive_Dash_Api_CampaignMonitor $aweber */
			$api = $this->get_api();

			$list_id = ! empty( $extra['list_identifier'] ) ? $extra['list_identifier'] : null;

			$args = array(
				'email' => $email,
			);
			if ( ! empty( $extra['name'] ) ) {
				$args['name'] = $extra['name'];
			}
			$args['CustomFields'] = $this->prepare_custom_fields_for_api( $custom_fields, $list_id );

			$this->add_subscriber( $list_id, $args );

		} catch ( Exception $e ) {
			return $e->getMessage();
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
	protected function prepare_custom_fields_for_api( $custom_fields = array(), $list_identifier = null ) {

		if ( empty( $list_identifier ) ) { // list identifier required here
			return array();
		}

		$api_fields = $this->get_api_custom_fields( null, true );

		if ( empty( $api_fields[ $list_identifier ] ) ) {
			return array();
		}

		$prepared_fields = array();

		foreach ( $api_fields[ $list_identifier ] as $field ) {
			foreach ( $custom_fields as $key => $custom_field ) {
				if ( $field['id'] === $key ) {

					$prepared_fields[] = array(
						'Key'   => $key,
						'Value' => $custom_field,
					);

					unset( $custom_fields[ $key ] ); // avoid unnecessary loops
				}
			}

			if ( empty( $custom_fields ) ) {
				break;
			}
		}

		return $prepared_fields;
	}

	public function has_custom_fields() {
		return true;
	}

	public function get_automator_add_autoresponder_mapping_fields() {
		return array( 'autoresponder' => array( 'mailing_list' => array( 'api_fields' ) ) );
	}
}
