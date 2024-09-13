<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Dash_List_Connection_Abstract
 *
 * base class for all connections
 * acts as a high-level interface for the main functionalities exposed by the system
 */
abstract class Thrive_Dash_List_Connection_Abstract {
	/**
	 * @var array connection details (used for API calls)
	 */
	protected $_credentials = [];

	/**
	 * @var string internal key for the connection api
	 */
	protected $_key = null;

	/**
	 * @var mixed
	 */
	protected $_api;

	/**
	 * error message to be displayed in the editor
	 *
	 * @var string
	 */
	protected $_error = '';

	/**
	 * Transient name for API custom fields
	 *
	 * @var string
	 */
	public $_custom_fields_transient = '';

	/**
	 * Allowed custom fields for APIs that supports them
	 *
	 * @var array
	 */
	protected $_mapped_custom_fields = [];

	/**
	 * Default form fields for APIs
	 *
	 * @var array
	 */
	protected $_default_form_fields = [];

	/**
	 * @param string $key
	 */
	public function __construct( $key ) {

		$this->_key                     = $key;
		$this->_custom_fields_transient = 'api_custom_fields_' . $key;
		$this->set_custom_fields_mapping();
		$this->set_custom_default_fields_mapping();
	}

	/**
	 * If the snake_case version of the function does not exist, attempt to call the camelCase version.
	 * Can be deleted after we switch everything to snake_case.
	 *
	 * @param $method_name
	 * @param $arguments
	 *
	 * @return mixed
	 */
	public function __call( $method_name, $arguments ) {
		$camel_case_method_name = tve_dash_to_camel_case( $method_name );

		return method_exists( $this, $camel_case_method_name ) ? call_user_func_array( [
			$this,
			$camel_case_method_name,
		], $arguments ) : null;
	}

	/**
	 * Same as above, but for static calls.
	 * Can be deleted after we switch everything to snake_case.
	 *
	 * @param $method_name
	 * @param $arguments
	 *
	 * @return mixed
	 */
	public static function __callStatic( $method_name, $arguments ) {
		$camel_case_method_name = tve_dash_to_camel_case( $method_name );

		return method_exists( __CLASS__, $camel_case_method_name ) ? call_user_func_array( [
			static::class,
			$camel_case_method_name,
		], $arguments ) : null;
	}

	/**
	 * Can be removed in 2-3 releases
	 *
	 * @return String
	 * @deprecated
	 */
	public static function getType() {
		return static::get_type();
	}

	/**
	 * Returns the connection type
	 *
	 * @return String
	 */
	public static function get_type() {
		return 'autoresponder';
	}

