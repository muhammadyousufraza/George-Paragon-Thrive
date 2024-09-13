<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace Thrive\Automator\Items;
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Wordpress_Comment extends Trigger {

	public static function get_id() {
		return 'wordpress/comment';
	}

	public static function get_wp_hook() {
		return 'comment_post';
	}

	public static function get_provided_data_objects() {
		return [ Comment_Data::get_id(), User_Data::get_id() ];
	}

	public static function get_hook_params_number() {
		return 3;
	}

	public static function get_name() {
		return __( 'User leaves comment', 'thrive-automator' );
	}

	public static function get_description() {
		return __( 'This trigger will be fired whenever a user leaves a comment on the site', 'thrive-automator' );
	}

	public static function get_image() {
		return 'tap-wordpress-logo';
	}

	public static function get_app_id() {
		return Wordpress_App::get_id();
	}

	/**
	 * Override default method so we manually init user data if we can match the form's email with an existing user
	 *
	 * @param array $params
	 *
	 * @return array
	 * @see Automation::start()
	 */
	public function process_params( $params = array() ) {

		$data_objects = array();

		if ( ! empty( $params ) ) {
			$comment_data = $params[0];
			/* get all registered data objects and see which ones we use for this trigger */
			$data_object_classes = Data_Object::get();

			if ( empty( $data_object_classes['comment_data'] ) ) {
				/* if we don't have a class that parses the current param, we just leave the value as it is */
				$data_objects['comment_data'] = $comment_data;
			} else {
				/* when a data object is available for the current parameter key, we create an instance that will handle the data */
				$data_objects['comment_data'] = new $data_object_classes['comment_data']( $comment_data, $this->get_automation_id() );
			}

			$user_data = null;
			/**
			 * try to match email with existing user
			 */
			if ( ! empty( $params[2] ) && ! empty( $params[2]['comment_author_email'] ) ) {
				$user_data = get_user_by( 'email', $params[2]['comment_author_email'] );
			}
			if ( empty( $data_object_classes['user_data'] ) ) {
				$data_objects['user_data'] = $user_data;
			} else {
				$data_objects['user_data'] = new $data_object_classes['user_data']( $user_data, $this->get_automation_id() );
			}
		}

		return $data_objects;
	}
}
