<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Order_Total_Tax
 */
class Woo_Order_Total_Tax extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Total tax';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by the total tax';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_dummy_value() {
		return 10;
	}

	public static function get_id() {
		return 'total_tax';
	}

	public static function get_supported_filters() {
		return array( 'number_comparison' );
	}

	public static function get_field_value_type() {
		return static::TYPE_NUMBER;
	}
}
