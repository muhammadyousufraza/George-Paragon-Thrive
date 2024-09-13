<?php

namespace Thrive\Automator\Items;

use Thrive\Automator\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Global_Date_Day_Field
 */
class Global_Date_Day_Field extends Data_Field {

	public static function get_name() {
		return __( 'Day', 'thrive-automator' );
	}

	public static function get_description() {
		return __( 'Day', 'thrive-automator' );
	}

	public static function get_placeholder() {
		return '';
	}

	public static function get_id() {
		return 'global_date_day';
	}

	public static function get_supported_filters() {
		return [ 'checkbox' ];
	}

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback() {
		return Utils::get_day_options();
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function value_callback() {
		return (int) current_time( 'w' );
	}

	public static function get_dummy_value() {
		return static::value_callback();
	}
}
