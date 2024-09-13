<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Product_Created_Date
 */
class Woo_Product_Created_Date extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Product created date';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by product created date';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_dummy_value() {
		return '2021-09-06';
	}

	public static function get_id() {
		return 'product_created_date';
	}

	public static function get_supported_filters() {
		return array( 'date' );
	}

	public static function get_field_value_type() {
		return static::TYPE_CLASS;
	}
}
