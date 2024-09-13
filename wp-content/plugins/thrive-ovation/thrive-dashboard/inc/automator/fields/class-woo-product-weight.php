<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Product_Weight
 */
class Woo_Product_Weight extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Product weight';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by the product weight';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_dummy_value() {
		return 1.5;
	}

	public static function get_id() {
		return 'product_weight';
	}

	public static function get_supported_filters() {
		return array( 'number_comparison' );
	}

	public static function get_field_value_type() {
		return static::TYPE_NUMBER;
	}
}
