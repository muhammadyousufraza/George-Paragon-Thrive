<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\ConditionalDisplay\Entities;

use TCB\ConditionalDisplay\Entity;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class User extends Entity {
	/**
	 * @return string
	 */
	public static function get_key() {
		return 'user_data';
	}

	public static function get_label() {
		return esc_html__( 'User', 'thrive-cb' );
	}

	public function create_object( $param ) {
		$user_id = get_current_user_id();

		return get_userdata( $user_id );
	}

	/**
	 * Determines the display order in the modal entity select
	 *
	 * @return int
	 */
	public static function get_display_order() {
		return 0;
	}
}
