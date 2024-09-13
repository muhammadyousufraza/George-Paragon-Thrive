<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Product_Reviews_Allowed
 */
class Woo_Product_Reviews_Allowed extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Reviews allowed';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by whether reviews are allowed';
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
		return 'product_reviews_allowed';
	}

	public static function get_supported_filters() {
		return array( 'boolean' );
	}

	public static function get_field_value_type() {
		return static::TYPE_BOOLEAN;
	}
}
