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
 * Class TD_TTW_Connection
 *
 * This is a twin of TPM_Connection from thrive product manager
 * Since we can't rely on TPM existence on client site we have to implement connection logic here
 * We will use the same db data from TPM, if any
 *
 * @property int    ttw_id
 * @property string ttw_salt
 * @property string ttw_email
 * @property string status
 * @property string ttw_expiration datetime until the current connection is known by TTW; ttw_salt has to be refreshed after this date;
 * @static new TD_TTW_Connection get_instance
 */
class TD_TTW_Connection {

	use TD_Magic_Methods;

	use TD_Singleton;

	use TD_TTW_Utils;

	const CONNECTED = 'connected';

	const NAME = 'tpm_connection';

	const SIGNATURE = 's6!xv(Q7Zp234L_snodt]CvG2meROk0Gurc49KiyJzz6kSjqAyqpUL&9+P4s';

	protected $_errors = array();

	protected $_messages = array();

	protected $_expected_data
		= array(
			'ttw_id',
			'ttw_email',
			'ttw_salt',
			'ttw_expiration',
		);

	private function __construct() {

		$this->_data = get_option( static::NAME, array() );
	}

	public function is_connected() {

		return static::CONNECTED === $this->status;
	}

	/**
	 * Disconnect ttw account
	 */
	public function disconnect() {

		delete_option( static::NAME );
		thrive_delete_transient( TD_TTW_User_Licenses::NAME );
		thrive_delete_transient( 'td_ttw_connection_error' );
	}

	public function get_login_url() {

		return add_query_arg(
			array(
				'callback_url' => urlencode( base64_encode( $this->get_callback_url() ) ),
				'td_site'      => base64_encode( get_site_url() ),
			),
			static::get_ttw_url() . '/connect-account/'
		);
	}

	/**
	 * URL where user is redirected back after he logs in TTW
	 *
	 * @return string
	 */
	protected function get_callback_url() {

		$url = admin_url( 'admin.php?page=tve_dash_ttw_account' );

		return add_query_arg(
			array(
				'td_token' => base64_encode( $this->get_token() ),
			),
			$url
		);
	}

	/**
	 * Get signature token, if none create one
	 *
	 * @return mixed|string
	 */
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

	/**
	 * Encrypt a given string
	 *
	 * @param string $str
	 *
	 * @return string
	 */
	public function encrypt( $str ) {

		$str .= '-' . static::SIGNATURE;

		$str = base64_encode( $str );

		return $str;
	}

	/**
	 * Decrypt a given string
	 *
	 * @param string $str
	 *
	 * @return mixed|string
	 */
	public function decrypt( $str ) {

		$str = base64_decode( $str );
		$str = explode( '-', $str );

		return $str[0];
	}

	/**
	 * If environment is on a staging server
	 *
	 * @return bool
	 */
	public static function is_debug_mode() {

		return ( defined( 'TD_TTW_DEBUG' ) && TD_TTW_DEBUG ) || ! empty( $_REQUEST['td_debug'] );
	}

	/**
	 * @return string
	 */
	public static function get_ttw_url() {

		if ( defined( 'TTW_URL' ) ) {

			return trim( TTW_URL, '/' );
		}

		if ( static::is_debug_mode() ) {

			return get_option( 'tpm_ttw_url', 'https://staging.thrivethemes.com' );
		}

		return 'https://thrivethemes.com';
	}

	public function get_email() {

		return $this->ttw_email;
	}

	public function get_disconnect_url() {

		$url = admin_url( 'admin.php?page=tve_dash_ttw_account' );
		$url = add_query_arg( array( 'td_disconnect' => 1 ), $url );

		return $url;
	}

	/**
	 * Render ttw connection screen
	 *
	 * @param false $return
	 *
	 * @return false|string
	 */
	public function render( $return = false ) {

		ob_start();
		include $this->path( 'templates/header.phtml' );

		if ( count( $this->_errors ) ) {
			include $this->path( 'templates/connection/error.phtml' );
		} else {
			include $this->path( 'templates/connection/form.phtml' );
		}

		include $this->path( 'templates/debugger.phtml' );

		$html = ob_get_clean();

		if ( true === $return ) {
			return $html;
		}

		echo $html; // phpcs:ignore
	}

	protected function _is_valid_token( $token ) {

		$tpm_token = get_option( 'tpm_token', null );

		return $this->decrypt( $tpm_token ) === $token;
	}

	/**
	 * Check if data is as expected
	 *
	 * @param $data array
	 *
	 * @return bool
	 */
	protected function _is_valid_data( $data ) {

		if ( ! is_array( $data ) ) {
			return false;
		}

		$keys = array_intersect( array_keys( $data ), $this->_expected_data );

		return $keys === $this->_expected_data;
	}

	/**
	 * Add a new message in list to be displayed
	 *
	 * @param string $str
	 * @param string $status
	 */
	public function push_message( $str, $status ) {

		$str = __( $str, 'thrive-dash' );

		$this->_messages[] = array(
			'message' => $str,
			'status'  => $status,
		);

		update_option( 'tpm_connection_messages', $this->_messages );
	}

	/**
	 * Process the request
	 * Validate it and sve the connection into DB
	 *
	 * @return bool
	 */
	public function process_request() {

		if ( ! empty( $_REQUEST['td_token'] ) && ! $this->_is_valid_token( base64_decode( sanitize_text_field( $_REQUEST['td_token'] ) ) ) ) {

			$this->_errors[] = __( 'Invalid token', 'thrive-dash' );

			return false;
		}

		$data = $this->_read_data();

		if ( ! $this->_is_valid_data( $data ) ) {

			$this->_errors[] = __( 'Invalid data', 'thrive-dash' );

			return false;
		}

		return $this->_save_connection( $data );
	}

	/**
	 * @param $data
	 *
	 * @return bool
	 */
	protected function _save_connection( $data ) {

		$data['status'] = static::CONNECTED;
		$this->_data    = $data;
		update_option( static::NAME, $data );
		/* always delete the stored license details transient when a new connection is made */
		thrive_delete_transient( TD_TTW_User_Licenses::NAME );

		return true;
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
				$data[ $key ] = ! empty( $_REQUEST[ $key ] ) ? sanitize_text_field( $_REQUEST[ $key ] ) : '';
				continue;
			}

			if ( ! empty( $_REQUEST[ $key ] ) ) {
				$data[ $key ] = base64_decode( urldecode( sanitize_text_field( $_REQUEST[ $key ] ) ) );
			}
		}

		return $data;
	}

	public function get_connection_data() {
		return [
			'user_id'  => $this->ttw_id,
			'ttw_auth' => $this->ttw_salt,
			'email'    => $this->ttw_email,
			'referrer' => base64_encode( get_site_url() ),
		];
	}
}