	/**
	 * Can be removed in 2-3 releases
	 *
	 * @return mixed
	 * @deprecated
	 */
	public static function getEmailMergeTag() {
		return static::get_email_merge_tag();
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
	 * Called from TTW, do not delete.
	 *
	 * @return mixed
	 * @deprecated
	 */
	public function getApi() {
		return $this->get_api();
	}

	/**
	 * get the API Connection code to use in calls
	 *
	 * @return mixed
	 */
	public function get_api() {
		if ( ! isset( $this->_api ) ) {
			$this->_api = $this->get_api_instance();
		}

		return $this->_api;
	}

	/**
	 * Can be removed in 2-3 releases
	 *
	 * @return array
	 * @deprecated
	 */
	public function getCredentials() {
		return $this->get_credentials();
	}

	/**
	 * @return array
	 */
	public function get_credentials() {
		return $this->_credentials;
	}

	/**
	 * @param array $connection_details
	 *
	 * @return Thrive_Dash_List_Connection_Abstract
	 */
	public function set_credentials( $connection_details ) {
		$this->_credentials = $connection_details;

		/* if we set new credentials, the previously saved API is no longer valid */
		$this->_api = null;

		return $this;
	}

	/**
	 * Can be removed in 2-3 releases
	 *
	 * @return string|null
	 * @deprecated
	 */
	public function getKey() {
		return $this->get_key();
	}

	/**
	 * @return string
	 */
	public function get_key() {
		return $this->_key;
	}

	/**
	 * Can be deleted in 2-3 releases
	 *
	 * @deprecated
	 */
	public function isConnected() {
		return $this->is_connected();
	}

	/**
	 * Whether this list is connected to the service (has been authenticated)
	 *
	 * @return bool
	 */
	public function is_connected() {
		return ! empty( $this->_credentials );
	}

	/**
	 * @return bool
	 */
	public function is_related() {
		return false;
	}

	/**
	 * get connection parameter by name
	 *
	 * @param string $field
	 * @param string $default
	 *
	 * @return mixed
	 */
	public function param( $field, $default = null ) {
		return isset( $this->_credentials[ $field ] ) ? $this->_credentials[ $field ] : $default;
	}

	/**
	 * Used in TTW, do not delete.
	 *
	 * @param $field
	 * @param $value
	 *
	 * @return $this
	 * @deprecated
	 */
	public function setParam( $field, $value ) {
		return $this->set_param( $field, $value );
	}

	/**
	 * set connection parameter
	 *
	 * @param string $field
	 * @param mixed $value
	 *
	 * @return $this
	 */
	public function set_param( $field, $value ) {
		$this->_credentials[ $field ] = $value;

		return $this;
	}

	/**
	 * setup a global error message
	 *
	 * @param string $message
	 *
	 * @return Thrive_Dash_List_Connection_Abstract
	 */
	public function error( $message ) {
		if ( wp_doing_ajax() ) {
			return $message;
		}

		return $this->message( 'error', $message );
	}

	/**
	 * setup a global success message
	 *
	 * @param string $message
	 *
	 * @return Thrive_Dash_List_Connection_Abstract
	 */
	public function success( $message ) {
		if ( wp_doing_ajax() ) {
			return true;
		}

		return $this->message( 'success', $message );
	}

	/**
	 * save the connection details
	 *
	 * @return Thrive_Dash_List_Connection_Abstract
	 * @see Thrive_Dash_List_Manager
	 *
	 */
	public function save() {
		Thrive_Dash_List_Manager::save( $this );

		return $this;
	}

	/**
	 * disconnect (remove) this API connection
	 */
	public function disconnect() {

		$disconnect = apply_filters( 'tve_dash_disconnect_' . $this->get_key(), true );

		if ( true === $disconnect ) {
			$this->before_disconnect();
			$this->set_credentials( [] );
			Thrive_Dash_List_Manager::save( $this );
		}

		return $this;
	}

	/**
	 * Actions to take before a disconnection (can be overwritten by any API connection for different purposes)
	 *
	 * @return $this
	 */
	public function before_disconnect() {

		delete_transient( $this->_custom_fields_transient );

		return $this;
	}

	/**
	 * get the last error message in communicating with the api
	 *
	 * @return string the error message
	 */
	public function get_api_error() {
		return $this->_error;
	}

	/**
	 * Used in TTW, do not delete.
	 *
	 * @return string
	 * @deprecated
	 */
	public function getTitle() {
		return $this->get_title();
	}

	/**
	 * @return string the API connection title
	 */
	abstract public function get_title();

	/**
	 * Can be deleted in 2-3 releases
	 *
	 * @deprecated
	 */
	public function outputSetupForm() {
		$this->output_setup_form();
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	abstract public function output_setup_form();

	/**
	 * Can be removed in 2-3 releases
	 *
	 * @return mixed
	 * @deprecated
	 */
	public function readCredentials() {
		return $this->read_credentials();
	}

	/**
	 * should handle: read data from post / get, test connection and save the details
	 * on error return the error message
	 * on success return true
	 *
	 * @return mixed
	 */
	abstract public function read_credentials();

	/**
	 * Used in TTW, do not delete.
	 *
	 * @return bool|string
	 * @deprecated
	 */
	public function testConnection() {
		return $this->test_connection();
	}

	/**
	 * test if a connection can be made to the service using the stored credentials
	 *
	 * @return bool|string true for success or error message for failure
	 */
	abstract public function test_connection();

	/**
	 * Can be removed in 2-3 releases
	 *
	 * @param $list_identifier
	 * @param $arguments
	 *
	 * @deprecated
	 */
	public function addSubscriber( $list_identifier, $arguments ) {
		$this->add_subscriber( $list_identifier, $arguments );
	}

	/**
	 *
	 * @param mixed $list_identifier
	 * @param array $arguments
	 *
	 * @return mixed
	 */
	abstract public function add_subscriber( $list_identifier, $arguments );

	/**
	 * delete a contact matching arguments
	 *
	 * @param string $email
	 * @param array $arguments
	 *
	 * @return mixed
	 */
	public function delete_subscriber( $email, $arguments = [] ) {
		return false;
	}

	/**
	 * Can be deleted in 2-3 releases
	 *
	 * @param bool $use_cache
	 *
	 * @return array|bool
	 * @deprecated
	 */
	public function getLists( $use_cache = true ) {
		return $this->get_lists( $use_cache );
	}

	/**`
	 * get all Subscriber Lists from this API service
	 * it will first check a local cache for the existing lists
	 *
	 * @param bool $use_cache if true, it will read the lists from a local cache (wp options)
	 *
	 * @return array|bool for error
	 * @see self::_get_lists()
	 */
	public function get_lists( $use_cache = true ) {
		if ( ! $this->is_connected() ) {
			$this->_error = $this->get_title() . ' ' . __( 'is not connected', 'thrive-dash' );

			return false;
		}
		$cache = get_option( 'thrive_auto_responder_lists', [] );
		if ( ! $use_cache || ! isset( $cache[ $this->get_key() ] ) ) {
			$lists = $this->_get_lists();
			if ( $lists !== false ) {
				$cache[ $this->get_key() ] = $lists;
				update_option( 'thrive_auto_responder_lists', $cache );
			}
		} else {
			$lists = $cache[ $this->get_key() ];
		}

		return $lists;
	}

	/**
	 * if an API instance has a special way of designating the list, it should override this method
	 * e.g. "Choose the mailing list you want your subscribers to be assigned to"
	 *
	 * @return string
	 */
	public function get_list_sub_title() {
		return '';
	}

	/**
	 * Get fields mapping specific to an API for add to autoresponder action
	 *
	 * @return string[][]
	 */
	public function get_automator_add_autoresponder_mapping_fields() {
		return array( 'autoresponder' => array( 'mailing_list', 'api_fields' ) );
	}

	/**
	 * Get fields mapping specific to an API for tag in autoresponder action
	 *
	 * @return string[][]
	 */
	public function get_automator_tag_autoresponder_mapping_fields() {
		return array( 'autoresponder' => array( 'tag_input' ) );
	}

	/**
	 * Enable custom subfields based on api
	 *
	 * @param $fields
	 * @param $field
	 * @param $action_id
	 * @param $action_data
	 *
	 * @return mixed
	 */
	public function set_custom_autoresponder_fields( $fields, $field, $action_id, $action_data ) {
		return $fields;
	}

	/**
	 * get an array of warning messages (e.g. The access token will expire in xxx days. Click here to renew it)
	 *
	 * @return array
	 */
	public function get_warnings() {
		return [];
	}

	/**
	 * output any (possible) extra editor settings for this API
	 *
	 * @param array $params allow various different calls to this method
	 *
	 * @return array
	 */
	public function get_extra_settings( $params = [] ) {
		do_action( 'tvd_autoresponder_render_extra_editor_settings_' . $this->get_key() );

		return [];
	}

	/**
	 * Can be removed in 2-3 releases
	 *
	 * @param array $params
	 *
	 * @deprecated
	 */
	public function renderExtraEditorSettings( $params = [] ) {
		$this->render_extra_editor_settings( $params );
	}

	/**
	 * output any (possible) extra editor settings for this API
	 *
	 * @param array $params allow various different calls to this method
	 */
	public function render_extra_editor_settings( $params = [] ) {
		do_action( 'tvd_autoresponder_render_extra_editor_settings_' . $this->get_key() );

		return false;
	}

	public function render_before_lists_settings( $params = [] ) {
		return false;
	}

	/**
	 * @return string
	 */
	public function get_logo_url() {
		return TVE_DASH_URL . '/inc/auto-responder/views/images/' . $this->get_key() . '.png';
	}

	/**
	 * @return array
	 */
	public function prepare_json() {
		$properties = array(
			'key'             => $this->get_key(),
			'connected'       => $this->is_connected(),
			'credentials'     => $this->get_credentials(),
			'title'           => $this->get_title(),
			'type'            => $this->get_type(),
			'logoUrl'         => $this->get_logo_url(),
			'success_message' => $this->custom_success_message(),
			'can_test'        => $this->can_test(),
			'can_delete'      => $this->can_delete(),
			'can_edit'        => $this->can_edit(),
		);

		$properties['notification'] = TVE_Dash_InboxManager::instance()->get_by_slug( $this->get_key() );

		return $properties;
	}

	/**
	 * Custom message for success state
	 *
	 * @return string
	 */
	public function custom_success_message() {
		return '';
	}

	/**
	 * Can be overwritten by any api who needs custom data to be sent
	 *
	 * @param      $params
	 * @param bool $force
	 * @param bool $get_all
	 *
	 * @return array|mixed
	 */
	public function get_api_custom_fields( $params, $force = false, $get_all = false ) {
		$cache_data = get_transient( $this->_custom_fields_transient );

		return $cache_data ?: [];
	}

	/**
	 * Get extra parameters or fields from apis.
	 *
	 * @param $func
	 * @param $params
	 *
	 * @return mixed
	 */
	public function get_api_extra( $func, $params ) {
		$extra = [];

		if ( method_exists( $this, $func ) ) {
			$extra = call_user_func_array( array( $this, $func ), array( $params ) );
		}

		return [
			'extra'             => $extra,
			'api_custom_fields' => $this->get_api_custom_fields( $params ),
		];
	}

	/**
	 * Return an array with the lists, custom fields and extra settings
	 *
	 * @param array $params
	 * @param int $force
	 *
	 * @return array
	 */
	public function get_api_data( $params = [], $force = false ) {
		if ( empty( $params ) ) {           //in case it comes out empty string
			$params = [];
		}


		if ( $this->get_key() === 'email' ) {
			$force = true;
		} else {
			$transient = 'tve_api_data_' . $this->get_key();
			$data = get_transient( $transient );
		}

		if ( false === $force && tve_dash_is_debug_on() ) {
			$force = true;
		}

		if ( true === $force || false === $data ) {
			$data = array(
				'lists'          => $this->get_lists( false ),
				'extra_settings' => $this->get_extra_settings( $params ),
				'custom_fields'  => $this->get_custom_fields( $params ),
			);
			if ( $this->get_key() !== 'email' ) {
				set_transient( $transient, $data, MONTH_IN_SECONDS );
			}
		}

		$data['api_custom_fields'] = $this->get_api_custom_fields( $params, $force );

		return $data;
	}

	/**
	 * Get API custom form fields. By default, we have only name and phone
	 *
	 * @param array $params
	 *
	 * @return array
	 */
	public function get_custom_fields( $params = [] ) {
		return [
			[ 'id' => 'name', 'placeholder' => __( 'Name', 'thrive-dash' ) ],
			[ 'id' => 'phone', 'placeholder' => __( 'Phone', 'thrive-dash' ) ],
		];
	}

	/**
	 * output directly the html for a connection form from views/setup
	 *
	 * @param string $filename
	 * @param array $data allows passing variables to the view file
	 */
	protected function output_controls_html( $filename, $data = [] ) {
		include dirname( dirname( __DIR__ ) ) . '/views/setup/' . $filename . '.php';
	}

	/**
	 * @param $type
	 * @param $message
	 *
	 * @return Thrive_Dash_List_Connection_Abstract
	 */
	protected function message( $type, $message ) {
		Thrive_Dash_List_Manager::message( $type, $message );

		return $this;
	}

	/**
	 * explode fullname into first name and last name
	 *
	 * @param string $full_name
	 *
	 * @return array
	 */
	protected function get_name_parts( $full_name ) {
		if ( empty( $full_name ) ) {
			return array( '', '' );
		}
		$parts = explode( ' ', $full_name );

		if ( count( $parts ) === 1 ) {
			return array(
				$parts[0],
				'',
			);
		}
		$last_name  = array_pop( $parts );
		$first_name = implode( ' ', $parts );

		return array(
			sanitize_text_field( $first_name ),
			sanitize_text_field( $last_name ),
		);
	}

	/**
	 * Compose name from email
	 *
	 * @param $email
	 *
	 * @return array
	 */
	protected function get_name_from_email( $email ) {

		if ( empty( $email ) || ! is_string( $email ) || false === strpos( $email, '@' ) ) {
			return array( '', '' );
		}

		$email_name = str_replace( array( '.', '_', '-', '+', '=' ), ' ', strstr( $email, '@', true ) );

		list( $first_name, $last_name ) = $this->get_name_parts( $email_name );

		if ( empty( $first_name ) ) {
			$first_name = $email_name;
		}

		if ( empty( $last_name ) ) {
			$last_name = $first_name;
		}

		return [
			$first_name,
			$last_name,
		];
	}

	/**
	 * instantiate the API code required for this connection
	 *
	 * @return mixed
	 */
	protected abstract function get_api_instance();

	/**
	 * get all Subscriber Lists from this API service
	 *
	 * @return array|bool for error
	 */
	protected abstract function _get_lists();

	/**
	 * Whether the integration supports forms
	 *
	 * @return bool
	 */
	public function has_forms() {
		return false;
	}

	/**
	 * @return array
	 */
	protected function _get_forms() {
		return [];
	}

	/**
	 * @return array|bool
	 */
	public function get_forms() {
		if ( ! $this->is_connected() ) {
			$this->_error = $this->get_title() . ' ' . __( 'is not connected', 'thrive-dash' );

			return false;
		}

		return $this->_get_forms();
	}

	/**
	 * Get API Videos URLs
	 *
	 * @return array
	 */
	public function get_api_video_urls() {
		$return    = [];
		$transient = get_transient( 'ttw_api_urls' );

		if ( ! empty( $transient ) && is_array( $transient ) ) {
			$return = (array) $transient;
		}

		return $return;
	}

	/**
	 * Displays the video link with his html structure
	 *
	 * @return mixed|string
	 */
	public function display_video_link() {

		$api_slug   = strtolower( str_replace( array( ' ', '-' ), '', $this->get_key() ) );
		$video_urls = $this->get_api_video_urls();
		if ( ! array_key_exists( $api_slug, $video_urls ) ) {
			return '';
		}

		return include dirname( dirname( __DIR__ ) ) . '/views/includes/video-link.php';
	}

	/**
	 * A connection can work with different versions of API
	 *
	 * @return false|mixed
	 */
	public function get_version() {

		$credentials = (array) $this->get_credentials();

		if ( ! empty( $credentials['version'] ) ) {
			return $credentials['version'];
		}

		return false;
	}

	/**
	 * Defines a common field structure to work with
	 *
	 * @param array $data Field data from the API
	 *
	 * @return array
	 */
	protected function normalize_custom_field( $data ) {

		return [
			'id'    => $data['id'], //unique identifier
			'name'  => $data['name'], //should be name="" attribute for an input
			'type'  => $data['type'], //type for e.g. [url, text]
			'label' => $data['label'], //label to display for users
		];
	}

	/**
	 * Get the value of from email for email delivery services
	 *
	 * @return string
	 */
	public function get_email_param() {
		return $this->param( 'email', get_option( 'admin_email' ) );
	}

	/**
	 * Insert message to API error log
	 *
	 * @param string|int $list_identifier
	 * @param array $data
	 * @param string $error
	 *
	 * @return bool
	 */
	public function api_log_error( $list_identifier, $data, $error ) {

		if ( ! $list_identifier || ! $data || ! $error ) {
			return false;
		}

		global $wpdb;

		$return        = false;
		$api_log_table = $wpdb->prefix . 'tcb_api_error_log';
		$table_exists  = (bool) $wpdb->get_var( $wpdb->prepare( 'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=%s', $api_log_table ) );

		if ( $table_exists ) {
			$log_data = array(
				'date'          => date( 'Y-m-d H:i:s' ),
				'error_message' => tve_sanitize_data_recursive( $error ),
				'api_data'      => serialize( tve_sanitize_data_recursive( $data ) ),
				'connection'    => $this->get_key(),
				'list_id'       => maybe_serialize( tve_sanitize_data_recursive( $list_identifier ) ),
			);

			$return = (bool) $wpdb->insert( $api_log_table, $log_data );
		}

		return $return;
	}

	/**
	 * Mapped custom fields setter
	 */
	protected function set_custom_fields_mapping() {
		$this->_mapped_custom_fields = apply_filters(
			'tve_dash_mapped_custom_fields',
			array(
				array(
					'id'          => 'mapping_text',
					'placeholder' => __( 'Text', 'thrive-dash' ),
					'unique'      => false,
				),
				array(
					'id'          => 'mapping_url',
					'placeholder' => __( 'URL', 'thrive-dash' ),
					'unique'      => false,
				),
				array(
					'id'          => 'mapping_radio',
					'placeholder' => __( 'Radio', 'thrive-dash' ),
					'unique'      => false,
				),
				array(
					'id'          => 'mapping_select',
					'placeholder' => __( 'Dropdown', 'thrive-dash' ),
					'unique'      => false,
				),
				array(
					'id'          => 'mapping_checkbox',
					'placeholder' => __( 'Checkbox', 'thrive-dash' ),
					'unique'      => false,
				),
				array(
					'id'          => 'mapping_textarea',
					'placeholder' => __( 'Textarea', 'thrive-dash' ),
					'unique'      => false,
				),
				array(
					'id'          => 'mapping_file',
					'placeholder' => __( 'File upload', 'thrive-dash' ),
					'unique'      => true,
				),
				array(
					'id'          => 'mapping_avatar_picker',
					'placeholder' => __( 'Avatar picker', 'thrive-dash' ),
					'unique'      => true,
				),
				array(
					'id'          => 'mapping_hidden',
					'placeholder' => __( 'Hidden', 'thrive-dash' ),
					'unique'      => false,
				),
			)
		);
	}

	protected function set_custom_default_fields_mapping() {
		$this->_default_form_fields = apply_filters(
			'tve_dash_mapped_default_fields',
			array(
				array(
					'id'          => 'email',
					'placeholder' => __( 'Email', 'thrive-dash' ),
					'unique'      => true,
					'mandatory'   => true,
				),
				array(

					'id'          => 'name',
					'placeholder' => __( 'Name', 'thrive-dash' ),
					'unique'      => true,
					'mandatory'   => false,
				),
				array(
					'id'          => 'phone',
					'placeholder' => __( 'Phone', 'thrive-dash' ),
					'unique'      => true,
					'mandatory'   => false,
				),
			) );
	}

	/**
	 * Global getter for the hardcoded allowed custom fields type
	 *
	 * @return array
	 */
	public function get_custom_fields_mapping() {
		return $this->_mapped_custom_fields;
	}

	/**
	 * Global getter for the default fields type
	 *
	 * @return array
	 */
	public function get_default_fields_mapper() {
		return $this->_default_form_fields;
	}

	/**
	 * Custom fields cache getter
	 *
	 * @return mixed
	 */
	protected function get_cached_custom_fields() {
		return get_transient( $this->_custom_fields_transient );
	}

	/**
	 * Save custom fields
	 *
	 * @param array $custom_fields
	 *
	 * @return bool
	 */
	protected function _save_custom_fields( $custom_fields = [] ) {

		if ( empty( $custom_fields ) ) {
			return false;
		}

		$custom_fields = tve_sanitize_data_recursive( $custom_fields );

		return set_transient( $this->_custom_fields_transient, $custom_fields, WEEK_IN_SECONDS );
	}

	public function process_field( $field ) {
		if ( is_array( $field ) ) {
			$field = implode( ', ', $field );
		}

		return stripslashes( $field );
	}

	/**
	 * Whether the integration supports tags
	 *
	 * @return bool
	 */
	public function has_tags() {
		return false;
	}

	/**
	 * Whether the current integration can provide custom fields
	 *
	 * @return false
	 */
	public function has_custom_fields() {
		return false;
	}

	/**
	 * Whether the current integration has multiple opt-in types
	 *
	 * @return false
	 */
	public function has_optin() {
		return false;
	}

	/**
	 * @return string
	 */
	public function get_tags_key() {
		return $this->_key . '_tags';
	}

	/**
	 * @return string
	 */
	public function get_forms_key() {
		return $this->_key . '_form';
	}

	/**
	 * @return string
	 */
	public function get_optin_key() {
		return $this->_key . '_optin';
	}

	/**
	 * Get tags key for the api
	 *
	 * @return array
	 */
	protected function get_mapped_field_ids() {

		$mapped_fields = array_map(
			function ( $field ) {
				return $field['id'];
			},
			$this->_mapped_custom_fields
		);

		$mapped_fields[] = 'user_consent';
		$mapped_fields[] = 'gdpr';

		return $mapped_fields;
	}

	/**
	 * Push external tags in $data, EX: adds tags from tqb
	 *
	 * @param array|string $tags
	 * @param array $data
	 *
	 * @return array
	 */
	public function push_tags( $tags, $data = [] ) {
		if ( empty( $tags ) || ! $this->has_tags() ) {
			return $data;
		}

		if ( is_array( $tags ) ) {
			$tags = implode( ', ', $tags );
		} else if ( ! is_string( $tags ) ) {
			$tags = '';
		}

		$tag_key = $this->get_tags_key();

		if ( empty( $data[ $tag_key ] ) ) {
			$tag_data = $tags;
		} else {
			$tag_data = $data[ $tag_key ] . ( empty( $tags ) ? '' : ', ' . $tags );
		}

		$data[ $tag_key ] = trim( $tag_data );

		return $data;
	}

	/**
	 * Whether this connection can be edited
	 *
	 * @return bool
	 */
	public function can_edit() {
		return true;
	}

	/**
	 * Whether this connection can be deleted
	 *
	 * @return bool
	 */
	public function can_delete() {
		return true;
	}

	/**
	 * Whether this connection can be tested to validate that the stored credentials are correct
	 *
	 * @return bool
	 */
	public function can_test() {
		return true;
	}

	/**
	 * Can be removed in 2-3 releases
	 *
	 * @return array
	 * @deprecated
	 */
	public function getDataForSetup() {
		return $this->get_data_for_setup();
	}

	/**
	 * Get localization data needed for setting up this connection within a form
	 *
	 * @return array
	 */
	public function get_data_for_setup() {
		return [];
	}

	/**
	 * get relevant data from webhook trigger
	 *
	 * @param $request WP_REST_Request
	 *
	 * @return array
	 */
	public function get_webhook_data( $request ) {
		return [];
	}

	/**
	 * @param string $email
	 * @param array $tags
	 * @param array $extra
	 *
	 * @return int
	 */
	public function update_tags( $email, $tags = '', $extra = [] ) {
		$args            = $this->get_args_for_tags_update( $email, $tags, $extra );
		$list_identifier = ! empty( $args['list_identifier'] ) ? $args['list_identifier'] : null;

		unset( $args['list_identifier'] );

		return $this->add_subscriber( $list_identifier, $args );
	}

	/**
	 * Prepare necessary arguments for adding a tag
	 *
	 * @param string $email
	 * @param array|string $tags
	 * @param array $extra
	 *
	 * @return array
	 */
	public function get_args_for_tags_update( $email, $tags = '', $extra = [] ) {
		$tags_key = $this->get_tags_key();

		$return = [
			'email'   => $email,
			$tags_key => $tags,
		];

		foreach ( $extra as $key => $value ) {
			$return[ $key ] = $value;
		}

		return $return;
	}

	/**
	 * Add a custom field for a subscriber
	 * This method should be overwritten in avery instance that deals with custom fields
	 * Subscriber ID should be returned
	 *
	 * @param       $email
	 * @param array $custom_fields
	 * @param array $extra
	 *
	 * @return int
	 */
	public function add_custom_fields( $email, $custom_fields = [], $extra = [] ) {
		return 0;
	}

	/**
	 *
	 * Each api expects custom fields data in a specific format
	 * This method should be overwritten in every instance that deals with custom fields and prepare them as needed
	 *
	 * @param array $custom_fields
	 * @param null $list_identifier
	 *
	 * @return array
	 */
	protected function prepare_custom_fields_for_api( $custom_fields = [], $list_identifier = null ) {
		return [];
	}

	/**
	 * Get available custom fields for this api connection
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function get_available_custom_fields( $data = [] ) {
		return method_exists( $this, 'get_all_custom_fields' ) ? $this->get_all_custom_fields( true ) : [];
	}

	/**
	 * @param null $list
	 *
	 * @return array|mixed
	 */
	public function get_custom_fields_by_list( $list = null ) {
		$fields = $this->get_available_custom_fields();
		if ( $list && isset( $fields[ $list ] ) ) {
			$fields = $fields[ $list ];
		}

		return $fields;
	}

	/**
	 * Get a sanitized value from post
	 *
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return array|mixed|null
	 */
	protected function post( $key, $default = null ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			return $default;
		}

		return map_deep( $_POST[ $key ], 'sanitize_text_field' );
	}

	/**
	 * Build custom fields mapping for automations
	 *
	 * @param $automation_data
	 *
	 * @return array
	 */
	public function build_automation_custom_fields( $automation_data ) {
		return [];
	}
}
