<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ultimatum
 */

namespace TU\Automator;
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Form_Email_Field
 */
class Campaign_User_Email_Data_Field extends \Thrive\Automator\Items\Data_Field {

	/**
	 * Field name
	 */
	public static function get_name() {
		return 'User email';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return __( 'Email field, provided by the campaign trigger', 'thrive-ult' );
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return __( 'Filter by email from campaign data', 'thrive-ult' );
	}

	public static function get_id() {
		return 'campaign_user_email';
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

