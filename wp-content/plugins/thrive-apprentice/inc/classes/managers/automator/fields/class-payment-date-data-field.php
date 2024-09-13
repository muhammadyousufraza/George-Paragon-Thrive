<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Payment_Date_Field
 */
class Payment_Date_Data_Field extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Payment date';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by payment date';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback() {
		return 'get_bundle_title_options';
	}

	public static function get_id() {
		return 'payment_date';
	}

	public static function get_supported_filters() {
		return [ 'time_date' ];
	}

	public static function get_field_value_type() {
		return static::TYPE_DATE;
	}

	public static function get_dummy_value() {
		return '2021-09-06 17:18:57';
	}
}
