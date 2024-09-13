<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Product_Get_Virtual
 */
class Woo_Product_Get_Virtual extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Is the product virtual?';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by whether the product is virtual or not';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_dummy_value() {
		return 'TRUE';
	}

	public static function get_id() {
		return 'product_get_virtual';
	}

	public static function get_supported_filters() {
		return array( 'boolean' );
	}

	public static function get_field_value_type() {
		return static::TYPE_BOOLEAN;
	}
}
