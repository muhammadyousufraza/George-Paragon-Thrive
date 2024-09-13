<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Product_Rating_Count
 */
class Woo_Product_Rating_Count extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Product rating count';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by the product rating count';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_dummy_value() {
		return 34;
	}

	public static function get_id() {
		return 'product_rating_count';
	}

	public static function get_supported_filters() {
		return array( 'number_comparison' );
	}

	public static function get_field_value_type() {
		return static::TYPE_NUMBER;
	}
}
