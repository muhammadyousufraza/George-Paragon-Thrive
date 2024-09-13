<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Product_Sku
 */
class Woo_Product_Id extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Product ID';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Filter by product id';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return '';
	}

	public static function get_dummy_value() {
		return 5;
	}

	public static function get_id() {
		return 'woo_product_id';
	}

	public static function get_supported_filters() {
		return array( 'autocomplete' );
	}

	public static function get_validators() {
		return array( 'required' );
	}

	public static function get_field_value_type() {
		return static::TYPE_NUMBER;
	}

	public static function primary_key() {
		return Woo_Product_Data::get_id();
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_options_callback() {
		$products = array();
		foreach ( Woo::get_products() as $product ) {
			$id              = $product->get_id();
			$products[ $id ] = array(
				'id'    => $id,
				'label' => $product->get_name(),
			);
		}

		return $products;
	}
}
