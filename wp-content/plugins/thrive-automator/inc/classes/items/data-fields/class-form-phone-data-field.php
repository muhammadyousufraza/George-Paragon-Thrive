<?php

namespace Thrive\Automator\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Form_Phone_Field
 */
class Form_Phone_Data_Field extends Data_Field {

	/**
	 * Field name
	 */
	public static function get_name() {
		return __( 'Phone', 'thrive-automator' );
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return __( 'Phone from the form data submitted by the user', 'thrive-automator' );
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return __( 'Filter by phone', 'thrive-automator' );
	}

	public static function get_id() {
		return 'phone';
	}

	public static function get_supported_filters() {
		return [ 'string_ec' ];
	}

	public static function get_validators() {
		return [ 'phone' ];
	}

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}

	public static function get_dummy_value() {
		return '07906578743';
	}

}
