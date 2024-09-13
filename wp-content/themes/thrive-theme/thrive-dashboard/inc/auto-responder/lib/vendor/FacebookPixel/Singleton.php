<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVD\Autoresponder\FacebookPixel;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

abstract class Singleton {
	/**
	 * @var array
	 */
	protected static $instances = array();


	private function __construct() {
	}

	public static function getInstance() {
		$fqn = get_called_class();
		if ( ! array_key_exists( $fqn, static::$instances ) ) {
			static::$instances[ $fqn ] = new static();
		}

		return static::$instances[ $fqn ];
	}
}
