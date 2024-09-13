<?php
/**
 * Thrive Themes - https://thrivethemes.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class Thrive_AB_Event_Manager
 */
class Thrive_AB_Cookie_Manager {

	protected static $_instance;

	public function __construct() {
	}

	public static function instance() {

		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public static function set_cookie( $test_id = null, $page_id = null, $variation_id = null, $type = 1 ) {
		setcookie( 'top-variation-' . $type . '-' . $test_id . '-' . $page_id, $variation_id, time() + ( 30 * 24 * 3600 ), '/' );
		$_COOKIE[ 'top-variation-' . $type . '-' . $test_id . '-' . $page_id ] = $variation_id;
	}

	public static function get_cookie( $test_id = null, $page_id = null, $type = 1 ) {
		return isset( $_COOKIE[ 'top-variation-' . $type . '-' . $test_id . '-' . $page_id ] ) ? $_COOKIE[ 'top-variation-' . $type . '-' . $test_id . '-' . $page_id ] : null;
	}

	public static function set_impression_cookie( $test_item, $sec = 5 ) {
		$cookie_name = 'top-impression-' . $test_item;

		$sec = intval( $sec );

		setcookie( $cookie_name, $test_item, time() + $sec, '/' );
		$_COOKIE[ $cookie_name ] = $test_item;
	}

	public static function get_impression_cookie( $test_item ) {

		$cookie_name = 'top-impression-' . $test_item;

		return isset( $_COOKIE[ $cookie_name ] ) ? $_COOKIE[ $cookie_name ] : null;
	}
}

return Thrive_AB_Cookie_Manager::instance();
