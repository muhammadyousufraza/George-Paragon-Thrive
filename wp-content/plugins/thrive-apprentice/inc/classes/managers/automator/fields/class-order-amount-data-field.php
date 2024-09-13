<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Order_Amount_Field
 */
class Order_Amount_Data_Field extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Order amount';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by order amount';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_id() {
		return 'order_amount';
	}

	public static function get_supported_filters() {
		return [ 'number_comparison' ];
	}

	public static function get_field_value_type() {
		return static::TYPE_NUMBER;
	}

	public static function get_dummy_value() {
		return '9.99';
	}
}
