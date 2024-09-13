<?php

namespace Thrive\Automator\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Wordpress_Passreset_Link extends Trigger {
	/**
	 * Get the trigger identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'wordpress/passreset_link';
	}

	/**
	 * Get the trigger hook
	 *
	 * @return string
	 */
	public static function get_wp_hook() {
		return 'retrieve_password_key';
	}

	public static function get_app_id() {
		return Wordpress_App::get_id();
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
		return __( 'Password reset link sent', 'thrive-automator' );
	}

	/**
	 * Get the trigger description
	 *
	 * @return string
	 */
	public static function get_description() {
		return __( 'This trigger will be fired whenever the password reset link is sent for a user', 'thrive-automator' );
	}

	/**
	 * Get the trigger logo
	 *
	 * @return string
	 */
	public static function get_image() {
		return 'tap-wordpress-logo';
	}

	public function process_params( $params = [] ) {

		$data = [];
		if ( ! empty( $params ) && ! empty( static::get_provided_data_objects() ) ) {
			$data_object_classes = Data_Object::get();
			$object_key          = array_values( static::get_provided_data_objects() )[0];
			$param               = get_user_by( 'login', $params[0] );

			$data[ $object_key ] = empty( $data_object_classes[ $object_key ] ) ? $param : new $data_object_classes[ $object_key ]( $param, $this->get_automation_id() );

		}

		return $data;
	}

}
