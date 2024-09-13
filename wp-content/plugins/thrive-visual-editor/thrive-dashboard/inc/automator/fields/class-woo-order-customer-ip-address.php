<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Order_Customer_Ip_Address
 */
class Woo_Order_Customer_Ip_Address extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Customer IP address';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by customer IP address';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_dummy_value() {
		return '26.103.134.169';
	}

	public static function get_id() {
		return 'customer_ip_address';
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
