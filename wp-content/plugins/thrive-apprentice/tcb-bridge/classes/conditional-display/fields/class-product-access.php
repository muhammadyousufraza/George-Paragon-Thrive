<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Architect\ConditionalDisplay\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Product_Access
 *
 * @package TVA\Architect\ConditionalDisplay
 */
class Product_Access extends \TCB\ConditionalDisplay\Field {
	/**
	 * @return string
	 */
	public static function get_entity() {
		return 'user_data';
	}

	/**
	 * @return string
	 */
	public static function get_key() {
		return 'product_access';
	}

	public static function get_label() {
		return __( 'Has access to Apprentice Product(s)', 'thrive-apprentice' );
	}

	public static function get_conditions() {
		return [ 'autocomplete_hidden' ];
	}

	public function get_value( $user_data ) {
		$products = [];

		if ( ! empty( $user_data ) ) {
			$products = array_map( static function ( $product ) {
				return $product->get_id();
			}, tva_access_manager()->get_products_with_access() );
		}

		return $products;
	}

	public static function get_options( $selected_values = [], $searched_keyword = '' ) {
		$products = \TVA\Product::get_items( array( 'name__like' => $searched_keyword ) );
		$values   = [];

		foreach ( $products as $key => $product ) {
			$product_id    = $product->get_id();
			$product_label = $product->get_name();

			if ( isset( $key ) & ! empty( $product_id ) && static::filter_options( $product_id, $product_label, $selected_values, $searched_keyword ) ) {
				$values[] = [ 'value' => (string) $product_id, 'label' => $product_label ];
			}
		}

		return $values;
	}

	/**
	 * @return string
	 */
	public static function get_autocomplete_placeholder() {
		return __( 'Search products', 'thrive-apprentice' );
	}
}
