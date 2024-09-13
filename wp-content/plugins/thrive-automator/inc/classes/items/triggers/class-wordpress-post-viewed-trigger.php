<?php

namespace Thrive\Automator\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Wordpress_Post_Viewed extends Trigger {

	/**
	 * Get the trigger identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'wordpress/postview';
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
		return 'tap_post_view';
	}

	/**
	 * Get the trigger provided params
	 *
	 * @return array
	 */
	public static function get_provided_data_objects() {
		return [ Post_Data::get_id(), User_Data::get_id() ];
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
		return __( 'Post is viewed', 'thrive-automator' );
	}

	/**
	 * Get the trigger description
	 *
	 * @return string
	 */
	public static function get_description() {
		return __( 'Triggers when a post is viewed', 'thrive-automator' );
	}

	/**
	 * Get the trigger logo
	 *
	 * @return string
	 */
	public static function get_image() {
		return 'tap-wordpress-logo';
	}

	public static function get_required_trigger_fields() {
		return [ 'post_type_trigger_field' => [ 'post_id_trigger_field' ] ];
	}

	/**
	 * @param array $params
	 *
	 * @return array|bool
	 */
	public function process_params( $params = [] ) {

		if ( current_user_can( 'manage_options' ) ) {
			return false;
		}
		$data = [];
		if ( ! empty( $params[0] ) ) {

			/* user id is the second parameter so we send it directly */
			$data[ Post_Data::get_id() ] = new Post_Data( $params[0] );
			if ( ! in_array( $data[ Post_Data::get_id() ]->get_value( 'wp_post_id' ), $this->data['post_type_trigger_field']['subfield']['post_id_trigger_field']['value'], false ) ) {
				return false;
			}
			if ( ! empty( $data[ Post_Data::get_id() ] ) ) {
				$data[ User_Data::get_id() ] = new User_Data( get_current_user_id() );
			}
		}

		return $data;
	}

	public static function hidden() {
		return true;
	}
}
