<?php

namespace TVA\Automator;

use InvalidArgumentException;
use Thrive\Automator\Items\Data_Object;
use TVA\Product;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Product_Data
 */
class Product_Data extends Data_Object {

	/**
	 * Get the data-object identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'product_data';
	}

	public static function get_nice_name() {
		return 'Apprentice product ID';
	}

	/**
	 * Array of field object keys that are contained by this data-object
	 *
	 * @return array
	 */
	public static function get_fields() {
		return [ 'product_id', 'product_identifier', 'product_title' ];
	}

	public static function create_object( $param ) {
		if ( empty( $param ) ) {
			throw new InvalidArgumentException( 'No parameter provided for Product_Data object' );
		}

		$product = null;

		if ( is_a( $param, '\TVA\Product' ) ) {
			$product = $param;
		} elseif ( is_a( $param, '\WP_Term' ) ) {
			$product = new Product( $param );
		} elseif ( is_numeric( $param ) ) {
			$term = Product::get_product_term_by_identifier( $param );
			if ( $term instanceof \WP_Term ) {
				$param = $term->term_id;
			}
			$product = new Product( (int) $param );
		} elseif ( ! empty( $param['product_id'] ) && is_numeric( $param['product_id'] ) ) {
			$product = new Product( (int) $param['product_id'] );
		} elseif ( is_array( $param ) ) {
			$product = new Product( (int) $param[0] );
		} elseif ( is_string( $param ) ) {
			$product = new Product( $param );
		}

		if ( $product && ! empty( $product->get_id() ) ) {
			return [
				'product_id'         => $product->get_id(),
				'product_identifier' => $product->get_identifier(),
				'product_title'      => $product->get_name(),
			];
		}

		return null;
	}

	public static function get_data_object_options() {
		$products = Product::get_items();
		$options  = [];

		foreach ( $products as $key => $product ) {
			$product_id = $product->get_id();

			if ( isset( $key ) & ! empty( $product_id ) ) {
				$options[ $product_id ] = [ 'id' => $product_id, 'label' => $product->get_name() ];
			}
		}

		return $options;
	}
}
