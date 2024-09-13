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

class Last_Logged_In extends Field {
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
		return 'last_logged_in';
	}

	public static function get_label() {
		return esc_html__( 'Last logged in', 'thrive-cb' );
	}

	public static function get_conditions() {
		return [ 'date_and_time_with_intervals' ];
	}

	public function get_value( $user_data ) {
		$last_logged_in = '';

		if ( $user_data ) {
			$user_meta = get_user_meta( $user_data->ID );

			if ( isset( $user_meta['tve_last_login'] ) && is_array( $user_meta['tve_last_login'] ) ) {
				$last_logged_in = date( 'Y-m-d H:i:s', (int) $user_meta['tve_last_login'][0] );
			}
		}

		return $last_logged_in;
	}

	/**
	 * Determines the display order in the modal field select
	 *
	 * @return int
	 */
	public static function get_display_order() {
		return 10;
	}
}
