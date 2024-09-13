<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Order_Customer_User_Agent
 */
class Woo_Order_Customer_User_Agent extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Customer user agent';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by customer user agent';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_dummy_value() {
		return 'Mozilla/5.0';
	}

	public static function get_id() {
		return 'customer_user_agent';
	}

	public static function get_supported_filters() {
		return array( 'string_ec' );
	}

	public static function get_validators() {
		return array( 'required' );
	}

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}
}
