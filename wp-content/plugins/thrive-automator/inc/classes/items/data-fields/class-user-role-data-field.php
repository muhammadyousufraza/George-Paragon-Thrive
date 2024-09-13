<?php

namespace Thrive\Automator\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class User_Role_Field
 */
class User_Role_Data_Field extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return __( 'User role', 'thrive-automator' );
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return __( 'Filter by WordPress user role', 'thrive-automator' );
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return __( 'Filter by user consent', 'thrive-automator' );
	}

	public static function get_id() {
		return 'user_role';
	}

	public static function get_supported_filters() {
		return [ 'checkbox' ];
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback() {
		$roles = [];
		if ( ! function_exists( 'get_editable_roles' ) ) {
			require_once ABSPATH . '/wp-admin/includes/user.php';
		}
		foreach ( get_editable_roles() as $key => $role ) {
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

	public static function get_field_value_type() {
		return static::TYPE_ARRAY;
	}

	public static function get_dummy_value() {
		return 'subscriber';
	}

	public static function get_validators() {
		return [ 'required' ];
	}
}
