<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Assessment_Mark_Data_Field
 */
class Assessment_Mark_Data_Field extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Assessment Mark';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Target an assessment by mark';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_id() {
		return 'assessment_mark';
	}

	public static function get_supported_filters() {
		return [ 'string_ec' ];
	}

	public static function get_validators() {
		return [ 'required' ];
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}

	public static function get_dummy_value() {
		return 9;
	}
}