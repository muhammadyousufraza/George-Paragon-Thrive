<?php

namespace Thrive\Automator\Items;

use Thrive\Automator\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Wordpress_Post_Updated extends Trigger {

	/**
	 * Get the trigger identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'wordpress/postupdated';
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
		return 'tap_post_updated';
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
		return 3;
	}

	/**
	 * Get the trigger name
	 *
	 * @return string
	 */
	public static function get_name() {
		return __( 'Post is updated', 'thrive-automator' );
	}

	/**
	 * Get the trigger description
	 *
	 * @return string
	 */
	public static function get_description() {
		return __( 'Triggers when a published post is edited and the changes are saved. Any user-based actions will be performed on the author of the post', 'thrive-automator' );
	}

	/**
	 * Get the trigger logo
	 *
	 * @return string
	 */
	public static function get_image() {
		return 'tap-wordpress-logo';
	}

	/**
	 * @param array $params
	 *
	 * @return array
	 */
	public function process_params( $params = [] ) {
		$data = [];

		if ( ! empty( $params[0] ) ) {
			/* user id is the second parameter so we send it directly */
			$data[ Post_Data::get_id() ] = new Post_Data( $params[0] );

			if ( ! empty( $data[ Post_Data::get_id() ] ) ) {
				$data[ User_Data::get_id() ] = new User_Data( get_current_user_id() );
			}
		}

		return $data;
	}
}
