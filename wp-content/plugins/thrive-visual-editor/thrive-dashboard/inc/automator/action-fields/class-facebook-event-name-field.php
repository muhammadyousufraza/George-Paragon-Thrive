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

class Facebook_Event_Name_Field extends Action_Field {

	public static function get_id() {
		return 'fb/event_name';
	}

	public static function get_name() {
		return __( 'Custom event name', 'thrive-dash' );
	}

	public static function get_description() {
		return '';
	}

	public static function get_placeholder() {
		return __( 'Enter a name for your custom event', 'thrive-dash' );
	}

	public static function get_validators() {
		return [ 'required' ];
	}

	public static function allow_dynamic_data() {
		return true;
	}

	public static function get_type() {
		return 'input';
	}

	public static function get_preview_template() {
		return __( 'Custom event name: ', 'thrive-dash' ) . '$$value';
	}
}
