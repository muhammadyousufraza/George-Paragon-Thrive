<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Product_Purchase_Note
 */
class Woo_Product_Purchase_Note extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Product purchase note';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by product purchase note';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_dummy_value() {
		return 'This is an example purchase note';
	}

	public static function get_id() {
		return 'product_purchase_note';
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
