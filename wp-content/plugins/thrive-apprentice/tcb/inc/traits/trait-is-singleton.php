<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace TCB\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Trait Is_Singleton
 *
 * @package TCB\Traits
 */
trait Is_Singleton {

	private static $_instance;

	/**
	 * General singleton implementation for getting class instances that also require an id
	 *
	 * @param int $id
	 *
	 * @return static
	 */
	public static function get_instance_with_id( $id = 0 ) {
		/* if we don't have any instance or when we send an id that is not the same as the previous one, we create a new instance */
		if ( empty( static::$_instance ) || is_wp_error( $id ) || ( ! empty( $id ) && static::$_instance->ID !== $id ) ) {
			static::$_instance = new static( $id );
		}

		return static::$_instance;
	}

	/**
	 * General singleton implementation for getting a class instance
	 *
	 * @return static
	 */
	public static function get_instance() {
		if ( empty( static::$_instance ) ) {
			static::$_instance = new static();
		}

		return static::$_instance;
	}
}
