<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Order_Shipping_Address_2
 */
class Woo_Order_Shipping_Address_2 extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Shipping address line 2';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by shipping address line 2';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_dummy_value() {
		return 'Monkey District';
	}

	public static function get_id() {
		return 'shipping_address_2';
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
