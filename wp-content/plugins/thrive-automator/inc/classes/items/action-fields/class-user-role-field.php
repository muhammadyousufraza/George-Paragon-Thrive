<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace Thrive\Automator\Items;
use Thrive\Automator\Utils;
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class User_Role_Field extends Action_Field {
	public static function get_name() {
		return __( 'User role', 'thrive-automator' );
	}

	public static function get_description() {
		return __( 'WordPress user role', 'thrive-automator' );
	}

	public static function get_placeholder() {
		return __( 'WordPress user role', 'thrive-automator' );
	}

	public static function get_id() {
		return 'user_role';
	}

	public static function get_type() {
		return Utils::FIELD_TYPE_AUTOCOMPLETE;
	}

	public static function get_preview_template() {
		return __( 'Role:', 'thrive-automator' ) . ' $$value';
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback( $action_id, $action_data ) {
		/* get_editable_roles only loaded in the admin sections */
		if ( ! function_exists( 'get_editable_roles' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/user.php' );
		}
		$user_roles = get_editable_roles();

		unset( $user_roles['administrator'], $user_roles['editor'] );

		$roles = [];
		foreach ( $user_roles as $key => $role ) {
			$roles[ $key ] = [
				'label' => $role['name'],
				'id'    => $key,
			];
		}

		return $roles;
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_validators() {
		return [ 'required' ];
	}

	public static function get_default_value() {
		return 'subscriber';
	}
}
