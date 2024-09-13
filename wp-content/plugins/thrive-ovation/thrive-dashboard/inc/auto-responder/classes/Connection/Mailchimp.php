<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_List_Connection_Mailchimp extends Thrive_Dash_List_Connection_Abstract {

	/**
	 * Return the connection type
	 *
	 * @return String
	 */
	public static function get_type() {
		return 'autoresponder';
	}

	/**
	 * Return the connection email merge tag
	 *
	 * @return String
	 */
	public static function get_email_merge_tag() {
		return '*|EMAIL|*';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return 'Mailchimp';
	}

	/**
	 * @return bool
	 */
	public function has_tags() {
		return true;
	}

	/**
	 * @return bool
	 */
	public function has_optin() {
		return true;
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {
		$related_api = Thrive_Dash_List_Manager::connection_instance( 'mandrill' );
		if ( $related_api->is_connected() ) {
			$credentials = $related_api->get_credentials();
			$this->set_param( 'email', $credentials['email'] );
			$this->set_param( 'mandrill-key', $credentials['key'] );
		}

		$this->output_controls_html( 'mailchimp' );
	}

	/**
	 * just save the key in the database
	 *
	 * @return mixed
	 */
	public function read_credentials() {
		$connection   = $this->post( 'connection' );
		$mandrill_key = ! empty( $connection['mandrill-key'] ) ? $connection['mandrill-key'] : '';

		if ( isset( $connection['mailchimp_key'] ) ) {
			$connection['mandrill-key'] = $connection['key'];
			$connection['key']          = $connection['mailchimp_key'];
			$mandrill_key               = $connection['mandrill-key'];
		}

		if ( empty( $_POST['connection']['key'] ) ) {
			return $this->error( __( 'You must provide a valid Mailchimp key', 'thrive-dash' ) );
		}

		$this->set_credentials( $connection );

		$result = $this->test_connection();

		if ( $result !== true ) {
			return $this->error( sprintf( __( 'Could not connect to Mailchimp using the provided key (<strong>%s</strong>)', 'thrive-dash' ), $result ) );
		}

		/**
		 * finally, save the connection details
		 */
		$this->save();

		/** @var Thrive_Dash_List_Connection_Mandrill $related_api */
		$related_api = Thrive_Dash_List_Manager::connection_instance( 'mandrill' );

		if ( ! empty( $mandrill_key ) ) {
			/**
			 * Try to connect to the email service too
			 */

			$related_api = Thrive_Dash_List_Manager::connection_instance( 'mandrill' );
			$r_result    = true;
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

		/**
		 * Fetch all custom fields on connect so that we have them all prepared
		 * - TAr doesn't need to fetch them from API
		 */
		$this->get_api_custom_fields( array(), true, true );

		return $this->success( __( 'Mailchimp connected successfully', 'thrive-dash' ) );
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
			/** @var Thrive_Dash_Api_Mailchimp $mc */
			$mc = $this->get_api();

			$mc->request( 'lists' );
		} catch ( Thrive_Dash_Api_Mailchimp_Exception $e ) {
			return $e->getMessage();
		}

		return true;
	}

	/**
	 * disconnect (remove) this API connection
	 */
	public function disconnect() {

		$this->before_disconnect();
		$this->set_credentials( array() );
		Thrive_Dash_List_Manager::save( $this );

		/**
		 * disconnect the email service too
		 */
		$related_api = Thrive_Dash_List_Manager::connection_instance( 'mandrill' );
		$related_api->set_credentials( array() );
		Thrive_Dash_List_Manager::save( $related_api );

		return $this;
	}

	/**
	 * Build interests object
	 *
	 * @param $list_identifier
	 * @param $arguments
	 *
	 * @return stdClass
	 */
	public function build_interests( $list_identifier, $arguments ) {

		$interests = new stdClass();

		if ( empty( $arguments ) || ! is_array( $arguments ) ) {
			return $interests;
		}

		if ( isset( $arguments['mailchimp_groupin'] ) && '0' !== (string) $arguments['mailchimp_groupin'] && ! empty( $arguments['mailchimp_group'] ) ) {
			$grouping = array();

			if ( is_array( $arguments['mailchimp_group'] ) ) {
				$group_ids = $arguments['mailchimp_group'];
			} else {
				$group_ids = explode( ',', $arguments['mailchimp_group'] );
			}

			$params['list_id']     = $list_identifier;
			$params['grouping_id'] = $arguments['mailchimp_groupin'];

			try {

				$grouping = $this->_get_groups( $params );
			} catch ( Thrive_Dash_Api_Mailchimp_Exception $e ) {
			}

			if ( ! empty( $grouping ) ) {

				foreach ( $grouping[0]->groups as $group ) {
					if ( in_array( (string) $group->id, $group_ids, true ) ) {
						$interests->{$group->id} = true;
					}
				}
			}
		}

		return $interests;
	}

	/**
	 * Build merge fields object
	 *
	 * @param $list_identifier
	 * @param $arguments
	 *
	 * @return stdClass
	 */
	public function build_merge_fields( $list_identifier, $arguments ) {

		$merge_fields = new stdClass();

		if ( empty( $list_identifier ) || empty( $arguments ) ) {
			return $merge_fields;
		}

		if ( ! empty( $arguments['name'] ) ) {
			list( $first_name, $last_name ) = $this->get_name_parts( $arguments['name'] );
		}

		// First name
		if ( ! empty( $first_name ) ) {
			$merge_fields->FNAME = $first_name;
		}

		// Last name
		if ( ! empty( $last_name ) ) {
			$merge_fields->LNAME = $last_name;
		}

		// Name
		if ( ! empty( $arguments['name'] ) ) {
			$merge_fields->NAME = $arguments['name'];
		}

		// Phone
		if ( ! empty( $arguments['phone'] ) ) {

			$phone_tag  = false;
			$api        = $this->get_api();
			$merge_vars = $this->getCustomFields( $list_identifier );

			foreach ( $merge_vars as $item ) {

				if ( 'phone' === $item->type || $item->name === $arguments['phone'] ) {
					$phone_tag                = true;
					$item_name                = $item->name;
					$item_tag                 = $item->tag;
					$merge_fields->$item_name = $arguments['phone'];
					$merge_fields->$item_tag  = $arguments['phone'];
				}
			}

			// Create phone merge field if not exists in mailchimp
			if ( false === $phone_tag ) {

				try {
					$api->request(
						'lists/' . $list_identifier . '/merge-fields',
						array(
							'name' => 'phone',
							'type' => 'phone',
							'tag'  => 'phone',
						),
						'POST'
					);

					$merge_fields->phone = $arguments['phone'];
				} catch ( Thrive_Dash_Api_Mailchimp_Exception $e ) {
				}
			}
		}

		return $merge_fields;
	}

	/**
	 * Create a simpler structure with [list_id] => [ field_id => field_name]
	 *
	 * @return array
	 */
	public function buildCustomFieldsList() {

		$parsed = array();

		foreach ( $this->get_all_custom_fields( false ) as $list_id => $merge_field ) {
			array_map(
				function ( $var ) use ( &$parsed, $list_id ) {
					$parsed[ $list_id ][ $var['id'] ] = $var['name'];
				},
				$merge_field
			);
		}

		return $parsed;
	}

	/**
	 * Add the mapped custom fields to merge_fields obj
	 *
	 * @param $list_identifier
	 * @param $args
	 * @param $merge_fields
	 *
	 * @return mixed
	 */
	public function buildMappedCustomFields( $list_identifier, $args, $merge_fields ) {

		if ( empty( $args['tve_mapping'] ) || ! tve_dash_is_bas64_encoded( $args['tve_mapping'] ) || ! is_serialized( base64_decode( $args['tve_mapping'] ) ) ) {
			return $merge_fields;
		}

		$mapped_form_data = thrive_safe_unserialize( base64_decode( $args['tve_mapping'] ) );

		if ( is_array( $mapped_form_data ) && is_object( $merge_fields ) && $list_identifier ) {

			// Cached and parsed custom fields from API
			$api_custom_fields = $this->buildCustomFieldsList();

			if ( empty( $api_custom_fields[ $list_identifier ] ) ) {
				return $merge_fields;
			}

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
						$field_label          = $api_custom_fields[ $list_identifier ][ $mapped_form_field_id ];

						$cf_form_name = str_replace( '[]', '', $cf_form_name );
						if ( ! empty( $args[ $cf_form_name ] ) ) {
							$args[ $cf_form_name ]        = $this->process_field( $args[ $cf_form_name ] );
							$merge_fields->{$field_label} = sanitize_text_field( $args[ $cf_form_name ] );
						}
					}
				}
			}
		}

		return $merge_fields;
	}

	/**
	 * Build custom fields mapping for automations
	 *
	 * @param $automation_data
	 *
	 * @return object
	 */
	public function build_automation_custom_fields( $automation_data ) {
		$mapped_data = new stdClass();
		if ( $automation_data['mailing_list'] ) {
			$api_custom_fields = $this->buildCustomFieldsList();

			foreach ( $automation_data['api_fields'] as $pair ) {
				$value = sanitize_text_field( $pair['value'] );
				if ( $value ) {
					$field_label               = $api_custom_fields[ $automation_data['mailing_list'] ][ $pair['key'] ];
					$mapped_data->$field_label = $value;
				}
			}
		}

		return $mapped_data;
	}

	/**
	 * Build optin and status data
	 *
	 * @param $list_identifier
	 * @param $arguments
	 *
	 * @return array
	 */
	public function build_statuses( $list_identifier, $arguments ) {

		if ( empty( $list_identifier ) || empty( $arguments ) ) {
			return array( '', '' );
		}

		$status    = '';
		$user_hash = md5( strtolower( $arguments['email'] ) );
		$optin     = isset( $arguments['mailchimp_optin'] ) && 's' === $arguments['mailchimp_optin'] ? 'subscribed' : 'pending';

		$api = $this->get_api();

		try {

			$contact = $api->request( 'lists/' . $list_identifier . '/members/' . $user_hash, array(), 'GET' );

			if ( ! empty( $contact->status ) ) {
				$status = $contact->status;
			}

		} finally {
			return array(
				$optin,
				$status,
			);
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

			if ( ! empty( $contact ) && $contact->status !== 'archived' ) {
				$user_hash = md5( $email );
				$api->request( 'lists/' . $arguments['list_identifier'] . '/members/' . $user_hash, array(), 'DELETE' );
			}
		}

		return true;
	}

	/**
	 * @param mixed $list_identifier
	 * @param array $arguments
	 *
	 * @return bool|mixed|string|void
	 * @throws Thrive_Dash_Api_Mailchimp_Exception
	 */
	public function add_subscriber( $list_identifier, $arguments ) {

		$arguments = (array) $arguments;

		if ( empty( $list_identifier ) || empty( $arguments ) ) {
			return __( 'Invalid arguments supplied in ' . __METHOD__, 'thrive-dash' );
		}

		// Build optin and status
		list( $optin, $status ) = $this->build_statuses( $list_identifier, $arguments );

		$email     = strtolower( $arguments['email'] );
		$user_hash = md5( $email );

		/** @var Thrive_Dash_Api_Mailchimp $api */
		$api = $this->get_api();

		// Subscribe
		try {

			$data = array(
				'email_address' => $email,
				'status'        => ! empty( $status ) && 'subscribed' === $status ? $status : $optin,
				'merge_fields'  => $this->build_merge_fields( $list_identifier, $arguments ),
				'interests'     => $this->build_interests( $list_identifier, $arguments ),
				'status_if_new' => $optin,
			);

			// Add custom fields to this request cuz it's sending the email twice on double optin
			if ( ! empty( $arguments['tve_mapping'] ) ) {
				// Append custom fields to existing ones
				$data['merge_fields'] = $this->buildMappedCustomFields( $list_identifier, $arguments, $data['merge_fields'] );
			}
			if ( ! empty( $arguments['automator_custom_fields'] ) ) {
				$data['merge_fields'] = (object) array_merge( (array) $data['merge_fields'], (array) $arguments['automator_custom_fields'] );
			}

			// On double optin, send the tags directly to the body [known problems on mailchimp tags endpoint]
			if ( isset( $arguments['mailchimp_optin'] ) && 'd' === $arguments['mailchimp_optin'] && ! empty( $arguments['mailchimp_tags'] ) ) {
				$tags         = explode( ',', $arguments['mailchimp_tags'] );
				$data['tags'] = $tags;
			}

			$member = $this->get_contact( $list_identifier, $email );
			if ( $member ) { //update contact
				$api->request( 'lists/' . $list_identifier . '/members/' . $user_hash, $data, 'PUT' );
			} else { //create contact
				$api->request( 'lists/' . $list_identifier . '/members', $data, 'POST' );
			}
		} catch ( Thrive_Dash_Api_Mailchimp_Exception $e ) {
			// mailchimp returns 404 if email contact already exists?
			//$e->getMessage() ? $e->getMessage() : __( 'Unknown Mailchimp Error', 'thrive-dash' );
		} catch ( Exception $e ) {
			return $e->getMessage() ?: __( 'Unknown Error', 'thrive-dash' );
		}

		// Add tags for other optin beside double
		if ( ! empty( $arguments['mailchimp_tags'] ) ) {
			try {
				$tags = explode( ',', $arguments['mailchimp_tags'] );
				$this->addTagsToContact( $list_identifier, $email, $tags );
			} catch ( Thrive_Dash_Api_Mailchimp_Exception $e ) {
				return __( 'Assign tag error: ' . $e->getMessage(), 'thrive-dash' );
			}
		}

		return true;
	}

	/**
	 * @param $params
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_Mailchimp_Exception
	 */
	protected function _get_groups( $params ) {

		$return    = array();
		$groupings = new stdClass();
		/** @var Thrive_Dash_Api_Mailchimp $api */
		$api   = $this->get_api();
		$lists = $api->request( 'lists', array( 'count' => 1000 ) );

		if ( empty( $params['list_id'] ) && ! empty( $lists ) ) {
			$params['list_id'] = $lists->lists[0]->id;
		}

		foreach ( $lists->lists as $list ) {
			if ( (string) $list->id === (string) $params['list_id'] ) {
				$groupings = $api->request( 'lists/' . $params['list_id'] . '/interest-categories', array( 'count' => 1000 ) );
			}
		}

		if ( $groupings->total_items > 0 ) {
			foreach ( $groupings->categories as $grouping ) {
				//if we have a grouping id in the params, we should only get that grouping
				if ( isset( $params['grouping_id'] ) && $grouping->id !== $params['grouping_id'] ) {
					continue;
				}
				$groups = $api->request( 'lists/' . $params['list_id'] . '/interest-categories/' . $grouping->id . '/interests', array( 'count' => 1000 ) );

				if ( $groups->total_items > 0 ) {
					$grouping->groups = $groups->interests;
				}
				$return[] = $grouping;
			}
		}

		return $return;
	}

	/**
	 * Makes a request through Mailchimp API for getting custom fields for a list
	 *
	 * @param string $list
	 *
	 * @return array|string
	 */
	public function getCustomFields( $list ) {

		try {
			/** @var Thrive_Dash_Api_Mailchimp $api */
			$api = $this->get_api();

			$query      = array(
				'count' => 1000,
			);
			$merge_vars = $api->request( 'lists/' . $list . '/merge-fields', $query );

			if ( 0 === $merge_vars->total_items ) {
				return array();
			}
		} catch ( Thrive_Dash_Api_Mailchimp_Exception $e ) {
			return $e->getMessage() ?: __( 'Unknown Mailchimp Error', 'thrive-dash' );
		} catch ( Exception $e ) {
			return $e->getMessage() ?: __( 'Unknown Error', 'thrive-dash' );
		}

		return $merge_vars->merge_fields;
	}

	/**
	 * @param $list_id
	 * @param $email_address
	 * @param $tags
	 *
	 * @return bool
	 * @throws Thrive_Dash_Api_Mailchimp_Exception
	 */
	public function addTagsToContact( $list_id, $email_address, $tags ) {
		if ( ! $list_id || ! $email_address || ! $tags ) {
			throw new Thrive_Dash_Api_Mailchimp_Exception( __( 'Missing required parameters for adding tags to contact', 'thrive-dash' ) );
		}

		$list_tags = $this->getListTags( $list_id );

		if ( is_array( $tags ) ) {
			foreach ( $tags as $tag_name ) {

				$tag_name = trim( $tag_name );

				if ( isset( $list_tags[ $tag_name ] ) ) {
					// Assign existing tag to contact/subscriber
					$tag_id = $list_tags[ $tag_name ]['id'];

					try {
						$this->assignTag( $list_id, $tag_id, $email_address );
					} catch ( Thrive_Dash_Api_Mailchimp_Exception $e ) {
						$this->_error = $e->getMessage() . ' ' . __( 'Please re-check your API connection details.', 'thrive-dash' );
					}

					continue;
				}

				try {
					// Create tag and assign it to contact/subscriber
					$this->createAndAssignTag( $list_id, $tag_name, $email_address );
				} catch ( Thrive_Dash_Api_Mailchimp_Exception $e ) {
				}
			}
		}
	}

	/**
	 * Get all tags of a list
	 *
	 * @param $list_id
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_Mailchimp_Exception
	 */
	public function getListTags( $list_id ) {

		$segments_by_name = array();
		$count            = 100; //default is 10
		$offset           = 0;
		$total_items      = 0;

		do {
			/** @var Thrive_Dash_Api_Mailchimp $api */
			$api = $this->get_api();

			$response = $api->request(
				'lists/' . $list_id . '/segments',
				array(
					'count'  => $count,
					'offset' => $offset,
				),
				'GET',
				true
			);

			if ( is_object( $response ) && ( isset( $response->total_items ) && $response->total_items > 0 ) ) {
				$total_items = $response->total_items;

				if ( empty( $response->segments ) ) {
					break;
				}

				foreach ( $response->segments as $segment ) {
					$segments_by_name[ $segment->name ]['id']   = $segment->id;
					$segments_by_name[ $segment->name ]['name'] = $segment->name;
					$segments_by_name[ $segment->name ]['type'] = $segment->type;
				}

				$offset += $count;
			}
		} while ( count( $segments_by_name ) < $total_items );

		return $segments_by_name;
	}

	/**
	 * @param $list_id
	 * @param $tag_id
	 * @param $email_address
	 *
	 * @return bool
	 * @throws Thrive_Dash_Api_Mailchimp_Exception
	 */
	public function assignTag( $list_id, $tag_id, $email_address ) {

		if ( ! $list_id || ! $tag_id || ! $email_address ) {
			throw new Thrive_Dash_Api_Mailchimp_Exception( __( 'Missing required parameters for adding tags to contact', 'thrive-dash' ) );
		}

		$save_tag = $this->get_api()->request( 'lists/' . $list_id . '/segments/' . $tag_id . '/members', array( 'email_address' => $email_address ), 'POST' );

		return is_object( $save_tag ) && isset( $save_tag->id );
	}

	/**
	 * @param $list_id
	 * @param $tag_name
	 * @param $email_address
	 *
	 * @return bool
	 * @throws Thrive_Dash_Api_Mailchimp_Exception
	 */
	public function createAndAssignTag( $list_id, $tag_name, $email_address ) {
		if ( ! $list_id || ! $tag_name || ! $email_address ) {
			return false;
		}

		try {
			$created_tag = $this->createTag( $list_id, $tag_name, $email_address );
		} catch ( Thrive_Dash_Api_Mailchimp_Exception $e ) {
		}

		if ( is_object( $created_tag ) && isset( $created_tag->id ) ) {
			return $this->assignTag( $list_id, $created_tag->id, $email_address );
		}

		return false;
	}

	/**
	 * @param $list_id
	 * @param $tag_name
	 * @param $email_address
	 *
	 * @return bool|object
	 */
	public function createTag( $list_id, $tag_name, $email_address ) {
		if ( ! $list_id || ! $tag_name || ! $email_address ) {
			return false;
		}

		return $this->get_api()->request( 'lists/' . $list_id . '/segments', array(
			'name'           => $tag_name,
			'static_segment' => array(),
		), 'POST' );
	}

	/**
	 * Allow the user to choose whether to have a single or a double optin for the form being edited
	 * It will hold the latest selected value in a cookie so that the user is presented by default with the same option selected the next time he edits such a form
	 *
	 * @param array $params
	 *
	 * @return array
	 * @throws Thrive_Dash_Api_Mailchimp_Exception
	 */
	public function get_extra_settings( $params = array() ) {
		$params['optin'] = empty( $params['optin'] ) ? ( isset( $_COOKIE['tve_api_mailchimp_optin'] ) ? sanitize_text_field( $_COOKIE['tve_api_mailchimp_optin'] ) : 'd' ) : $params['optin'];
		setcookie( 'tve_api_mailchimp_optin', $params['optin'], strtotime( '+6 months' ), '/' );
		$groups           = $this->_get_groups( $params );
		$params['groups'] = $groups;

		return $params;
	}

	/**
	 * Extract the info we need for custom fields based on list_id or API's first list_id
	 *
	 * @param string $list_id
	 *
	 * @return array
	 */
	public function get_custom_fields_for_list( $list_id ) {

		$extract = array();

		if ( empty( $list_id ) ) {
			return $extract;
		}

		// Needed custom fields type
		$allowed_types = array(
			'text',
			'url',
		);
		$custom_fields = $this->getCustomFields( $list_id );

		if ( is_array( $custom_fields ) ) {
			foreach ( $custom_fields as $field ) {
				$field = (object) $field; // just making sure we work with objects [APIs can change the structure]

				if ( ! empty( $field->type ) && 1 === (int) $field->public && in_array( $field->type, $allowed_types, true ) ) {
					$extract[] = $this->normalize_custom_field( $field );
				}
			}
		}

		return $extract;
	}

	/**
	 * Get all custom fields for all lists
	 *
	 * @param int $force
	 *
	 * @return array
	 */
	public function get_all_custom_fields( $force ) {

		$custom_data = array();

		// Serve from cache if exists and requested
		$cached_data = $this->get_cached_custom_fields();
		if ( false === $force && ! empty( $cached_data ) ) {
			return $cached_data;
		}

		// Build custom fields for every list
		$lists = $this->get_lists( $force );

		foreach ( $lists as $list ) {
			if ( ! empty( $list['id'] ) ) {
				$custom_data[ $list['id'] ] = $this->get_custom_fields_for_list( $list['id'] );
			}
		}

		$this->_save_custom_fields( $custom_data );

		return $custom_data;
	}

	/**
	 * Grab api custom fields
	 *
	 * @param      $params
	 * @param bool $force
	 * @param bool $get_all
	 *
	 * @return array|mixed
	 */
	public function get_api_custom_fields( $params, $force = false, $get_all = false ) {

		$lists = $this->get_all_custom_fields( $force );

		// Get custom fields for all list ids [used on localize in TAr]
		if ( true === $get_all ) {
			return $lists;
		}

		$list_id = isset( $params['list_id'] ) ? $params['list_id'] : null;

		if ( '0' === $list_id ) {
			$list_id = current( array_keys( $lists ) );
		}

		return array(
			$list_id => $lists[ $list_id ],
		);
	}

	/**
	 * Allow the user to choose whether to have a single or a double optin for the form being edited
	 * It will hold the latest selected value in a cookie so that the user is presented by default with the same option selected the next time he edits such a form
	 *
	 * @param array $params
	 *
	 * @throws Thrive_Dash_Api_Mailchimp_Exception
	 */
	public function render_extra_editor_settings( $params = array() ) {
		$params['optin'] = empty( $params['optin'] ) ? ( isset( $_COOKIE['tve_api_mailchimp_optin'] ) ? sanitize_text_field( $_COOKIE['tve_api_mailchimp_optin'] ) : 'd' ) : $params['optin'];
		setcookie( 'tve_api_mailchimp_optin', $params['optin'], strtotime( '+6 months' ), '/' );
		$groups           = $this->_get_groups( $params );
		$params['groups'] = $groups;
		$this->output_controls_html( 'mailchimp/api-groups', $params );
		$this->output_controls_html( 'mailchimp/optin-type', $params );
	}

	/**
	 * instantiate the API code required for this connection
	 *
	 * @return mixed|Thrive_Dash_Api_Mailchimp
	 * @throws Thrive_Dash_Api_Mailchimp_Exception
	 */
	protected function get_api_instance() {
		return new Thrive_Dash_Api_Mailchimp( $this->param( 'key' ) );
	}

	/**
	 * get all Subscriber Lists from this API service
	 *
	 * @return array|bool
	 */
	protected function _get_lists() {

		try {
			/** @var Thrive_Dash_Api_Mailchimp $mc */
			$mc = $this->get_api();

			$raw   = $mc->request( 'lists', array( 'count' => 1000 ) );
			$lists = array();

			if ( empty( $raw->total_items ) || empty( $raw->lists ) ) {
				return array();
			}
			foreach ( $raw->lists as $item ) {

				$lists [] = array(
					'id'   => $item->id,
					'name' => $item->name,
				);
			}

			return $lists;
		} catch ( Thrive_Dash_Api_Mailchimp_Exception $e ) {
			$this->_error = $e->getMessage() . ' ' . __( 'Please re-check your API connection details.', 'thrive-dash' );

			return false;
		}
	}

	/**
	 * Makes an API request for an email into a specific list
	 *
	 * @param string $list_id
	 * @param string $email
	 *
	 * @return strClass|null contact if exists
	 */
	public function get_contact( $list_id, $email ) {

		/** @var Thrive_Dash_Api_Mailchimp $api */
		$api     = $this->get_api();
		$contact = null;
		try {
			$contact = $api->request( 'lists/' . $list_id . '/members/' . md5( $email ) );
		} catch ( Exception $e ) {

		}

		return $contact;
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
	 * @param array $field
	 *
	 * @return array
	 */
	protected function normalize_custom_field( $field ) {

		$field = (object) $field;

		return array(
			'id'    => isset( $field->merge_id ) ? $field->merge_id : '',
			'name'  => ! empty( $field->tag ) ? $field->tag : '',
			'type'  => ! empty( $field->type ) ? $field->type : '',
			'label' => ! empty( $field->name ) ? $field->name : '',
		);
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
			/** @var Thrive_Dash_Api_Mailchimp $api */
			$api     = $this->get_api();
			$list_id = ! empty( $extra['list_identifier'] ) ? $extra['list_identifier'] : null;
			$args    = array(
				'email' => $email,
			);

			if ( ! empty( $extra['name'] ) ) {
				$args['name'] = $extra['name'];
			}
			$this->add_subscriber( $list_id, $args );

			$member = $this->get_contact( $list_id, $email );
			$data   = array(
				'merge_fields'  => (object) $this->prepare_custom_fields_for_api( $custom_fields, $list_id ),
				'email_address' => $email,
			);

			$api->request( 'lists/' . $list_id . '/members/' . md5( $email ), $data, 'PUT' );

			return $member->id;

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
	public function prepare_custom_fields_for_api( $custom_fields = array(), $list_identifier = null ) {

		$prepared_fields = array();
		$api_fields      = $this->get_api_custom_fields( array( 'list_id' => $list_identifier ), true );

		if ( empty( $api_fields[ $list_identifier ] ) ) {
			return $prepared_fields;
		}

		foreach ( $api_fields[ $list_identifier ] as $field ) {
			foreach ( $custom_fields as $key => $custom_field ) {
				if ( (int) $field['id'] === (int) $key ) {
					$prepared_fields[ $field['name'] ] = $custom_field;

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
		return array( 'autoresponder' => array( 'mailing_list' => array( 'api_fields' ), 'optin' => array(), 'tag_input' => array() ) );
	}

	public function get_automator_tag_autoresponder_mapping_fields() {
		return array( 'autoresponder' => array( 'mailing_list', 'tag_input' ) );
	}

	public function has_custom_fields() {
		return true;
	}
}
