<?php

namespace TVA\Automator;

use Thrive\Automator\Items\Action_Field;
use TVA\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Products_Field
 */
class Products_Field extends Action_Field {

	public static function get_id() {
		return 'products';
	}

	public static function get_type() {
		return 'autocomplete';
	}

	/**
	 * Field description
	 */
	public static function get_description() {
		return 'Please select a product';
	}

	/**
	 * Field name
	 */
	public static function get_name() {
		return '';
	}

	/**
	 * Field input placeholder
	 */
	public static function get_placeholder() {
		return 'Type product name';
	}

	/**
	 * $$value will be replaced by field value
	 * $$length will be replaced by value length
	 *
	 * @var string
	 */
	public static function get_preview_template() {
		return 'Product: $$value';
	}

	/**
	 * For multiple option inputs, name of the callback function called through ajax to get the options
	 */
	public static function get_options_callback( $action_id, $action_data ) {
		$products = Product::get_items();
		$values   = [];

		foreach ( $products as $key => $product ) {
			$product_id = $product->get_id();

			if ( isset( $key ) & ! empty( $product_id ) ) {
				$name            = $product->get_name();
				$values[ $name ] = [ 'id' => $product_id, 'label' => $name ];
			}
		}

		return $values;
	}

	public static function is_ajax_field() {
		return true;
	}

	public static function get_validators() {
		return [ 'required' ];
	}

	public static function allowed_data_set_values() {
		return [ Product_Data::get_id() ];
	}
}
