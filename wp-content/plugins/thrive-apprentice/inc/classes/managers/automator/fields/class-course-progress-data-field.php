<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Course_progress_Data_Field
 */
class Course_Progress_Data_Field extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Percentage of course completed';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by completed percentage';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_id() {
		return 'course_progress';
	}

	public static function get_supported_filters() {
		return [ 'number_comparison' ];
	}

	public static function get_field_value_type() {
		return static::TYPE_NUMBER;
	}

	public static function get_dummy_value() {
		return '50';
	}
}
