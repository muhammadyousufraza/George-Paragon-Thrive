<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Order_Item_Count
 */
class Woo_Order_Item_Count extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Item count';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by the item count';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_dummy_value() {
		return 3;
	}

	public static function get_id() {
		return 'item_count';
	}

	public static function get_supported_filters() {
		return array( 'number_comparison' );
	}

	public static function get_field_value_type() {
		return static::TYPE_NUMBER;
	}
}
