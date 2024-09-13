<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace Thrive\Automator\Items;

use WP_User;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Wordpress_Remove_User_Role extends Action {

	protected $roles;

	public static function get_id(): string {
		return 'wordpress/remove_user_role';
	}

	public static function get_name(): string {
		return __( 'Remove user role', 'thrive-automator' );
	}

	public static function get_description(): string {
		return __( 'Remove role(s) from a user', 'thrive-automator' );
	}

	public static function get_app_id(): string {
		return Wordpress_App::get_id();
	}

	public static function get_image(): string {
		return 'tap-wordpress-logo';
	}

	public static function get_required_action_fields(): array {
		return [ User_Role_Field::get_id() ];
	}

	public static function get_required_data_objects(): array {
		return [ User_Data::get_id() ];
	}

	public function prepare_data( $data = [] ) {
		if ( ! empty( $data[ User_Role_Field::get_id() ]['value'] ) ) {
			$this->roles = $data[ User_Role_Field::get_id() ]['value'];
		}
	}

	public function do_action( $data ) {
		global $automation_data;
		$user_data = $automation_data->get( User_Data::get_id() );
		if ( ! empty( $user_data ) ) {
			$wp_user_object = new WP_User( $user_data->get_value( 'user_id' ) );
			foreach ( $this->roles as $role ) {
				$wp_user_object->remove_role( $role );
			}

		}

		return true;
	}
}
