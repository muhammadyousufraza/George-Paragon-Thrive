<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Dashboard\Automator;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

use Thrive\Automator\Items\Action_Field;

class Facebook_Custom_Options_Field extends Action_Field {

	public static function get_id() {
		return 'fb/custom_options';
	}

	public static function get_name() {
		return __( 'Facebook custom options', 'thrive-dash' );
	}

	public static function get_description() {
		return '';
	}

	public static function get_placeholder() {
		return '';
	}

	public static function get_type() {
		return 'key_value_pair';
	}

	public static function get_preview_template() {
		return '';
	}

	public static function allow_dynamic_data() {
		return true;
	}

	public static function get_validators() {
		return [ 'key_value_pair' ];
	}
}
