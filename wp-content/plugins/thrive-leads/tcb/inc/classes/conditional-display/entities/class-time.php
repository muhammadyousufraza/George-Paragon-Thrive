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

class Time extends Entity {

	/**
	 * @return string
	 */
	public static function get_key() {
		return 'time_data';
	}

	public static function get_label() {
		return esc_html__( 'Time and date', 'thrive-cb' );
	}

	public function create_object( $param ) {
		return '';
	}

	/**
	 * Determines the display order in the modal entity select
	 *
	 * @return int
	 */
	public static function get_display_order() {
		return 15;
	}
}
