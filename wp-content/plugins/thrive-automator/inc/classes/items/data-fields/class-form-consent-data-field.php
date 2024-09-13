<?php

namespace Thrive\Automator\Items;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Form_Consent_Field
 */
class Form_Consent_Data_Field extends Data_Field {

	/**
	 * Field name
	 */
	public static function get_name() {
		return __( 'User consent', 'thrive-automator' );
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return __( 'Consent from the form data submitted by the user', 'thrive-automator' );
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return __( 'Filter by user consent', 'thrive-automator' );
	}

	public static function get_id() {
		return 'user_consent';
	}

	public static function get_supported_filters() {
		return [ 'boolean' ];
	}

	public static function get_field_value_type() {
		return static::TYPE_BOOLEAN;
	}

	public static function get_dummy_value() {
		return 'TRUE';
	}
}
