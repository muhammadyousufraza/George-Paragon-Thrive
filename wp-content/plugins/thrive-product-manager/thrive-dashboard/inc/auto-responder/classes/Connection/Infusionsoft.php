<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_List_Connection_Infusionsoft extends Thrive_Dash_List_Connection_Abstract {

	/**
	 * @var array Allowed custom fields
	 */
	protected $_custom_fields = array();

	/**
	 * Thrive_Dash_List_Connection_Infusionsoft constructor.
	 *
	 * @param $key
	 */
	public function __construct( $key ) {

		parent::__construct( $key );

		// DataType ID for text and website
		$this->_custom_fields = array(
			15 => 'text',
			18 => 'url',
		);
	}

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
		return 'Keap (Infusionsoft)';
	}

	public function get_list_sub_title() {
		return __( 'Choose your Tag Name List', 'thrive-dash' );
	}

	/**
	 * @return bool
	 */
	public function has_tags() {

		return true;
	}

	/**
	 * @param array|string $tags
	 * @param array        $data
	 *
	 * @return array
	 */
	public function push_tags( $tags, $data = array() ) {

		$data['tqb_tags'] = implode( ', ', $tags );

		return $data;
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {
		$this->output_controls_html( 'infusionsoft' );
	}

	/**
	 * just save the key in the database
	 *
	 * @return mixed
	 */
	public function read_credentials() {
		$client_id = ! empty( $_POST['connection']['client_id'] ) ? sanitize_text_field( $_POST['connection']['client_id'] ) : '';
		$key       = ! empty( $_POST['connection']['api_key'] ) ? sanitize_text_field( $_POST['connection']['api_key'] ) : '';

		if ( empty( $key ) || empty( $client_id ) ) {
			return $this->error( __( 'Client ID and API key are required', 'thrive-dash' ) );
		}

		$this->set_credentials( array( 'client_id' => $client_id, 'api_key' => $key ) );

		$result = $this->test_connection();

		if ( true !== $result ) {
			/* translators: %s: error message */
			$error = __( 'Could not connect to Keap (Infusionsoft) using the provided credentials (<strong>%s</strong>)', 'thrive-dash' );

			return $this->error( sprintf( $error, $result ) );
		}

		/**
		 * finally, save the connection details
		 */
		$this->save();

		return $this->success( 'Keap (Infusionsoft) connected successfully' );
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
		$result = $this->_get_lists();

		if ( is_array( $result ) ) {
			return true;
		}

		/* At this point, $result will be a string */

		return $result;
	}

	/**
	 * instantiate the API code required for this connection
	 *
	 * @return mixed|Thrive_Dash_Api_Infusionsoft
	 * @throws Thrive_Dash_Api_Infusionsoft_InfusionsoftException
	 */
	protected function get_api_instance() {
		return new Thrive_Dash_Api_Infusionsoft( $this->param( 'client_id' ), $this->param( 'api_key' ) );
	}

	/**
	 * get all Subscriber Lists from this API service
	 *
	 * @return array
	 */
	protected function _get_lists() {
		try {
			/** @var Thrive_Dash_Api_Infusionsoft $api */
			$api = $this->get_api();

			$query_data      = array(
				'GroupName' => '%',
			);
			$selected_fields = array( 'Id', 'GroupName' );
			$response        = $api->data( 'query', 'ContactGroup', 1000, 0, $query_data, $selected_fields );

			if ( empty( $response ) ) {
				return array();
			}

			$tags = $response;

			/**
			 * Infusionsoft has a limit of 1000 results to fetch, we should get all tags if the user has more
			 */
			$i = 1;
			while ( count( $response ) === 1000 ) {
				$response = $api->data( 'query', 'ContactGroup', 1000, $i, $query_data, $selected_fields );
				$tags     = array_merge( $tags, $response );
				$i ++;
			}

			$lists = array();

			foreach ( $tags as $item ) {
				$lists[] = array(
					'id'   => $item['Id'],
					'name' => $item['GroupName'],
				);
			}

			return $lists;

		} catch ( Exception $e ) {
			return $e->getMessage();
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
		$api = $this->get_api();


		if ( ! empty( $email ) && ! empty( $arguments['list_identifier'] ) ) {

			$contact = $this->get_contact( $arguments['list_identifier'], $email );
			if ( ! empty( $contact ) ) {
				$user_hash = md5( $email );
				$api->request( 'lists/' . $arguments['list_identifier'] . '/members/' . $user_hash, array(), 'DELETE' );
			}
		}

		return true;
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
			/** @var Thrive_Dash_Api_Infusionsoft $api */
			$api        = $this->get_api();
			$name_array = array();
			if ( ! empty( $arguments['name'] ) ) {
				list( $first_name, $last_name ) = $this->get_name_parts( $arguments['name'] );
				$name_array = array(
					'FirstName' => $first_name,
					'LastName'  => $last_name,
				);
			}

			$phone_array = array();
			if ( ! empty( $arguments['name'] ) ) {
				$phone_array = array(
					'Phone1' => $arguments['phone'],
				);
			}
			$data       = array(
				'Email' => $arguments['email'],
			);
			$data       = array_merge( $data, $name_array, $phone_array );
			$contact_id = $api->contact( 'addWithDupCheck', $data, 'Email' );

			if ( $contact_id ) {
				$api->APIEmail( 'optIn', $data['Email'], 'thrive opt in' );

				$today          = date( 'Ymj\TG:i:s' );
				$creation_notes = 'A web form was submitted with the following information:';
				$ip_address     = tve_dash_get_ip();

				if ( ! empty( $arguments['url'] ) ) {
					$creation_notes .= "\nReferring URL: " . $arguments['url'];
				}

				$creation_notes .= "\nIP Address: " . $ip_address;
				$creation_notes .= "\ninf_field_Email: " . $arguments['email'];

				if ( ! empty( $first_name ) ) {
					$creation_notes .= "\ninf_field_LastName: " . $last_name;
					$creation_notes .= "\ninf_field_FirstName: " . $first_name;
				}

				$add_note = array(
					'ContactId'         => $contact_id,
					'CreationDate'      => $today,
					'CompletionDate'    => $today,
					'ActionDate'        => $today,
					'EndDate'           => $today,
					'ActionType'        => 'Other',
					'ActionDescription' => 'Thrive Leads Note',
					'CreationNotes'     => $creation_notes,
				);

				$api->data( 'add', 'ContactAction', $add_note );

				if ( ! empty( $arguments['tve_affiliate'] ) ) {
					$api->data( 'add', 'Referral', array(
						'AffiliateId' => $arguments['tve_affiliate'],
						'ContactId'   => $contact_id,
						'DateSet'     => $today,
						'IPAddress'   => $ip_address,
						'Source'      => 'thrive opt in',
					) );
				}
			}

			$contact = $api->contact(
				'load',
				$contact_id,
				array(
					'Id',
					'Email',
					'Groups',
				)
			);

			$existing_groups = empty( $contact['Groups'] ) ? array() : explode( ',', $contact['Groups'] );

			if ( ! in_array( $list_identifier, $existing_groups ) ) {
				$api->contact( 'addToGroup', $contact_id, $list_identifier );
			}

			do_action( 'tvd_after_infusionsoft_contact_added', $this, $contact, $list_identifier, $arguments );

			// Update custom fields
			// Make another call to update custom mapped fields in order not to break the subscription call,
			// if custom data doesn't pass API custom fields validation
			if ( ! empty( $arguments['tve_mapping'] ) || ! empty( $arguments['automator_custom_fields'] ) ) {
				$this->updateCustomFields( $contact_id, $arguments );
			}

			return true;

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
		return '~Contact.Email~';
	}

	/**
	 * Retrieve the contact's tags.
	 * Tags in Infusionsoft are named Groups
	 *
	 * @param int $contact_id
	 *
	 * @return array
	 */
	public function get_contact_tags( $contact_id ) {

		$tags = array();

		if ( empty( $contact_id ) ) {
			return $tags;
		}

		$api = $this->get_api();

		$query_data = array(
			'ContactId' => $contact_id,
		);

		$selected_fields = array(
			'GroupId',
			'ContactGroup',
		);

		$saved_tags = $api->data( 'query', 'ContactGroupAssign', 9999, 0, $query_data, $selected_fields );

		if ( ! empty( $saved_tags ) ) {
			/**
			 * set the group id as key in tags array and
			 * set as value the group name
			 */
			foreach ( $saved_tags as $item ) {
				$tags[ $item['GroupId'] ] = $item['ContactGroup'];
			}
		}

		return $tags;
	}

	/**
	 * Retrieve all tags(groups) form Infusionsoft for current connection
	 *
	 * @return array
	 */
	public function get_tags( $use_cache = true ) {

		$tags = array();

		if ( $use_cache ) {
			$lists = $this->get_lists();
			foreach ( $lists as $list ) {
				$tags[ $list['id'] ] = $list['name'];
			}

			return $tags;
		}

		$api = $this->get_api();

		$query_data = array(
			'Id' => '%',
		);

		$selected_fields = array(
			'Id',
			'GroupName',
		);

		$saved_tags = $api->data( 'query', 'ContactGroup', 1000, 0, $query_data, $selected_fields );

		$data = $saved_tags;

		/**
		 * Infusionsoft has a limit of 1000 results to fetch, we should get all tags if the user has more
		 */
		$i = 1;
		while ( count( $saved_tags ) === 1000 ) {
			$saved_tags = $api->data( 'query', 'ContactGroup', 1000, $i, $query_data, $selected_fields );
			$data       = array_merge( $data, $saved_tags );
			$i ++;
		}

		if ( ! empty( $data ) ) {
			foreach ( $data as $item ) {
				$tags[ $item['Id'] ] = $item['GroupName'];
			}
		}

		return $tags;
	}

	/**
	 * Add a new Tag(Group) to Infusionsoft
	 *
	 * @param $tag_name
	 *
	 * @return int|null id
	 */
	public function create_tag( $tag_name ) {

		$query_data = array(
			'GroupName' => $tag_name,
		);

		$selected_fields = array(
			'Id',
			'GroupName',
		);

		$tags = $this->get_api()->data( 'query', 'ContactGroup', 1, 0, $query_data, $selected_fields );

		if ( is_array( $tags ) && ! empty( $tags ) ) {

			$tag = $tags[0];

			if ( isset( $tag['Id'] ) ) {

				return $tag['Id'];
			}
		}

		$id = $this->get_api()->data(
			'add',
			'ContactGroup',
			array(
				'GroupName' => $tag_name,
			)
		);

		$this->get_lists( false );

		return ! empty( $id ) ? $id : null;
	}

	/**
	 * @param array $params  which may contain `list_id`
	 * @param bool  $force   make a call to API and invalidate cache
	 * @param bool  $get_all where to get lists with their custom fields
	 *
	 * @return array|mixed
	 */
	public function get_api_custom_fields( $params, $force = false, $get_all = false ) {

		$custom_fields = array();

		try {
			$custom_fields = $this->get_all_custom_fields( $force );
		} catch ( Thrive_Dash_Api_Infusionsoft_InfusionsoftException $e ) {
		}

		return $custom_fields;
	}

	/**
	 * Get all custom fields
	 *
	 * @param $force calls the API and invalidate cache
	 *
	 * @return array|mixed
	 */
	public function get_all_custom_fields( $force ) {

		// Serve from cache if exists and requested
		$cached_data = $this->get_cached_custom_fields();

		if ( false === $force && ! empty( $cached_data ) ) {
			return $cached_data;
		}

		$custom_fields = array();

		// https://developer.infusionsoft.com/docs/table-schema/#DataFormField [custom fields Ids]
		// There is no IN operator in Infusionsoft XML-RPC that works with other fields beside id type, so will do separate calls bellow for each type
		// https://developer.infusionsoft.com/docs/xml-rpc/#data-query-a-data-table
		// Make sure we grab all custom fields [there are clients with more than 1k records.. been there, done that]

		foreach ( array_keys( $this->_custom_fields ) as $field_id ) {

			if ( empty( $field_id ) || ! is_int( $field_id ) ) {
				continue;
			}

			$custom_fields = $this->_getCustomFieldsById( $field_id, $custom_fields );
		}

		$this->_save_custom_fields( $custom_fields );

		return $custom_fields;
	}

	/**
	 * Get API custom text fields and append them to $custom_fields array
	 *
	 * @param int   $field_id
	 * @param array $custom_fields
	 *
	 * @return array
	 */
	protected function _getCustomFieldsById( $field_id, $custom_fields = array() ) {

		if ( empty( $field_id ) || ! is_int( $field_id ) || ! is_array( $custom_fields ) ) {
			return $custom_fields;
		}

		/** @var Thrive_Dash_Api_Infusionsoft $api */
		$api   = $this->get_api();
		$limit = 1000; // API pull limit
		$page  = 0;

		do {
			$response = $api->data(
				'query',
				'DataFormField',
				$limit,
				$page,
				array(
					'DataType' => (int) $field_id,
					'GroupId'  => '~<>~0',  //I suspect the group ID set to 0 is their way of soft deleting custom fields
				),
				array(
					'GroupId',
					'Name',
					'Label',
				)
			);

			if ( ! empty( $response ) && is_array( $response ) ) {
				$custom_fields = array_merge( $custom_fields, array_map( array(
					$this,
					'normalize_custom_field',
				), $response ) );
			}
			$page ++;
		} while ( count( $response ) === $limit );

		return $custom_fields;
	}

	/**
	 * Normalize custom field data
	 *
	 * @param $field
	 *
	 * @return array
	 */
	protected function normalize_custom_field( $field ) {

		$field = (array) $field;

		return array(
			'id'    => isset( $field['Name'] ) ? $field['Name'] : '',
			'name'  => ! empty( $field['Label'] ) ? $field['Label'] : '',
			'type'  => ! empty( $field['DataType'] ) && array_key_exists( (int) $field['DataType'], $this->_custom_fields ) ? $this->_custom_fields[ $field['DataType'] ] : '',
			'label' => ! empty( $field['Label'] ) ? $field['Label'] : '',
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

		$fields = array_merge( parent::get_custom_fields(), $this->_mapped_custom_fields );

		return $fields;
	}

	/**
	 * Call the API in order to update subscriber's custom fields
	 *
	 * @param int   $contact_id
	 * @param array $arguments
	 *
	 * @return bool
	 */
	public function updateCustomFields( $contact_id, $arguments ) {
		if ( ! is_int( $contact_id ) || empty( $arguments ) ) {
			return false;
		}
		$saved = false;
		try {
			if ( empty( $arguments['automator_custom_fields'] ) ) {
				$custom_fields = $this->buildMappedCustomFields( $arguments );
			} else {
				$custom_fields = $arguments['automator_custom_fields'];
			}

			if ( ! empty( $custom_fields ) ) {
				$api = $this->get_api();
				$api->contact(
					'update',
					$contact_id,
					$custom_fields
				);
				$saved = true;
			}

		} catch ( Thrive_Dash_Api_Infusionsoft_InfusionsoftException $e ) {
			$this->api_log_error( $contact_id, array( 'infusion_custom_fields' => $custom_fields ), $e->getMessage() );
		}

		return $saved;
	}

	/**
	 * Creates and prepare the mapping data from the update call
	 *
	 * @param array $args          form arguments
	 * @param array $custom_fields array of custom fields where to append/update
	 *
	 * @return array
	 */
	public function buildMappedCustomFields( $args, $custom_fields = array() ) {

		if ( empty( $args['tve_mapping'] ) || ! tve_dash_is_bas64_encoded( $args['tve_mapping'] ) || ! is_serialized( base64_decode( $args['tve_mapping'] ) ) ) {
			return $custom_fields;
		}

		$mapped_form_data = thrive_safe_unserialize( base64_decode( $args['tve_mapping'] ) );

		if ( is_array( $mapped_form_data ) ) {

			// Loop trough allowed custom fields names
			foreach ( $this->get_mapped_field_ids() as $mapped_field_name ) {

				// Extract an array with all custom fields (siblings) names from the form data
				// {ex: [mapping_url_0, .. mapping_url_n] / [mapping_text_0, .. mapping_text_n]}
				$cf_form_fields = preg_grep( "#^{$mapped_field_name}#i", array_keys( $mapped_form_data ) );

				// Matched "form data" for current allowed name
				if ( ! empty( $cf_form_fields ) && is_array( $cf_form_fields ) ) {

					// Pull form allowed data, sanitize it and build the custom fields array
					foreach ( $cf_form_fields as $cf_form_name ) {

						if ( empty( $mapped_form_data[ $cf_form_name ][ $this->_key ] ) ) {
							continue;
						}

						$mapped_form_field_id = $mapped_form_data[ $cf_form_name ][ $this->_key ];
						$cf_form_name         = str_replace( '[]', '', $cf_form_name );
						if ( ! empty( $args[ $cf_form_name ] ) ) {
							$args[ $cf_form_name ] = $this->process_field( $args[ $cf_form_name ] );
							// Build key => value pairs as the API needs
							$custom_fields[ '_' . $mapped_form_field_id ] = sanitize_text_field( $args[ $cf_form_name ] );
						}
					}
				}
			}
		}

		return $custom_fields;
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
				$mapped_data["_{$pair['key']}"] = $value;
			}
		}

		return $mapped_data;
	}

	/**
	 * @param       $email
	 * @param array $custom_fields
	 * @param array $extra
	 *
	 * @return false
	 */
	public function add_custom_fields( $email, $custom_fields = array(), $extra = array() ) {

		try {
			/** @var Thrive_Dash_Api_Infusionsoft $api */
			$api     = $this->get_api();
			$list_id = ! empty( $extra['list_identifier'] ) ? $extra['list_identifier'] : null;
			$args    = array(
				'email' => $email,
			);

			if ( ! empty( $extra['name'] ) ) {
				$args['name'] = $extra['name'];
			}

			unset( $extra['name'], $extra['list_identifier'] );

			$args          = array_merge( $extra, $args );
			$custom_fields = $this->prepare_custom_fields_for_api( $custom_fields );

			add_action(
				'tvd_after_infusionsoft_contact_added',
				static function ( $instance, $contact, $list_identifier, $arguments ) use ( $api, $custom_fields ) {

					$api->contact(
						'update',
						$contact['Id'],
						$custom_fields
					);
				},
				10,
				4
			);

			$this->add_subscriber( $list_id, $args );

		} catch ( Exception $e ) {
			return false;
		}
	}

	public function getTags() {
		return $this->get_tags();
	}


	/**
	 * get relevant data from webhook trigger
	 *
	 * @param $request WP_REST_Request
	 *
	 * @return array
	 */
	public function get_webhook_data( $request ) {

		$contact = $request->get_param( 'email' );

		return array( 'email' => empty( $contact ) ? '' : $contact );
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
		$api_fields      = $this->get_api_custom_fields( null, true );

		foreach ( $api_fields as $field ) {
			foreach ( $custom_fields as $key => $custom_field ) {
				if ( $field['id'] === $key && $custom_field ) {

					$prepared_fields[ '_' . $key ] = $custom_field;

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
		return array( 'autoresponder' => array( 'mailing_list', 'api_fields' ) );
	}

	public function get_automator_tag_autoresponder_mapping_fields() {
		return array( 'autoresponder' => array( 'tag_select' ) );
	}


	public function update_tags( $email, $tags = '', $extra = array() ) {
		$args = $this->get_args_for_tags_update( $email, $tags, $extra );

		return $this->add_subscriber( $tags, $args );
	}

	public function has_custom_fields() {
		return true;
	}
}

