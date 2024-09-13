<?php

namespace Thrive\Automator\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Global_Date_Time_Field
 */
class Global_Date_Time_Field extends Global_Date_Field {

	public static function get_name() {
		return __( 'Time', 'thrive-automator' );
	}

	public static function get_description() {
		return __( 'Time', 'thrive-automator' );
	}

	public static function get_placeholder() {
		return '';
	}

	public static function get_id() {
		return 'global_date_time';
	}

	public static function get_supported_filters() {
		return [ 'time' ];
	}

	public static function get_field_value_type() {
		return static::TYPE_DATE;
	}

	public static function value_callback() {
		return current_time( 'H:i:s' );
	}
}
