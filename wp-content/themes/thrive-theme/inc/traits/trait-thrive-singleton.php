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
 * Trait Thrive_Singleton
 */
trait Thrive_Singleton {

	/**
	 * @var null instance
	 */
	private static $_instance;

	/**
	 * General singleton implementation for class instance that also requires an id
	 *
	 * @param int $id
	 *
	 * @return null
	 */
	public static function instance_with_id( $id = 0 ) {
		/* if we don't have any instance or when we send an id that it's not the same as the previous one, we create a new instance */
		if ( empty( static::$_instance ) || is_wp_error( $id ) || ( ! empty( $id ) && static::$_instance->ID !== $id ) ) {
			static::$_instance = new static( $id );
		}

		return static::$_instance;
	}

	/**
	 * General singleton implementation for class instance
	 *
	 * @return mixed
	 */
	public static function instance() {
		if ( empty( static::$_instance ) ) {
			static::$_instance = new static();
		}

		return static::$_instance;
	}
}

