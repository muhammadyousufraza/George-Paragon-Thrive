<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Order_Customer_Id
 */
class Woo_Order_Customer_Id extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Customer ID';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by customer ID';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_dummy_value() {
		return 1;
	}

	public static function get_id() {
		return 'customer_id';
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
