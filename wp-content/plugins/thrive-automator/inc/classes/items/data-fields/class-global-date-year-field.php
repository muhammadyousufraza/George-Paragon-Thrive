<?php

namespace Thrive\Automator\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Global_Date_Year_Field
 */
class Global_Date_Year_Field extends Global_Date_Day_Field {

	public static function get_name() {
		return __( 'Year', 'thrive-automator' );
	}

	public static function get_description() {
		return __( 'Year', 'thrive-automator' );
	}

	public static function get_placeholder() {
		return '';
	}

	public static function get_id() {
		return 'global_date_year';
	}

	public static function get_supported_filters() {
		return [ 'number_comparison' ];
	}

	public static function get_field_value_type() {
		return static::TYPE_NUMBER;
	}

	public static function value_callback() {
		return (int) current_time( 'Y' );
	}
}
