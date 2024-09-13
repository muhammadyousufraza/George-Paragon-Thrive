<?php

namespace Thrive\Automator\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Global_Date_Field
 */
class Global_Date_Field extends Data_Field {

	/**
	 * Field name
	 */
	public static function get_name() {
		return __( 'Date', 'thrive-automator' );
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return __( 'Date', 'thrive-automator' );
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_id() {
		return 'global_date';
	}

	public static function get_supported_filters() {
		return [ 'time_date' ];
	}

	public static function get_field_value_type() {
		return static::TYPE_DATE;
	}

	public static function value_callback() {
		return current_time( 'Y-m-d H:i:s' );
	}

	public static function get_dummy_value() {
		return static::value_callback();
	}
}
