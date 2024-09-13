<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Order_Number
 */
class Woo_Order_Id extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Order id';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Target an individual order id';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_dummy_value() {
		return 113099351;
	}

	public static function get_id() {
		return 'woo_order_id';
	}

	public static function get_supported_filters() {
		return array( 'number_comparison' );
	}

	public static function get_validators() {
		return array( 'required' );
	}

	public static function get_field_value_type() {
		return static::TYPE_NUMBER;
	}

	public static function primary_key() {
		return Woo_Order_Data::get_id();
	}
}
