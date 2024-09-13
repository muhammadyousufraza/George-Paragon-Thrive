<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 4/15/2019
 * Time: 14:22
 */

class TVA_Cookie_Manager {
	/**
	 * @var
	 */
	protected static $_instance;

	/**
	 * TVA_Cookie_Manager constructor.
	 */
	public function __construct() {
	}

	/**
	 * @return TVA_Cookie_Manager
	 */
	public static function instance() {

		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @param null   $expire
	 * @param string $path
	 */
	public static function set_cookie( $name, $value, $expire = null, $path = '/' ) {
		$expire = $expire ? $expire : time() + ( 30 * 24 * 3600 );

		setcookie( 'tva_' . $name, $value, $expire, $path );
		$_COOKIE[ 'tva_' . $name ] = $value;
	}

	/**
	 * @param $name
	 *
	 * @return string|null
	 */
	public static function get_cookie( $name ) {
		return isset( $_COOKIE[ 'tva_' . $name ] ) ? $_COOKIE[ 'tva_' . $name ] : null;
	}

	/**
	 * Remove a cookie
	 *
	 * @param $name
	 */
	public static function remove_cookie( $name ) {
		self::set_cookie( $name, '', - 1 );
		unset( $_COOKIE[ 'tva_' . $name ] );
	}
}

