<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\ConditionalDisplay\Fields\User;

use TCB\ConditionalDisplay\Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class User_Role extends Field {
	/**
	 * @return string
	 */
	public static function get_entity() {
		return 'user_data';
	}

	/**
	 * @return string
	 */
	public static function get_key() {
		return 'user_role';
	}

	public static function get_label() {
		return esc_html__( 'Role', 'thrive-cb' );
	}

	public static function get_conditions() {
		return [ 'checkbox' ];
	}

	public function get_value( $user_data ) {
		return empty( $user_data ) ? '' : $user_data->roles;
	}

	public static function get_options( $selected_values = [], $search = '' ) {
		$roles = [];

		if ( ! function_exists( 'get_editable_roles' ) ) {
			require_once ABSPATH . '/wp-admin/includes/user.php';
		}
		foreach ( get_editable_roles() as $key => $role ) {
			$roles[ $key ] = [
				'label' => $role['name'],
				'value' => $key,
			];
		}

		return $roles;
	}

	/**
	 * Determines the display order in the modal field select
	 *
	 * @return int
	 */
	public static function get_display_order() {
		return 5;
	}
}
