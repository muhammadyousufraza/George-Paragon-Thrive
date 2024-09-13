<?php /** @noinspection ALL */

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_List_Connection_ActiveCampaign extends Thrive_Dash_List_Connection_Abstract {

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
		return 'ActiveCampaign';
	}

	/**
	 * @return bool
	 */
	public function has_tags() {

		return true;
	}

	public function has_custom_fields() {
		return true;
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {
		$this->output_controls_html( 'activecampaign' );
	}

	/**
	 * should handle: read data from post / get, test connection and save the details
	 *
	 * on error, it should register an error message (and redirect?)
	 *
	 * @return mixed
	 */
	public function read_credentials() {
		$api_url = ! empty( $_POST['connection']['api_url'] ) ? sanitize_text_field( $_POST['connection']['api_url'] ) : '';
		$api_key = ! empty( $_POST['connection']['api_key'] ) ? sanitize_text_field( $_POST['connection']['api_key'] ) : '';

		if ( empty( $api_key ) || empty( $api_url ) || empty( $_POST['connection'] ) ) {
			return $this->error( __( 'Both API URL and API Key fields are required', 'thrive-dash' ) );
		}

		$this->set_credentials( compact( 'api_url', 'api_key' ) );

		$result = $this->test_connection();

		if ( $result !== true ) {
			return $this->error( sprintf( __( 'Could not connect to ActiveCampaign using the provided details. Response was: <strong>%s</strong>', 'thrive-dash' ), $result ) );
		}

		/**
		 * finally, save the connection details
		 */
		$this->save();

		/**
		 * Fetch all custom fields on connect so that we have them all prepared
		 * - TAr doesn't need to get them from API
		 */
		$this->get_api_custom_fields( array(), true, true );

		return $this->success( __( 'ActiveCampaign connected successfully', 'thrive-dash' ) );
	}

	/**
	 * test if a connection can be made to the service using the stored credentials
	 *
	 * @return bool|string true for success or error message for failure
	 */
	public function test_connection() {
		/** @var Thrive_Dash_Api_ActiveCampaign $api */
		$api = $this->get_api();

		try {
			$api->call( 'account_view', array() );

			return true;
		} catch ( Thrive_Dash_Api_ActiveCampaign_Exception $e ) {
			return $e->getMessage();
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
		$api_url = $this->param( 'api_url' );
		$api_key = $this->param( 'api_key' );

		return new Thrive_Dash_Api_ActiveCampaign( $api_url, $api_key );
	}

	/**
	 * get all Subscriber Lists from this API service
	 *
	 * @return array|bool for error
	 */
	protected function _get_lists() {
		try {
			$raw   = $this->get_api()->getLists();
			$lists = array();

			foreach ( $raw as $list ) {
				$lists [] = array(
					'id'   => $list['id'],
					'name' => $list['name'],
				);
			}

			return $lists;

		} catch ( Thrive_Dash_Api_ActiveCampaign_Exception $e ) {

			$this->_error = $e->getMessage();

			return false;

		} catch ( Exception $e ) {

			$this->_error = $e->getMessage();

			return false;
		}

	}

	/**
	 * get all Subscriber Forms from this API service
	 *
	 * @return array|bool for error
	 */
	protected function _get_forms() {
		try {
			$raw   = $this->get_api()->get_forms();
			$forms = array();

			$lists = $this->get_lists();
			foreach ( $lists as $list ) {
				$forms[ $list['id'] ][0] = array(
					'id'   => 0,
					'name' => __( 'none', 'thrive-dash' ),
				);
			}

			foreach ( $raw as $form ) {
				foreach ( $form['lists'] as $list_id ) {
					if ( empty( $forms[ $list_id ] ) ) {
						$forms[ $list_id ] = array();
					}
					/**
					 * for some reason, I've seen an instance where forms were duplicated (2 or more of the same form were displayed in the list)
					 */
					$forms[ $list_id ][ $form['id'] ] = array(
						'id'   => $form['id'],
						'name' => $form['name'],
					);
				}
			}

			return $forms;

		} catch ( Thrive_Dash_Api_ActiveCampaign_Exception $e ) {

			$this->_error = $e->getMessage();

			return false;

		} catch ( Exception $e ) {

			$this->_error = $e->getMessage();

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
		$api     = $this->get_api();
		$contact = $api->call( 'contact_view_email', array( 'email' => $email ) );

		if ( isset( $contact['result_code'] ) && $contact['result_code'] == 1 ) {

			$body   = array( 'id' => $contact['id'] );
			$result = $api->call( 'contact_delete', $body, array() );

			return isset( $result['result_code'] ) && $result['result_code'] == 1;
		}

		return true;

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

		/** @var Thrive_Dash_Api_ActiveCampaign $api */
		$api        = $this->get_api();
		$name_array = array();
		if ( ! empty( $arguments['name'] ) ) {
			list( $first_name, $last_name ) = $this->get_name_parts( $arguments['name'] );
			$name_array = array(
				'firstname' => $first_name,
				'lastName'  => $last_name,
			);
		}


		// Get contact
		try {
			$contact = $api->call( 'contact_view_email', array( 'email' => $arguments['email'] ) );
		} catch ( Thrive_Dash_Api_ActiveCampaign_Exception $e ) {
			return $e->getMessage();
		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		$update = false;
		if ( isset( $contact['result_code'] ) && $contact['result_code'] == 1 ) {
			foreach ( $contact['lists'] as $list ) {
				if ( $list['listid'] == $list_identifier ) {
					$update = true;
				}
			}
		}

		// Prepared args for passing to subscribe/update methods
		$prepared_args = array(
			'email'            => ! empty( $arguments['email'] ) ? sanitize_email( $arguments['email'] ) : '',
			'phone'            => empty( $arguments['phone'] ) ? '' : sanitize_text_field( $arguments['phone'] ),
			'form_id'          => empty( $arguments['activecampaign_form'] ) ? 0 : sanitize_text_field( $arguments['activecampaign_form'] ),
			'organizationName' => '',
			'tags'             => ! empty( $arguments['activecampaign_tags'] ) ? trim( $arguments['activecampaign_tags'], ',' ) : '',
			'ip'               => null,
		);
		$prepared_args = array_merge( $prepared_args, $name_array );
		// Add or update subscriber
		try {

			/**
			 * Try to add/update contact on a single api call so linkted automation will be properly triggered
			 */
			if ( ! empty( $arguments['tve_mapping'] ) ) {
				$prepared_args['custom_fields'] = $this->buildMappedCustomFields( $arguments );
			} else if ( ! empty( $arguments['automator_custom_fields'] ) ) {
				$prepared_args['custom_fields'] = $arguments['automator_custom_fields'];
			}

			if ( isset( $contact['result_code'] ) && ( empty( $contact['result_code'] ) || false === $update ) ) {
				$api->add_subscriber( $list_identifier, $prepared_args );
			} else {
				$prepared_args['contact'] = $contact;
				$api->updateSubscriber( $list_identifier, $prepared_args );
			}

			$return = true;
		} catch ( Thrive_Dash_Api_ActiveCampaign_Exception $e ) {
			$return = $e->getMessage();
		} catch ( Exception $e ) {
			$return = $e->getMessage();
		}

		/**
		 * Add/update action failed so we try again by doing two separate requests
		 * one for add/update contact and another one for updating custom fields
		 */
		if ( true !== $return ) {
			try {

				if ( isset( $contact['result_code'] ) && ( empty( $contact['result_code'] ) || false === $update ) ) {
					$api->add_subscriber( $list_identifier, $prepared_args );
				} else {
					$prepared_args['contact'] = $contact;
					$api->updateSubscriber( $list_identifier, $prepared_args );
				}

				$return = true;
			} catch ( Thrive_Dash_Api_ActiveCampaign_Exception $e ) {
				$return = $e->getMessage();
			} catch ( Exception $e ) {
				$return = $e->getMessage();
			}

			// Update custom fields
			// Make another call to update custom mapped fields in order not to break the subscription call,
			// if custom data doesn't pass API custom fields validation
			if ( true === $return && ! empty( $arguments['tve_mapping'] ) ) {
				unset( $prepared_args['tags'] );
				$this->updateCustomFields( $list_identifier, $arguments, $prepared_args );
			}
		}

		return $return;
	}

	/**
	 * Update custom fields
	 *
	 * @param string|int $list_identifier
	 * @param array      $arguments     form data
	 * @param array      $prepared_args prepared array for subscription
	 *
	 * @return bool|string
	 */
	public function updateCustomFields( $list_identifier, $arguments, $prepared_args ) {

		if ( ! $list_identifier || empty( $arguments ) || empty( $prepared_args ) ) {
			return false;
		}

		/** @var Thrive_Dash_Api_ActiveCampaign $api */
		$api = $this->get_api();
		try {

			// Refresh the contact data for mapping custom fields
			$prepared_args['contact'] = $api->call( 'contact_view_email', array( 'email' => $arguments['email'] ) );

			// Build mapped fields array
			$prepared_args['custom_fields'] = $this->buildMappedCustomFields( $arguments );

			$api->updateSubscriber( $list_identifier, $prepared_args );

			$return = true;
		} catch ( Exception $e ) {
			// Log api errors
			$this->api_log_error( $list_identifier, $prepared_args, __METHOD__ . ': ' . $e->getMessage() );
			$return = $e->getMessage();
		}

		return $return;
	}

	/**
	 * output any (possible) extra editor settings for this API
	 *
	 * @param array $params allow various different calls to this method
	 *
	 * @return array
	 */
	public function get_extra_settings( $params = array() ) {
		$params['forms'] = $this->_get_forms();
		if ( ! is_array( $params['forms'] ) ) {
			$params['forms'] = array();
		}

		return $params;
	}

	/**
	 * output any (possible) extra editor settings for this API
	 *
	 * @param array $params allow various different calls to this method
	 */
	public function render_extra_editor_settings( $params = array() ) {
		$params['forms'] = $this->_get_forms();
		if ( ! is_array( $params['forms'] ) ) {
			$params['forms'] = array();
		}
		$this->output_controls_html( 'activecampaign/forms-list', $params );
	}

	/**
	 * Return the connection email merge tag
	 *
	 * @return String
	 */
	public static function get_email_merge_tag() {
		return '%EMAIL%';
	}

	/**
	 * @param      $params
	 * @param bool $force
	 * @param bool $get_all
	 *
	 * @return array|mixed
	 */
	public function get_api_custom_fields( $params, $force = false, $get_all = false ) {

		return $this->get_all_custom_fields( $force );
	}

	/**
	 * @param (bool) $force
	 *
	 * @return array|mixed
	 */
	public function get_all_custom_fields( $force ) {

		$custom_data = array();

		// Serve from cache if exists and requested
		$cached_data = $this->get_cached_custom_fields();
		if ( false === $force && ! empty( $cached_data ) ) {
			return $cached_data;
		}

		// Needed custom fields type
		$allowed_types = array(
			'text',
			'url',
			'hidden',
		);

		// Build custom fields for every list
		$custom_fields = $this->get_api()->getCustomFields();

		if ( is_array( $custom_fields ) ) {
			foreach ( $custom_fields as $field ) {
				if ( ! empty( $field['type'] ) && in_array( $field['type'], $allowed_types, true ) && 1 === (int) $field['visible'] ) {
					$custom_data[] = $this->normalize_custom_field( $field );
				}
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
	protected function normalize_custom_field( $field ) {

		$field = (array) $field;

		return array(
			'id'    => ! empty( $field['id'] ) ? $field['id'] : '',
			'name'  => ! empty( $field['perstag'] ) ? $field['perstag'] : '',
			'type'  => $field['type'],
			'label' => ! empty( $field['title'] ) ? $field['title'] : '',
		);
	}

	/**
	 * Append custom fields to defaults
	 *
	 * @param array $params
	 *
	 * @return array
	 */
	public function get_custom_fields( $params = array() ) {
		return array_merge( parent::get_custom_fields(), $this->_mapped_custom_fields );
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

		if ( is_array( $form_data ) ) {

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

						$mapped_api_id = $form_data[ $cf_form_name ][ $this->_key ];

						$cf_form_name = str_replace( '[]', '', $cf_form_name );
						if ( ! empty( $args[ $cf_form_name ] ) ) {
							$args[ $cf_form_name ]                     = $this->process_field( $args[ $cf_form_name ] );
							$mapped_data["field[{$mapped_api_id}, 0]"] = sanitize_text_field( $args[ $cf_form_name ] );
						}
					}
				}
			}
		}

		return $mapped_data;
	}

	/**
	 * Build custom fields mapping for automations
	 *
	 * @param $automation_data
	 *
	 * @return array
	 */
	public function build_automation_custom_fields( $automation_data ) {
		$mapped_data = array();
		foreach ( $automation_data['api_fields'] as $pair ) {
			$value = sanitize_text_field( $pair['value'] );
			if ( $value ) {
				$mapped_data["field[{$pair['key']}, 0]"] = $value;
			}
		}

		return $mapped_data;
	}


	/**
	 * get relevant data from webhook trigger
	 *
	 * @param $request WP_REST_Request
	 *
	 * @return array
	 */
	public function get_webhook_data( $request ) {
		$contact = $request->get_param( 'contact' );

		return array( 'email' => empty( $contact['email'] ) ? '' : $contact['email'] );
	}

	/**
	 * @param       $email
	 * @param array $custom_fields
	 * @param array $extra
	 *
	 * @return false|int|mixed
	 */
	public function add_custom_fields( $email, $custom_fields = array(), $extra = array() ) {

		try {
			/** @var Thrive_Dash_Api_ActiveCampaign $api */
			$api     = $this->get_api();
			$list_id = ! empty( $extra['list_identifier'] ) ? $extra['list_identifier'] : null;
			$args    = array(
				'email' => $email,
			);

			if ( ! empty( $extra['name'] ) ) {
				$args['name'] = $extra['name'];
			}

			$this->add_subscriber( $list_id, $args );

			$args['contact']       = $api->call( 'contact_view_email', array( 'email' => $email ) );
			$args['custom_fields'] = $this->prepare_custom_fields_for_api( $custom_fields );

			$api->updateSubscriber( $list_id, $args );

			return $args['contact']['id'];

		} catch ( Exception $e ) {
			return false;
		}
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

		$prepared_fields = array();

		foreach ( $custom_fields as $key => $custom_field ) {
			if ( $custom_field ) {
				$prepared_fields["field[{$key}], 0"] = sanitize_text_field( $custom_field );
			}
		}

		return $prepared_fields;
	}


	public function get_automator_add_autoresponder_mapping_fields() {
		return array( 'autoresponder' => array( 'mailing_list' => array( 'form_list' ), 'api_fields' => array(), 'tag_input' => array() ) );
	}

	public function get_custom_fields_by_list( $list = null ) {
		return $this->get_available_custom_fields();
	}

	public function has_forms() {
		return true;
	}
}
