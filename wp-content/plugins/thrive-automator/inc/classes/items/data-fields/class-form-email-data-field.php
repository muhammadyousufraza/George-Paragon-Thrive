<?php

namespace Thrive\Automator\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Form_Email_Field
 */
class Form_Email_Data_Field extends Data_Field {

	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Email';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return __( 'Email field, unique identifier for user', 'thrive-automator' );
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return __( 'Filter by email', 'thrive-automator' );
	}

	public static function get_id() {
		return 'email';
	}

	public static function get_supported_filters() {
		return [ 'string_equals' ];
	}

	public static function get_validators() {
		return [ 'required', 'email' ];
	}

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}

	public static function get_dummy_value() {
		return 'john_doe@fakemail.com';
	}

	public static function primary_key() {
		return 'email_data';
	}
}
