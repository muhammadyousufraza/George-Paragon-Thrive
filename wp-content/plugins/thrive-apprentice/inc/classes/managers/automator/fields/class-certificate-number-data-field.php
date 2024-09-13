<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Data_Field;
use TVA_Course_V2;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Certificate_Number_Data_Field extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Certificate number';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by certificate number';
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
		return array();
	}

	public static function is_ajax_field() {
		return false;
	}

	public static function get_id() {
		return 'certificate_number';
	}

	public static function get_supported_filters() {
		return [ 'string_equals' ];
	}

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}
}