<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Product_Parent_Id
 */
class Woo_Product_Parent_Id extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Product parent ID';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by product parent ID';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_dummy_value() {
		return '76';
	}

	public static function get_id() {
		return 'product_parent_id';
	}

	public static function get_supported_filters() {
		return array( 'string_ec' );
	}

	public static function get_validators() {
		return array( 'required' );
	}

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}
}
