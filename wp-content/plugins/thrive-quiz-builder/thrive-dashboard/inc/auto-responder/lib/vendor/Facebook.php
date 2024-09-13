<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

require_once 'Facebook/Exception/Exception.php';

/**
 * Facebook API extension / implementation for database-stored user ids and tokens
 */
class Thrive_Dash_Api_Facebook extends Thrive_Dash_Api_Facebook_Base {

	const FB_API_OPTION = 'thrive_fb_api_persist';

	/**
	 * Allowed keys
	 *
	 * @var string[]
	 */
	protected static $keys = array( 'state', 'code', 'access_token', 'user_id' );

	/**
	 * Get the stored data as an array
	 *
	 * @return array
	 */
	private function data() {
		$data = get_option( self::FB_API_OPTION, [] );
		if ( ! is_array( $data ) ) {
			$data = [];
		}

		return $data;
	}

	/**
	 * Provides the implementations of the inherited abstract
	 * methods. The implementation uses WordPress's wp_options to maintain
	 * a store for authorization codes, user ids, CSRF states, and
	 * access tokens.
	 */

	/**
	 * {@inheritdoc}
	 *
	 * @see BaseFacebook::setPersistentData()
	 */
	protected function setPersistentData( $key, $value ) {
		if ( ! in_array( $key, self::$keys, true ) ) {
			self::errorLog( 'Unsupported key passed to setPersistentData.' );

			return;
		}

		$data = $this->data();

		$data[ $key ] = $value;
		update_option( self::FB_API_OPTION, $data );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @see BaseFacebook::getPersistentData()
	 */
	protected function getPersistentData( $key, $default = false ) {
		if ( ! in_array( $key, self::$keys, true ) ) {
			self::errorLog( 'Unsupported key passed to getPersistentData.' );

			return $default;
		}

		$data = $this->data();

		return isset( $data[ $key ] ) ? $data[ $key ] : $default;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @see BaseFacebook::clearPersistentData()
	 */
	protected function clearPersistentData( $key ) {
		if ( ! in_array( $key, self::$keys, true ) ) {
			self::errorLog( 'Unsupported key passed to clearPersistentData.' );

			return;
		}

		$data = $this->data();
		unset( $data[ $key ] );

		update_option( self::FB_API_OPTION, $data );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @see BaseFacebook::clearAllPersistentData()
	 */
	protected function clearAllPersistentData() {
		delete_option( self::FB_API_OPTION );
	}
}
