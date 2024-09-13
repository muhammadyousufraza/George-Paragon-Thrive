<?php

namespace Thrive\Automator\Items;

use Thrive\Automator\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Global_Date_Month_Field
 */
class Global_Date_Month_Field extends Global_Date_Day_Field {

	public static function get_name() {
		return __( 'Month', 'thrive-automator' );
	}

	public static function get_description() {
		return __( 'Month', 'thrive-automator' );
	}

	public static function get_id() {
		return 'global_date_month';
	}

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback() {
		return Utils::get_month_options();
	}

	public static function value_callback() {
		return (int) current_time( 'n' );
	}
}
