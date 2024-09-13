<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Trait TD_Singleton
 */
trait TD_Singleton {

	protected static $_instance;

	/**
	 * General singleton implementation for class instance
	 *
	 * @return static
	 */
	public static function get_instance() {
		if ( empty( static::$_instance ) ) {
			static::$_instance = new self();
		}

		return static::$_instance;
	}

	/**
	 * Avoid cloning
	 */
	protected function __clone() {
	}
}
