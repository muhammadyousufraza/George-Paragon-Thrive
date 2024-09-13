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
 * Class Tvd_Auth_Check
 */
class Tvd_Auth_Check {

	/**
	 * Tvd_Auth_Check constructor.
	 */
	public function __construct() {
		/**
		 * Actions used for handling the interim login ( login via popup in Thrive Theme Dashboard )
		 */
		add_action( 'login_footer', array( $this, 'login_footer' ) );
		add_action( 'set_logged_in_cookie', array( $this, 'set_logged_in_cookie' ), 10, 4 );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'auth_enqueue_scripts' ) );
	}

	public static function auth_enqueue_scripts() {
		tve_dash_enqueue_script( 'tvd-auth-check', TVE_DASH_URL . '/inc/auth-check/auth-check.js' );

		wp_localize_script( 'tvd-auth-check', 'tvd_auth_check', array( 'userkey' => static::generate_editor_key() ) );
	}

	/**
	 * Generate a new editor key for the user and save it
	 *
	 * @return bool|string
	 */
	public static function generate_editor_key() {
		if ( ! $id = get_current_user_id() ) {
			return false;
		}

		$key = wp_create_nonce( 'tcb_editor_key' );
		update_user_meta( $id, 'tcb_edit_key', $key );

		return $key;
	}

	/**
	 * get the current editor key for the user
	 *
	 * @param mixed $id
	 *
	 * @return bool|mixed
	 */
	public function get_user_editor_key( $id ) {
		if ( ! $id && ! ( $id = get_current_user_id() ) ) {
			return null;
		}

		return get_user_meta( $id, 'tcb_edit_key', true );
	}

	public function login_footer() {
		global $interim_login;

		if ( empty( $interim_login ) || $interim_login !== 'success' || empty( $_POST ) || empty( $_POST['tvd_auth_check_user_key'] ) || empty( $this->tvd_interim_user_id ) ) {
			return;
		}

		/**
		 * Problem: during the login POST, after login, the user does not seem to be actually available
		 */
		$user_id = $this->tvd_interim_user_id;
		/**
		 * This is used to correctly re-generate the nonce
		 */
		$_COOKIE[ LOGGED_IN_COOKIE ] = $this->tvd_interim_login_cookie;
		$user_key                    = $this->get_user_editor_key( $user_id );

		wp_set_current_user( $user_id );

		if ( $user_key === $_POST['tvd_auth_check_user_key'] ) {
			/* pass data that we need after the login auth */
			$data = apply_filters( 'tvd_auth_check_data', array(
				'rest_nonce' => wp_create_nonce( 'wp_rest' ),
			) );
			include 'handle-login.php';
		}
	}

	/**
	 * Helper function to store the actual value of the logged in cookie during the login process stared from the dashboard
	 *
	 * @param string $logged_in_cookie The logged-in cookie.
	 * @param int $expire The time the login grace period expires as a UNIX timestamp.
	 *                                 Default is 12 hours past the cookie's expiration time.
	 * @param int $expiration The time when the logged-in authentication cookie expires as a UNIX timestamp.
	 *                                 Default is 14 days from now.
	 * @param int $user_id User ID.
	 */
	public function set_logged_in_cookie( $logged_in_cookie, $expire, $expiration, $user_id ) {
		global $interim_login;
		if ( ! empty( $interim_login ) && ! empty( $_POST ) && ! empty( $_POST['tvd_auth_check_user_key'] ) ) {
			$this->tvd_interim_user_id      = $user_id;
			$this->tvd_interim_login_cookie = $logged_in_cookie;
		}
	}
}

new Tvd_Auth_Check();
