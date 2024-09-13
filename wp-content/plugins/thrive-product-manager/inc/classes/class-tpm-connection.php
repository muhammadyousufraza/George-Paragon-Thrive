<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-product-manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TPM_Connection
 *
 * @property int    ttw_id
 * @property string ttw_salt
 * @property string ttw_email
 * @property string status
 * @property string ttw_expiration datetime until the current connection is known by TTW; ttw_salt has to be refreshed after this date;
 */
class TPM_Connection {

	const CONNECTED = 'connected';

	const NAME = 'tpm_connection';

	const SIGNATURE = 's6!xv(Q7Zp234L_snodt]CvG2meROk0Gurc49KiyJzz6kSjqAyqpUL&9+P4s';

	protected $_data = array();

	protected $_messages = array();

	protected $_errors = array();

	protected $_expected_data
		= array(
			'ttw_id',
			'ttw_email',
			'ttw_salt',
			'ttw_expiration',
		);

	protected static $_instance;

	private function __construct() {

		$this->_data = get_option( self::NAME, array() );

		$this->_messages = get_option( 'tpm_connection_messages', array() );

		add_filter( 'tpm_messages', array( $this, 'apply_messages' ) );
	}

	/**
	 * @return TPM_Connection
	 */
	public static function get_instance() {

		if ( ! self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __isset( $name ) {

		return isset( $this->_data[ $name ] );
	}

	public function __set( $key, $value ) {

		$this->_data[ $key ] = $value;

		return $this->_data[ $key ];
	}

	public function __get( $param ) {

		$value = null;

		if ( isset( $this->_data[ $param ] ) ) {
			$value = $this->_data[ $param ];
		}

		return $value;
	}

	public function is_connected() {

		return $this->status === self::CONNECTED;
	}

	public function set_data( $data ) {
		$this->_data = $data;
	}

	public function get_login_url() {

		return add_query_arg( array(
			'callback_url' => urlencode( base64_encode( $this->get_callback_url() ) ),
			'tpm_site'     => base64_encode( get_site_url() ),
		), Thrive_Product_Manager::get_ttw_url() . '/connect-account' );
	}

	/**
	 * URL where user is redirected back after he logs in TTW
	 *
	 * @return string
	 */
	protected function get_callback_url() {

		$url = Thrive_Product_Manager::get_instance()->get_admin_url();
		$url = add_query_arg( array(
			'tpm_token' => base64_encode( $this->get_token() ),
		), $url );

		return $url;
	}

	public function get_token() {

		$token = get_option( 'tpm_token', null );

		if ( ! empty( $token ) ) {
			return $this->decrypt( $token );
		}

		$rand_nr     = mt_rand( 1, 11 );
		$rand_chars  = '^!#)_@%*^@(yR&dsYh';
		$rand_string = substr( str_shuffle( $rand_chars ), 0, $rand_nr );

		$token     = $rand_string . strrev( base_convert( bin2hex( hash( 'sha512', uniqid( mt_rand() . microtime( true ) * 10000, true ), true ) ), 16, 36 ) );
		$to_length = ceil( strlen( $token ) / 2 );

		$token = $rand_nr . substr( $token, mt_rand( 1, 9 ), $to_length );

		add_option( 'tpm_token', $this->encrypt( $token ) );

		return $token;
	}

	public function encrypt( $str ) {

		$str .= '-' . self::SIGNATURE;

		$str = base64_encode( $str );

		return $str;
	}

	public function decrypt( $str ) {

		$str = base64_decode( $str );
		$str = explode( '-', $str );

		return $str[0];
	}

	protected function _is_valid_token( $token ) {

		$tpm_token = get_option( 'tpm_token', null );

		return $this->decrypt( $tpm_token ) === $token;
	}

	/**
	 * Process the request
	 * Validate it and sve the connection into DB
	 *
	 * @return bool
	 */
	public function process_data() {

		if ( ! $this->_is_valid_token( base64_decode( $_REQUEST['tpm_token'] ) ) ) {

			$this->_errors[] = __( 'Invalid token', Thrive_Product_Manager::T );

			return false;
		}

		$data = $this->_read_data();

		if ( ! $this->_is_valid( $data ) ) {

			$this->_errors[] = __( 'Invalid data', Thrive_Product_Manager::T );

			return false;
		}

		return $this->_save_connection( $data );
	}

	/**
	 * Reads expected data from request
	 *
	 * @return array
	 */
	protected function _read_data() {

		$data = array();

		$no_decode = array(
			'ttw_salt',
		);

		foreach ( $this->_expected_data as $key ) {

			//this has to be in clear; not encoded
			if ( in_array( $key, $no_decode, false ) ) {
				$data[ $key ] = $_REQUEST[ $key ];
				continue;
			}

			if ( ! empty( $_REQUEST[ $key ] ) ) {
				$data[ $key ] = base64_decode( urldecode( $_REQUEST[ $key ] ) );
			}
		}

		return $data;
	}

	public function render( $return = false ) {

		ob_start();
		include thrive_product_manager()->path( 'inc/templates/header.phtml' );

		if ( count( $this->_errors ) ) {
			include thrive_product_manager()->path( 'inc/templates/connection/error.phtml' );
		} else {
			include thrive_product_manager()->path( 'inc/templates/connection/form.phtml' );
		}

		$html = ob_get_clean();

		if ( $return === true ) {
			return $html;
		}

		echo $html;
	}

	public function get_data() {

		return $this->_data;
	}

	/**
	 * @param $data
	 *
	 * @return bool
	 */
	protected function _save_connection( $data ) {

		$data['status'] = self::CONNECTED;
		$this->_data    = $data;
		update_option( self::NAME, $data );
		delete_option( 'tpm_bk_connection' );

		tpm_cron()->log( '_save_connection()' );

		if ( ! tpm_cron()->schedule( $this->ttw_expiration ) ) {
			add_filter( 'tpm_messages', array( tpm_cron(), 'push_message_event_unscheduled' ) );
		}

		tpm_delete_transient( 'td_ttw_licenses_details' );
		tpm_delete_transient( 'td_ttw_connection_error' );

		return true;
	}

	/**
	 * Check if data is as expected
	 *
	 * @param $data array
	 *
	 * @return bool
	 */
	protected function _is_valid( $data ) {

		if ( ! is_array( $data ) ) {
			return false;
		}

		$keys = array_intersect( array_keys( $data ), $this->_expected_data );

		return $keys === $this->_expected_data;
	}

	public function apply_messages( $messages = array() ) {

		$messages = array_merge( $messages, $this->_messages );

		$this->_messages = array();
		update_option( 'tpm_connection_messages', array() );

		return $messages;
	}

	public function push_message( $str, $status ) {

		$str = __( $str, Thrive_Product_Manager::T );

		$this->_messages[] = array(
			'message' => $str,
			'status'  => $status,
		);

		update_option( 'tpm_connection_messages', $this->_messages );
	}

	public function disconnect() {

		TPM_Product_List::get_instance()->clear_cache();
		TPM_License_Manager::get_instance()->clear_cache();

		tpm_cron()->unschedule();

		//save the connection data to be used at TPM deactivation plugin
		update_option( 'tpm_bk_connection', $this->_data );
		tpm_delete_transient( 'td_ttw_licenses_details' );
		tpm_delete_transient( 'td_ttw_connection_error' );

		return delete_option( self::NAME );
	}

	public function get_email() {

		return $this->ttw_email;
	}

	public function get_disconnect_url() {

		$url = Thrive_Product_Manager::get_instance()->get_admin_url();
		$url = add_query_arg( array( 'tpm_disconnect' => 1 ), $url );

		return $url;
	}

	/**
	 * Checks if token's validation date is lower or equal than now()
	 *
	 * @return bool
	 */
	public function is_expired() {

		$date = (int) strtotime( $this->ttw_expiration );

		return $date <= time();
	}

	/**
	 * Does a request to TTW for a new token and saves it on connection
	 * - sets a WP Cron
	 *
	 * @return bool
	 */
	public function refresh_token() {

		tpm_cron()->log( 'refresh_token()' );

		$user_id = $this->ttw_id;
		if ( empty( $user_id ) ) {
			return false;
		}

		$ttw_salt = $this->ttw_salt;
		if ( empty( $ttw_salt ) ) {
			return false;
		}

		$params = array();

		$request = new TPM_Request( '/api/v1/public/refresh-tokens/user/' . $user_id, $params );
		$request->set_header( 'Authorization', $ttw_salt );

		tpm_cron()->log( var_export( $request, true ) );

		$proxy_request = new TPM_Proxy_Request( $request );
		$response      = $proxy_request->execute( '/tpm/proxy' );

		$body = wp_remote_retrieve_body( $response );

		tpm_cron()->log( var_export( $body, true ) );

		if ( empty( $body ) ) {
			return false;
		}

		$defaults = array(
			'success'    => false,
			'auth_token' => '',
		);

		$body = json_decode( $body, true );

		if ( true === is_array( $body ) ) {
			$body = array_merge( $defaults, $body );
		} else {
			$body = $defaults;
		}

		if ( empty( $body['auth_token'] ) ) {
			return false;
		}

		! empty( $body['auth_token'] ) ? $this->_data['ttw_salt'] = $body['auth_token'] : null;

		! empty( $body['ttw_expiration'] ) ? $this->_data['ttw_expiration'] = $body['ttw_expiration'] : null;

		return $this->_save_connection( $this->_data );
	}
}
