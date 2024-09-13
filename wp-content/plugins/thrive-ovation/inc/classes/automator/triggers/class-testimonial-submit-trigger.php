<?php

namespace TVO\Automator;

use Thrive\Automator\Items\Data_Object;
use Thrive\Automator\Items\Trigger;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Testimonial_Submit extends Trigger {

	/**
	 * Get the trigger identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'thrive/testimonial_submit';
	}

	/**
	 * Get the trigger hook
	 *
	 * @return string
	 */
	public static function get_wp_hook() {
		return 'thrive_ovation_testimonial_submit';
	}

	/**
	 * Get the trigger provided params
	 *
	 * @return array
	 */
	public static function get_provided_data_objects() {
		return [ 'testimonial_data', 'user_data', 'email_data' ];
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
	 * Get the id of the app to which the hook belongs
	 *
	 * @return string
	 */
	public static function get_app_id() {
		return Ovation_App::get_id();
	}

	/**
	 * Get the trigger name
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'User leaves a testimonial';
	}

	/**
	 * Get the trigger description
	 *
	 * @return string
	 */
	public static function get_description() {
		return 'This trigger will be fired when a user/guest submits a testimonial in a Thrive Ovation testimonial capture form';
	}

	/**
	 * Get the trigger logo
	 *
	 * @return string
	 */
	public static function get_image() {
		return 'tap-ovation-logo';
	}

	public function process_params( $params = array() ) {
		$data = array();

		if ( ! empty( $params[0] ) ) {

			$data_object_classes = Data_Object::get();

			list ( $testimonial, $user ) = $params;

			$data['testimonial_data'] = empty( $data_object_classes['testimonial_data'] ) ? $testimonial : new $data_object_classes['testimonial_data']( $testimonial );
			$data['user_data']        = empty( $data_object_classes['user_data'] ) || ! is_a( $user, 'WP_User' ) ? null : new $data_object_classes['user_data']( $user );
			$data['email_data']       = empty( $data_object_classes['email_data'] ) ? null : new $data_object_classes['email_data']( [ 'email' => $testimonial['testimonial_author_email'] ] );
		}

		return $data;
	}
}
