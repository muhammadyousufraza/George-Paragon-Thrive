<?php

namespace Thrive\Automator\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Wordpress_Profile_Update extends Trigger {
	/**
	 * Get the trigger identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'wordpress/update';
	}

	public static function get_app_id() {
		return Wordpress_App::get_id();
	}

	/**
	 * Get the trigger hook
	 *
	 * @return string
	 */
	public static function get_wp_hook() {
		return 'tap_user_updates_own_profile';
	}

	/**
	 * Get the trigger provided params
	 *
	 * @return array
	 */
	public static function get_provided_data_objects() {
		return [ User_Data::get_id() ];
	}

	/**
	 * Get the number of params
	 *
	 * @return int
	 */
	public static function get_hook_params_number() {
		return 1;
	}


	/**
	 * Get the trigger name
	 *
	 * @return string
	 */
	public static function get_name() {
		return __( 'User profile updated', 'thrive-automator' );
	}

	/**
	 * Get the trigger description
	 *
	 * @return string
	 */
	public static function get_description() {
		return __( 'This trigger will be fired whenever a user updates their profile', 'thrive-automator' );
	}

	/**
	 * Get the trigger logo
	 *
	 * @return string
	 */
	public static function get_image() {
		return 'tap-wordpress-logo';
	}

}
