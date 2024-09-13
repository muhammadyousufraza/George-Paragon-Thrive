<?php

namespace Thrive\Automator\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Wordpress_Post_Publish extends Trigger {

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

	/**
	 * Get the trigger identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'wordpress/postpublish';
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
		return 'tap_post_publish';
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
		return __( 'Post is published', 'thrive-automator' );
	}

	/**
	 * Get the trigger description
	 *
	 * @return string
	 */
	public static function get_description() {
		return __( 'Triggers when a post is published. Any user-based actions will be performed on the author of the post.', 'thrive-automator' );
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
