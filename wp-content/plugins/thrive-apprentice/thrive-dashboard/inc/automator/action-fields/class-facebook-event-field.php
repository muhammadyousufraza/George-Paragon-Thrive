<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Action_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Facebook_Event_Field extends Action_Field {

	public static function get_name() {
		return __( 'Event', 'thrive-dash' );
	}

	public static function get_description() {
		return '';
	}

	public static function get_placeholder() {
		return __( 'Choose a standard event name', 'thrive-dash' );
	}

	public static function get_id() {
		return 'fb/event';
	}

	public static function get_type() {
		return 'select';
	}

	public static function get_field_values( $filters = [] ) {
		return Facebook::get_event_types();
	}

	public static function get_preview_template() {
		return 'Event: $$value';
	}

	public static function get_validators() {
		return [ 'required' ];
	}
}
