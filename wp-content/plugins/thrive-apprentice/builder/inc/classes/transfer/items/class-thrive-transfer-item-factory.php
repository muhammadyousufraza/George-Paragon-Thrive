<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Transfer_Item_Factory
 */
class Thrive_Transfer_Item_Factory {
	/**
	 * Build an instance for an item class
	 *
	 * @param string $type
	 *
	 * @return Thrive_Transfer_Base
	 * @throws Exception
	 */
	public static function build( $type, $controller ) {
		if ( empty( $type ) ) {
			throw new Exception( 'Please provide an element type' );
		}

		$class_name = 'Thrive_Transfer_' . ucfirst( $type );

		if ( class_exists( $class_name, false ) ) {
			$instance = new $class_name( $controller );
		} else {
			throw new Exception( 'Invalid element type' );
		}

		return $instance;
	}
}
