<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Product_Cross_Sell_Ids
 */
class Woo_Product_Cross_Sell_Ids extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Product cross sell IDs';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by product cross sell IDs';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '45';
	}

	public static function get_dummy_value() {
		return 'Example';
	}

	public static function get_id() {
		return 'product_cross_sell_ids';
	}

	public static function get_supported_filters() {
		return array( 'string_ec' );
	}

	public static function get_validators() {
		return array( 'required' );
	}

	public static function get_field_value_type() {
		return static::TYPE_ARRAY;
	}
}
