<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Product_Total_Number_Sales
 */
class Woo_Product_Total_Number_Sales extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Total number of sales';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by the total number of sales';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_dummy_value() {
		return 25;
	}

	public static function get_id() {
		return 'product_total_number_sales';
	}

	public static function get_supported_filters() {
		return array( 'number_comparison' );
	}

	public static function get_field_value_type() {
		return static::TYPE_NUMBER;
	}
}
