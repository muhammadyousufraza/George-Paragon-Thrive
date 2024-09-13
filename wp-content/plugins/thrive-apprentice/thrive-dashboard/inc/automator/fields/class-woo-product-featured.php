<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Product_Featured
 */
class Woo_Product_Featured extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Product featured';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by whether the product is featured or not';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_dummy_value() {
		return 'FALSE';
	}

	public static function get_id() {
		return 'product_featured';
	}

	public static function get_supported_filters() {
		return array( 'boolean' );
	}

	public static function get_field_value_type() {
		return static::TYPE_BOOLEAN;
	}
}
