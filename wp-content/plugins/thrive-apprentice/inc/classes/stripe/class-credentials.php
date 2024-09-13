<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Stripe;

use Random\RandomException;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Credentials
 *
 * This class provides methods for handling Stripe credentials.
 */
class Credentials {

	/**
	 * Constants for option keys.
	 */
	const LIVE_SECRET_KEY_OPTION      = 'tva_stripe_live_secret_key';
	const LIVE_PUBLISHABLE_KEY_OPTION = 'tva_stripe_live_publishable_key';
	const TEST_SECRET_KEY_OPTION      = 'tva_stripe_test_secret_key';
	const TEST_PUBLISHABLE_KEY_OPTION = 'tva_stripe_test_publishable_key';
	const CREDENTIALS_ENDPOINT_OPTION = 'tva_stripe_credentials_endpoint';
	const STATE_OPTION                = 'tva_stripe_connection_state';
	const ACCOUNT_OPTION              = 'tva_stripe_user_id';

	/**
	 * Get the secret key.
	 *
	 * @param bool $live_mode Whether to get the live secret key or the test secret key.
	 *
	 * @return string The secret key.
	 */
	public static function get_secret_key( $live_mode = true ) {
		return get_option( $live_mode ? static::LIVE_SECRET_KEY_OPTION : static::TEST_SECRET_KEY_OPTION, '' );
	}

	/**
	 * Get the publishable key.
	 *
	 * @param bool $live_mode Whether to get the live publishable key or the test publishable key.
	 *
	 * @return string The publishable key.
	 */
	public static function get_publishable_key( $live_mode = true ) {
		return get_option( $live_mode ? static::LIVE_PUBLISHABLE_KEY_OPTION : static::TEST_PUBLISHABLE_KEY_OPTION, '' );
	}

	/**
	 * Save the secret key.
	 *
	 * @param string $secret_key The secret key to save.
	 * @param bool   $live_mode  Whether to save the live secret key or the test secret key.
	 */
	public static function save_secret_key( $secret_key, $live_mode = true ) {
		update_option( $live_mode ? static::LIVE_SECRET_KEY_OPTION : static::TEST_SECRET_KEY_OPTION, $secret_key, false );
	}

	/**
	 * Save the publishable key.
	 *
	 * @param string $key       The publishable key to save.
	 * @param bool   $live_mode Whether to save the live publishable key or the test publishable key.
	 */
	public static function save_publishable_key( $key, $live_mode = true ) {
		update_option( $live_mode ? static::LIVE_PUBLISHABLE_KEY_OPTION : static::TEST_PUBLISHABLE_KEY_OPTION, $key, false );
	}

	/**
	 * Get the account ID.
	 *
	 * @return string The account ID.
	 */
	public static function get_account_id() {
		return Hooks::is_legacy() ? Connection::get_instance()->get_account_id() : get_option( static::ACCOUNT_OPTION, '' );
	}

	/**
	 * Save the account ID.
	 *
	 * @param string $id The account ID to save.
	 */
	public static function save_account_id( $id ) {
		update_option( static::ACCOUNT_OPTION, $id, false );
	}

	/**
	 * Get the credentials endpoint.
	 *
	 * @return string The credentials endpoint.
	 */
	public static function get_credentials_endpoint() {
		$endpoint = get_option( static::CREDENTIALS_ENDPOINT_OPTION, '' );

		if ( ! $endpoint ) {
			$endpoint = uniqid( 'tva-webhook-' );
			update_option( static::CREDENTIALS_ENDPOINT_OPTION, $endpoint, false );
		}

		return $endpoint;
	}

	/**
	 * Generate a state.
	 *
	 * @return string The generated state.
	 * @throws RandomException
	 */
	public static function generate_state() {
		$pad = substr( uniqid( 'stripe-', true ), 0, 20 );

		return str_pad( bin2hex( random_bytes( 16 ) ), 80, $pad, STR_PAD_BOTH );
	}

	/**
	 * Get the state.
	 *
	 * @return string The state.
	 * @throws RandomException
	 */
	public static function get_state() {
		$state = get_option( self::STATE_OPTION );
		if ( ! $state ) {
			$state = static::generate_state();
			update_option( static::STATE_OPTION, $state, false );
		}

		return $state;
	}

	/**
	 * Delete the state.
	 */
	public static function delete_state() {
		delete_option( static::STATE_OPTION );
	}

	/**
	 * Save the credentials.
	 *
	 * @param string $secret_key           The secret key.
	 * @param string $publishable_key      The publishable key.
	 * @param string $stripe_user_id       The account ID.
	 * @param string $test_secret_key      The test secret key.
	 * @param string $test_publishable_key The test publishable key.
	 */
	public static function save_credentials( $secret_key, $publishable_key, $stripe_user_id, $test_secret_key, $test_publishable_key ) {
		static::save_account_id( $stripe_user_id );
		static::save_secret_key( $secret_key );
		static::save_secret_key( $test_secret_key, false );
		static::save_publishable_key( $publishable_key );
		static::save_publishable_key( $test_publishable_key, false );
		static::delete_state();
		update_option( Hooks::STRIPE_VERSION_META, 'v2' );
		delete_option( Connection::ACCOUNT_OPTION );
		Request::clear_cache();
		$connection = Connection_V2::get_instance();
		$response   = $connection->ensure_endpoint();

		return $response['success'] ? $connection->ensure_endpoint( false ) : $response;
	}


	/**
	 * Disconnect the Stripe account.
	 *
	 * This method deletes all the saved credentials and the state.
	 */
	public static function disconnect() {
		static::delete_state();
		delete_option( static::ACCOUNT_OPTION );
		delete_option( static::LIVE_SECRET_KEY_OPTION );
		delete_option( static::LIVE_PUBLISHABLE_KEY_OPTION );
		delete_option( static::TEST_SECRET_KEY_OPTION );
		delete_option( static::TEST_PUBLISHABLE_KEY_OPTION );
	}
}
