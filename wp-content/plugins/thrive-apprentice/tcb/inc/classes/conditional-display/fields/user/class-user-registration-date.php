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

class User_Registration_Date extends Field {
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
		return 'user_registration_date';
	}

	public static function get_label() {
		return esc_html__( 'Registration date', 'thrive-cb' );
	}

	public static function get_conditions() {
		return [ 'date_and_time_with_intervals' ];
	}

	public function get_value( $user_data ) {
		return empty( $user_data ) ? '' : $user_data->user_registered;
	}

	/**
	 * Determines the display order in the modal field select
	 *
	 * @return int
	 */
	public static function get_display_order() {
		return 15;
	}
}
