<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Product_Low_Stock_Amount
 */
class Woo_Product_Low_Stock_Amount extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Low stock amount';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by the low stock amount';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_dummy_value() {
		return 2;
	}

	public static function get_id() {
		return 'product_low_stock_amount';
	}

	public static function get_supported_filters() {
		return array( 'number_comparison' );
	}

	public static function get_field_value_type() {
		return static::TYPE_NUMBER;
	}
}
