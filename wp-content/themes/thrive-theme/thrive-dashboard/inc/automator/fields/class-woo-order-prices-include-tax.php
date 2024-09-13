<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Order_Prices_Include_Tax
 */
class Woo_Order_Prices_Include_Tax extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Price includes tax';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by whether the price includes tax or not';
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
		return 'prices_include_tax';
	}

	public static function get_supported_filters() {
		return array( 'boolean' );
	}

	public static function get_field_value_type() {
		return static::TYPE_BOOLEAN;
	}
}
