<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\ConditionalDisplay\Fields\User;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class User_Is_Not_Logged_In extends User_Is_Logged_In {
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
		return 'is_not_logged_in';
	}

	public static function get_label() {
		return esc_html__( 'Is not logged in', 'thrive-cb' );
	}

	public function get_value( $user_data ) {
		return empty( $user_data );
	}

	/**
	 * Determines the display order in the modal field select
	 *
	 * @return int
	 */
	public static function get_display_order() {
		return 1;
	}
}
