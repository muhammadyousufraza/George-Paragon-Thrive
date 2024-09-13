<?php

namespace TQB\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Quiz_Number_Result_Field
 */
class Quiz_Number_Result_Data_Field extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Quiz score (if numeric)';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Target by numeric result attained';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_id() {
		return 'quiz_number_result';
	}

	public static function get_supported_filters() {
		return array( 'number_comparison' );
	}

	/**
	 * Get the type of the field value
	 *
	 * @return string
	 */
	public static function get_field_value_type() {
		return static::TYPE_NUMBER;
	}

	public static function get_dummy_value() {
		return 60;
	}
}
