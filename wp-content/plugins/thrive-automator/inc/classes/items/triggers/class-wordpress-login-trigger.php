<?php

namespace Thrive\Automator\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Wordpress_Login extends Trigger {

	/**
	 * @param array $params
	 *
	 * @return array
	 */
	public function process_params( $params = [] ) {
		$data = [];

		if ( ! empty( $params[1] ) ) {
			/* user id is the second parameter so we send it directly */
			$data = parent::process_params( [ $params[1] ] );
		}

		return $data;
	}

	/**
	 * Get the trigger identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'wordpress/userlogin';
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
		return 'wp_login';
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
		return 2;
	}

	/**
	 * Get the trigger name
	 *
	 * @return string
	 */
	public static function get_name() {
		return __( 'User logs into account', 'thrive-automator' );
	}

	/**
	 * Get the trigger description
	 *
	 * @return string
	 */
	public static function get_description() {
		return __( 'This trigger will be fired whenever a user logs into their account through any form on your site', 'thrive-automator' );
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
