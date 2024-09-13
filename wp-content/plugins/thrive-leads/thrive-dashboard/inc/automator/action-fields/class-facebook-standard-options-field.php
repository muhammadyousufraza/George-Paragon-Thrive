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

class Facebook_Standard_Options_Field extends Action_Field {

	public static function get_id() {
		return 'fb/standard_options';
	}

	public static function get_name() {
		return __( 'Facebook standard options', 'thrive-dash' );
	}

	public static function get_description() {
		return '';
	}

	public static function get_placeholder() {
		return '';
	}

	public static function get_type() {
		return 'mapping_pair';
	}

	public static function get_field_values( $filters = [] ) {
		return array_merge( Facebook::get_standard_options(), Facebook::get_event_options(), Facebook::get_user_options() );
	}

	public static function allow_dynamic_data() {
		return true;
	}

	public static function get_preview_template() {
		return '';
	}

	public static function get_validators() {
		return [ 'key_value_pair' ];
	}
}
