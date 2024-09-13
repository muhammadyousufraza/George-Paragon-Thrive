<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Data_Field;
use TVA\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Product_Id_Data_Field
 */
class Product_Id_Data_Field extends Data_Field {
	/**
	 * Field name
	 */
	public static function get_name() {
		return 'Product id';
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

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback() {
		$products = [];

		foreach ( Product::get_items( [ 'status' => 'publish' ] ) as $product ) {
			$id              = $product->get_id();
			$products[ $id ] = [
				'label' => $product->name,
				'id'    => $id,
			];
		}

		return $products;
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_id() {
		return 'product_id';
	}

	public static function get_supported_filters() {
		return [ 'autocomplete' ];
	}

	public static function get_validators() {
		return [ 'required' ];
	}

	public static function get_field_value_type() {
		return static::TYPE_NUMBER;
	}

	public static function primary_key() {
		return Product_Data::get_id();
	}

	public static function get_dummy_value() {
		return 2;
	}
}
