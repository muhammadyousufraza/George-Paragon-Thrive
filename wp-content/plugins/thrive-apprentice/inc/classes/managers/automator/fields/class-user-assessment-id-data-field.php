<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class User_Assessment_ID_Data_Field
 */
class User_Assessment_ID_Data_Field extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'User Assessment Id';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by user assessment id';
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
		return [];
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_id() {
		return 'user_assessment_id';
	}

	public static function get_supported_filters() {
		return [ 'autocomplete' ];
	}

	public static function get_validators() {
		return [ 'required' ];
	}

	public static function get_field_value_type() {
		return static::TYPE_NUMBER;
	}

	public static function get_dummy_value() {
		return 9;
	}
}
