<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Order_Shipping_City
 */
class Woo_Order_Shipping_City extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Shipping city';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by shipping city';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_dummy_value() {
		return 'Milwaukee';
	}

	public static function get_id() {
		return 'shipping_city';
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
