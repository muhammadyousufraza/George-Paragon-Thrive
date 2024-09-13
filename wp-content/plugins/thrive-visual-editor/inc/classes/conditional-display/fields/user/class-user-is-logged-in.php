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

class User_Is_Logged_In extends Field {
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
		return 'is_logged_in';
	}

	public static function get_label() {
		return esc_html__( 'Is logged in', 'thrive-cb' );
	}

	public static function get_conditions() {
		return [];
	}

	public function get_value( $user_data ) {
		return ! empty( $user_data );
	}

	public static function is_boolean() {
		return true;
	}

	/**
	 * Determines the display order in the modal field select
	 *
	 * @return int
	 */
	public static function get_display_order() {
		return 0;
	}
}
