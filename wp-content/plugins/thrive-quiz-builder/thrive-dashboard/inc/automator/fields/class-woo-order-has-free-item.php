<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Order_Has_Free_Item
 */
class Woo_Order_Has_Free_Item extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Has free item';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by whether the order contains a free item or not';
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
		return 'has_free_item';
	}

	public static function get_supported_filters() {
		return array( 'boolean' );
	}

	public static function get_field_value_type() {
		return static::TYPE_BOOLEAN;
	}
}
