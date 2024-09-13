<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_List_Connection_AWeber extends Thrive_Dash_List_Connection_Abstract {
	const APP_ID          = '10fd90de';
	const CONSUMER_KEY    = 'AkkjPM2epMfahWNUW92Mk2tl';
	const CONSUMER_SECRET = 'V9bzMop78pXTlPEAo30hxZF7dXYE6T6Ww2LAH95m';

	/**
	 * Return the connection type
	 *
	 * @return String
	 */
	public static function get_type() {
		return 'autoresponder';
	}

	/**
	 * @return bool
	 */
	public function has_tags() {

		return true;
	}

	/**
	 * get the authorization URL for the AWeber Application
	 *
	 * @return string
	 */
	public function getAuthorizeUrl() {
		/** @var Thrive_Dash_Api_AWeber $aweber */
		$aweber       = $this->get_api();
		$callback_url = admin_url( 'admin.php?page=tve_dash_api_connect&api=aweber' );

		list ( $request_token, $request_token_secret ) = $aweber->getRequestToken( $callback_url );

		update_option( 'thrive_aweber_rts', $request_token_secret );

		return $aweber->getAuthorizeUrl();
	}

	/**
	 * @return bool|void
	 */
	public function is_connected() {
		return $this->param( 'token' ) && $this->param( 'secret' );
	}

	/**
	 * @return string the API connection title
	 */
	public function get_title() {
		return 'AWeber';
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {
		$this->output_controls_html( 'aweber' );
	}

	/**
	 * should handle: read data from post / get, test connection and save the details
	 *
	 * on error, it should register an error message (and redirect?)
	 *
	 * @return mixed
	 */
	public function read_credentials() {
		/** @var Thrive_Dash_Api_AWeber $aweber */
		$aweber = $this->get_api();

		$aweber->user->tokenSecret  = get_option( 'thrive_aweber_rts' );
		$aweber->user->requestToken = ! empty( $_REQUEST['oauth_token'] ) ? sanitize_text_field( $_REQUEST['oauth_token'] ) : '';
		$aweber->user->verifier     = ! empty( $_REQUEST['oauth_verifier'] ) ? sanitize_text_field( $_REQUEST['oauth_verifier'] ) : '';

		try {
			list( $accessToken, $accessTokenSecret ) = $aweber->getAccessToken();
			$this->set_credentials( array(
				'token'  => $accessToken,
				'secret' => $accessTokenSecret,
			) );
		} catch ( Exception $e ) {
			$this->error( $e->getMessage() );

			return false;
		}

		$result = $this->test_connection();
		if ( $result !== true ) {
			$this->error( sprintf( __( 'Could not test AWeber connection: %s', 'thrive-dash' ), $result ) );

			return false;
		}

		$this->save();

		/**
		 * Fetch all custom fields on connect so that we have them all prepared
		 * - TAr doesn't need to fetch them from API
		 */
		$this->get_api_custom_fields( array(), true, true );

		return true;
	}

	/**
	 * test if a connection can be made to the service using the stored credentials
	 *
	 * @return bool|string true for success or error message for failure
	 */
	public function test_connection() {
		/** @var Thrive_Dash_Api_AWeber $aweber */
		$aweber = $this->get_api();

		try {
			$aweber->getAccount( $this->param( 'token' ), $this->param( 'secret' ) );

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
		return new Thrive_Dash_Api_AWeber( self::CONSUMER_KEY, self::CONSUMER_SECRET );
	}

	/**
	 * get all Subscriber Lists from this API service
	 *
	 * @return array
	 */
	protected function _get_lists() {
		/** @var Thrive_Dash_Api_AWeber $aweber */
		$aweber = $this->get_api();

		try {
			$lists   = array();
			$account = $aweber->getAccount( $this->param( 'token' ), $this->param( 'secret' ) );
			foreach ( $account->lists as $item ) {
				/** @var Thrive_Dash_Api_AWeber_Entry $item */
				$lists [] = array(
					'id'   => $item->data['id'],
					'name' => $item->data['name'],
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
	 * @param       $list_identifier
	 * @param array $arguments
	 *
	 * @return mixed
	 */
	public function add_subscriber( $list_identifier, $arguments ) {

		try {
			/** @var Thrive_Dash_Api_AWeber $aweber */
			$aweber  = $this->get_api();
			$account = $aweber->getAccount( $this->param( 'token' ), $this->param( 'secret' ) );
			$listURL = "/accounts/{$account->id}/lists/{$list_identifier}";
			$list    = $account->loadFromUrl( $listURL );

			# create a subscriber
			$params = array(
				'email'      => $arguments['email'],
				'ip_address' => tve_dash_get_ip(),
			);
			if ( ! empty( $arguments['name'] ) ) {
				$params['name'] = $arguments['name'];
			}

			if ( isset( $arguments['url'] ) ) {
				$params['custom_fields']['Web Form URL'] = $arguments['url'];
			}
			// create custom fields
			$custom_fields = $list->custom_fields;

			try {
				$custom_fields->create( array( 'name' => 'Web Form URL' ) );
			} catch ( Exception $e ) {
			}

			if ( ! empty( $arguments['phone'] ) && ( $phone_field_name = $this->phoneCustomFieldExists( $list ) ) ) {
				$params['custom_fields'][ $phone_field_name ] = $arguments['phone'];
			}

			if ( ! empty( $arguments['aweber_tags'] ) ) {
				$params['tags'] = explode( ',', trim( $arguments['aweber_tags'], ' ,' ) );
				$params['tags'] = array_map( 'trim', $params['tags'] );
			}

			if ( ( $existing_subscribers = $list->subscribers->find( array( 'email' => $params['email'] ) ) ) && $existing_subscribers->count() === 1 ) {
				$subscriber = $existing_subscribers->current();
				if ( ! empty( $arguments['name'] ) ) {
					$subscriber->name        = $params['name'];
				}
				if ( ! empty( $params['custom_fields'] ) ) {
					$subscriber->custom_fields = $params['custom_fields'];
				}
				if ( empty( $params['tags'] ) || ! is_array( $params['tags'] ) ) {
					$params['tags'] = array();
				}
				$tags = array_values( array_diff( $params['tags'], $subscriber->tags->getData() ) );

				if ( ! empty( $tags ) ) {
					$subscriber->tags = array(
						'add' => $tags,
					);
				}

				$new_subscriber = $subscriber->save() == 209;
			} else {
				$new_subscriber = $list->subscribers->create( $params );
			}

			if ( ! $new_subscriber ) {
				return sprintf( __( "Could not add contact: %s to list: %s", 'thrive-dash' ), $arguments['email'], $list->name );
			}

			// Update custom fields
			// Make another call to update custom mapped fields in order not to break the subscription call,
			// if custom data doesn't pass API custom fields validation
			$mapping = thrive_safe_unserialize( base64_decode( isset( $arguments['tve_mapping'] ) ? $arguments['tve_mapping'] : '' ) );
			if ( ! empty( $mapping ) || ! empty( $arguments['automator_custom_fields'] ) ) {
				$this->updateCustomFields( $list_identifier, $arguments, $params );
			}

		} catch ( Exception $e ) {
			return $e->getMessage();
		}

		return true;
	}

	protected function phoneCustomFieldExists( $list ) {
		$customFieldsURL = $list->custom_fields_collection_link;
		$customFields    = $list->loadFromUrl( $customFieldsURL );
		foreach ( $customFields as $custom ) {
			if ( stripos( $custom->name, 'phone' ) !== false ) {
				//return the name of the phone custom field cos users can set its name as: Phone/phone/pHone/etc
				//used in custom_fields for subscribers parameters
				/** @see add_subscriber */
				return $custom->name;
			}
		}

		return false;
	}

	/**
	 * output any (possible) extra editor settings for this API
	 *
	 * @param array $params allow various different calls to this method
	 */
	public function get_extra_settings( $params = array() ) {
		return $params;
	}

	/**
	 * output any (possible) extra editor settings for this API
	 *
	 * @param array $params allow various different calls to this method
	 */
	public function render_extra_editor_settings( $params = array() ) {
		$this->output_controls_html( 'aweber/tags', $params );
	}

	/**
	 * Return the connection email merge tag
	 *
	 * @return String
	 */
	public static function get_email_merge_tag() {
		return '{!email}';
	}

	/**
	 * @param array $params  which may contain `list_id`
	 * @param bool  $force   make a call to API and invalidate cache
	 * @param bool  $get_all where to get lists with their custom fields
	 *
	 * @return array
	 */
	public function get_api_custom_fields( $params, $force = false, $get_all = true ) {

		$lists = $this->get_all_custom_fields( $force );

		// Get custom fields for all list ids [used on localize in TAr]
		if ( true === $get_all ) {
			return $lists;
		}

		$list_id = isset( $params['list_id'] ) ? $params['list_id'] : null;

		if ( '0' === $list_id ) {
			$list_id = current( array_keys( $lists ) );
		}

		return array( $list_id => $lists[ $list_id ] );
	}

	/**
	 * Get all custom fields by list id
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
		$lists         = $this->_get_lists();

		if ( is_array( $lists ) ) {
			foreach ( $lists as $list ) {

				if ( empty( $list['id'] ) ) {
					continue;
				}

				$custom_fields[ $list['id'] ] = $this->getCustomFieldsByListId( $list['id'] );
			}
		}

		$this->_save_custom_fields( $custom_fields );

		return $custom_fields;
	}

	/**
	 * Get custom fields by list id
	 *
	 * @param $list_id
	 *
	 * @return array
	 */
	public function getCustomFieldsByListId( $list_id ) {

		$fields = array();

		if ( empty( $list_id ) ) {
			return $fields;
		}

		try {
			$account  = $this->get_api()->getAccount( $this->param( 'token' ), $this->param( 'secret' ) );
			$list_url = "/accounts/{$account->id}/lists/{$list_id}";
			$list_obj = $account->loadFromUrl( $list_url );

			// CF obj
			$custom_fields_url = $list_obj->custom_fields_collection_link;
			$custom_fields     = $list_obj->loadFromUrl( $custom_fields_url );

			foreach ( $custom_fields as $custom_field ) {

				if ( ! empty( $custom_field->data['name'] ) && ! empty( $custom_field->data['id'] ) ) {

					$fields[] = $this->_normalize_custom_field( $custom_field->data );
				}
			}
		} catch ( Thrive_Dash_Api_AWeber_Exception $e ) {
		}

		return $fields;
	}

	/**
	 * Normalize custom field data
	 *
	 * @param $field
	 *
	 * @return array
	 */
	protected function _normalize_custom_field( $field ) {

		$field = (array) $field;

		return array(
			'id'    => isset( $field['id'] ) ? $field['id'] : '',
			'name'  => ! empty( $field['name'] ) ? $field['name'] : '',
			'type'  => '', // API does not have type
			'label' => ! empty( $field['name'] ) ? $field['name'] : '',
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
	 * Call the API in order to update subscriber's custom fields
	 *
	 * @param $list_identifier
	 * @param $arguments
	 * @param $data
	 *
	 * @return bool
	 */
	public function updateCustomFields( $list_identifier, $arguments, $data ) {
		if ( ! $list_identifier || empty( $arguments ) || empty( $data['email'] ) ) {
			return false;
		}
		$saved = false;

		/** @var Thrive_Dash_Api_AWeber $aweber */
		$aweber   = $this->get_api();
		$account  = $aweber->getAccount( $this->param( 'token' ), $this->param( 'secret' ) );
		$list_url = "/accounts/{$account->id}/lists/{$list_identifier}";
		$list     = $account->loadFromUrl( $list_url );

		if ( empty( $arguments['automator_custom_fields'] ) ) {
			$custom_fields = $this->buildMappedCustomFields( $list_identifier, $arguments );
		} else {
			$custom_fields = $arguments['automator_custom_fields'];
		}

		$existing_subscribers = $list->subscribers->find( array( 'email' => $data['email'] ) );
		if ( $existing_subscribers && $existing_subscribers->count() === 1 && ! empty( $custom_fields ) ) {
			$subscriber                = $existing_subscribers->current();
			$subscriber->custom_fields = $custom_fields;
			$saved                     = $subscriber->save();
		}

		if ( ! $saved ) {
			$this->api_log_error( $list_identifier, $custom_fields, __( 'Could not update custom fields', 'thrive-dash' ) );
		}

		return $saved;
	}

	/**
	 * Creates and prepare the mapping data from the subscription form
	 *
	 * @param       $list_identifier
	 * @param       $args
	 * @param array $custom_fields
	 *
	 * @return array
	 */
	public function buildMappedCustomFields( $list_identifier, $args, $custom_fields = array() ) {

		if ( empty( $args['tve_mapping'] ) || ! tve_dash_is_bas64_encoded( $args['tve_mapping'] ) || ! is_serialized( base64_decode( $args['tve_mapping'] ) ) ) {
			return $custom_fields;
		}

		$mapped_form_data = thrive_safe_unserialize( base64_decode( $args['tve_mapping'] ) );

		if ( is_array( $mapped_form_data ) && $list_identifier ) {
			$api_custom_fields = $this->buildCustomFieldsList();

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

						$args[ $cf_form_name ] = $this->process_field( $args[ $cf_form_name ] );

						$mapped_form_field_id = $mapped_form_data[ $cf_form_name ][ $this->_key ];
						$field_label          = $api_custom_fields[ $list_identifier ][ $mapped_form_field_id ];

						$cf_form_name = str_replace( '[]', '', $cf_form_name );
						if ( ! empty( $args[ $cf_form_name ] ) ) {
							$args[ $cf_form_name ]         = $this->process_field( $args[ $cf_form_name ] );
							$custom_fields[ $field_label ] = sanitize_text_field( $args[ $cf_form_name ] );
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
		if ( $automation_data['mailing_list'] ) {
			$api_custom_fields = $this->buildCustomFieldsList();

			foreach ( $automation_data['api_fields'] as $pair ) {
				$value = sanitize_text_field( $pair['value'] );
				if ( $value ) {
					$field_label                 = $api_custom_fields[ $automation_data['mailing_list'] ][ $pair['key'] ];
					$mapped_data[ $field_label ] = $value;
				}
			}
		}

		return $mapped_data;
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
	 * @param       $email
	 * @param array $custom_fields
	 * @param array $extra
	 *
	 * @return false|int
	 */
	public function add_custom_fields( $email, $custom_fields = array(), $extra = array() ) {

		try {
			/** @var Thrive_Dash_Api_AWeber $api */
			$api     = $this->get_api();
			$list_id = ! empty( $extra['list_identifier'] ) ? $extra['list_identifier'] : null;
			$args    = array(
				'email' => $email,
			);

			if ( ! empty( $extra['name'] ) ) {
				$args['name'] = $extra['name'];
			}

			$this->add_subscriber( $list_id, $args );

			$account  = $api->getAccount( $this->param( 'token' ), $this->param( 'secret' ) );
			$list_url = "/accounts/{$account->id}/lists/{$list_id}";
			$list     = $account->loadFromUrl( $list_url );

			$existing_subscribers = $list->subscribers->find( array( 'email' => $email ) );

			if ( $existing_subscribers && $existing_subscribers->count() === 1 ) {
				$subscriber      = $existing_subscribers->current();
				$prepared_fields = $this->prepare_custom_fields_for_api( $custom_fields, $list_id );

				$subscriber->custom_fields = array_merge( $subscriber->data['custom_fields'], $prepared_fields );

				$subscriber->save();

				return $subscriber->id;
			}

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

		if ( empty( $list_identifier ) ) { // list identifier required here
			return array();
		}

		$api_fields = $this->get_api_custom_fields( array( 'list_id' => $list_identifier ), true );

		if ( empty( $api_fields[ $list_identifier ] ) ) {
			return array();
		}

		$prepared_fields = array();

		foreach ( $api_fields[ $list_identifier ] as $field ) {
			foreach ( $custom_fields as $key => $custom_field ) {
				if ( (int) $field['id'] === (int) $key && $custom_field ) {
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
		return array( 'autoresponder' => array( 'mailing_list' => array( 'api_fields' ), 'tag_input' => array() ) );
	}

	public function get_automator_tag_autoresponder_mapping_fields() {
		return array( 'autoresponder' => array( 'mailing_list', 'tag_input' ) );
	}

	public function has_custom_fields() {
		return true;
	}
}
