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

class User_Id extends Field {
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
		return 'user_id';
	}

	public static function get_label() {
		return esc_html__( 'Username', 'thrive-cb' );
	}

	public static function get_conditions() {
		return [ 'autocomplete' ];
	}

	public function get_value( $user_data ) {
		return empty( $user_data ) ? '' : $user_data->ID;
	}

	public static function get_options( $selected_values = [], $searched_keyword = '' ) {
		$users = [];

		foreach ( get_users() as $user ) {
			if ( static::filter_options( $user->ID, $user->data->user_login, $selected_values, $searched_keyword ) ) {
				$users[] = [
					'value' => (string) $user->ID,
					'label' => $user->data->user_login,
				];
			}
		}

		return $users;
	}

	/**
	 * @return string
	 */
	public static function get_placeholder_text() {
		return esc_html__( 'Search users', 'thrive-cb' );
	}

	/**
	 * Determines the display order in the modal field select
	 *
	 * @return int
	 */
	public static function get_display_order() {
		return 20;
	}
}
