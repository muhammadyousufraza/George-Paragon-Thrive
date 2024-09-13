<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Product_Name
 */
class Woo_Product_Name extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Product name';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by product name';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_dummy_value() {
		return 'Example Product';
	}

	public static function get_id() {
		return 'woo_product_name';
	}

	public static function get_supported_filters() {
		return array( 'string_eca' );
	}

	public static function get_validators() {
		return array( 'required' );
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback() {
		$products = array();
		foreach ( Woo::get_products() as $product ) {
			$name       = $product->get_name();
			$products[] = array(
				'label' => $name,
				'id'    => $name,
			);
		}

		return $products;
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_field_value_type() {
		return static::TYPE_STRING;
	}
}
