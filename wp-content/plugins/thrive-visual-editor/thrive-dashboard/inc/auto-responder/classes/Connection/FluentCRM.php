<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

use FluentCrm\App\Models\CustomContactField;

class Thrive_Dash_List_Connection_FluentCRM extends Thrive_Dash_List_Connection_Abstract {
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
		return 'FluentCRM';
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
	 * check whether or not the FluentCRM plugin is installed
	 */
	public function pluginInstalled() {
		return function_exists( 'FluentCrmApi' );
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {
		$this->output_controls_html( 'fluentcrm' );
	}

	/**
	 * just save the key in the database
	 *
	 * @return mixed|void
	 */
	public function read_credentials() {
		if ( ! $this->pluginInstalled() ) {
			return $this->error( __( 'FluentCRM plugin must be installed and activated.', 'thrive-dash' ) );
		}

		$this->set_credentials( $this->post( 'connection', array() ) );

		$result = $this->test_connection();

		if ( $result !== true ) {
			return $this->error( '<strong>' . $result . '</strong>)' );
		}
		/**
		 * finally, save the connection details
		 */
		$this->save();

		return true;
	}

	/**
	 * test if a connection can be made to the service using the stored credentials
	 *
	 * @return bool|string true for success or error message for failure
	 */
	public function test_connection() {
		if ( ! $this->pluginInstalled() ) {
			return __( 'FluentCRM plugin must be installed and activated.', 'thrive-dash' );
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
		if ( ! $this->pluginInstalled() ) {
			return __( 'FluentCRM plugin is not installed / activated', 'thrive-dash' );
		}

		$name_array = array();
		if ( ! empty( $arguments['name'] ) ) {
			list( $first_name, $last_name ) = $this->get_name_parts( $arguments['name'] );
			$name_array = array(
				'first_name' => $first_name,
				'last_name'  => $last_name,
			);
		}

		if ( ! empty( $arguments['phone'] ) ) {
			$prepared_args['phone'] = $arguments['phone'];
		}

		if ( ! empty( $arguments['tve_mapping'] ) ) {
			$prepared_args['custom_values'] = $this->buildMappedCustomFields( $arguments );
		}
		$prepared_args['tags'] = array();
		$tag_key               = $this->get_tags_key();
		if ( ! empty( $arguments[ $tag_key ] ) ) {
			$prepared_args['tags'] = $this->importTags( $arguments[ $tag_key ] );
		}

		if ( isset( $arguments['fluentcrm_optin'] ) && 'd' === $arguments['fluentcrm_optin'] ) {
			$prepared_args['status'] = 'pending';
		}

		$data = [
			'email' => $arguments['email'],
			'lists' => array( $list_identifier ),
		];

		$data = array_merge( $data, $name_array, $prepared_args );

		try {
			$fluent  = FluentCrmApi( 'contacts' );
			$contact = $fluent->createOrUpdate( $data );

			if ( $contact->status === 'pending' ) {
				$contact->sendDoubleOptinEmail();
			}
		} catch ( Exception $exception ) {
			return $exception->getMessage();
		}

		return true;
	}

	/**
	 * Import tags
	 *
	 * @return array true for success or error message for failure
	 */
	public function importTags( $tags ) {
		$imported_tags = array();
		$inserted_tags = array();
		if ( ! empty( $tags ) ) {
			$tags = explode( ',', trim( $tags, ' ,' ) );

			foreach ( $tags as $tag ) {
				$inserted_tags[] = array(
					'title' => $tag,
				);
			}

			$inserted_tags = FluentCrmApi( 'tags' )->importBulk( $inserted_tags );//[1,2,3]

			foreach ( $inserted_tags as $new_tag ) {
				$imported_tags[] = $new_tag->id;

			}
		}

		return $imported_tags;
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

			foreach ( $this->get_mapped_field_ids() as $mapped_field ) {

				// Extract an array with all custom fields (siblings) names from form data
				// {ex: [mapping_url_0, .. mapping_url_n] / [mapping_text_0, .. mapping_text_n]}
				$custom_fields = preg_grep( "#^{$mapped_field}#i", array_keys( $form_data ) );

				// Matched "form data" for current allowed name
				if ( ! empty( $custom_fields ) && is_array( $custom_fields ) ) {

					// Pull form allowed data, sanitize it and build the custom fields array
					foreach ( $custom_fields as $cf_name ) {

						if ( empty( $form_data[ $cf_name ][ $this->_key ] ) ) {
							continue;
						}

						$field_id = $form_data[ $cf_name ][ $this->_key ];
						$cf_name  = str_replace( '[]', '', $cf_name );
						if ( ! empty( $args[ $cf_name ] ) ) {
							$args[ $cf_name ]         = $this->process_field( $args[ $cf_name ] );
							$mapped_data[ $field_id ] = sanitize_text_field( $args[ $cf_name ] );
						}

					}
				}
			}
		}

		return $mapped_data;
	}

	/**
	 * instantiate the API code required for this connection
	 *
	 * @return mixed
	 */
	protected function get_api_instance() {
		// no API instance needed here
		return null;
	}

	/**
	 * get all Subscriber Lists from this API service
	 *
	 * @return array|bool
	 */
	protected function _get_lists() {

		if ( ! $this->pluginInstalled() ) {
			$this->_error = __( 'FluentCRM plugin could be found.', 'thrive-dash' );

			return false;
		}

		$lists = array();

		$list_api = FluentCrmApi( 'lists' );

		// Get all the lists
		$all_lists = $list_api->all();

		foreach ( $all_lists as $list ) {
			$lists[] = array(
				'id'   => $list->id,
				'name' => $list->title,
			);
		}

		return $lists;
	}

	public function get_tags() {

		if ( ! $this->pluginInstalled() ) {
			$this->_error = __( 'FluentCRM plugin could be found.', 'thrive-dash' );

			return array();
		}

		$tags = array();

		$tag_api = FluentCrmApi( 'tags' );

		// Get all the tags
		$all_tags = $tag_api->all();

		foreach ( $all_tags as $tag ) {
			$tags[] = array(
				'id'       => $tag->id,
				'text'     => $tag->title,
				'selected' => false,
			);
		}

		return $tags;
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
		if ( class_exists( 'FluentCrm\App\Models\CustomContactField' ) ) {

			$cached_data = $this->get_cached_custom_fields();
			if ( false === $force && ! empty( $cached_data ) ) {
				return $cached_data;
			}

			$custom_fields = ( new CustomContactField )->getGlobalFields()['fields'];

			if ( is_array( $custom_fields ) ) {
				foreach ( $custom_fields as $field ) {
					if ( ! empty( $field['type'] ) && $field['type'] === 'text' ) {
						$custom_data[] = $this->normalize_custom_field( $field );
					}
				}
			}
		}

		$this->_save_custom_fields( $custom_data );

		return $custom_data;
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
			'id'    => isset( $field['slug'] ) ? $field['slug'] : '',
			'name'  => ! empty( $field['label'] ) ? $field['label'] : '',
			'type'  => ! empty( $field['type'] ) ? $field['type'] : '',
			'label' => ! empty( $field['label'] ) ? $field['label'] : '',
		);
	}

	/**
	 * Return the connection email merge tag
	 *
	 * @return String
	 */
	public static function get_email_merge_tag() {
		return '{{contact.email}}';
	}

	public function get_automator_add_autoresponder_mapping_fields() {
		return array( 'autoresponder' => array( 'mailing_list', 'api_fields', 'optin', 'tag_input' ) );
	}
}
