<?php

namespace Thrive\Automator\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class User_Email_Field
 */
class User_Email_Data_Field extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return __( 'User email', 'thrive-automator' );
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return __( 'Filter by WordPress user email', 'thrive-automator' );
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_id() {
		return 'user_email';
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
}
