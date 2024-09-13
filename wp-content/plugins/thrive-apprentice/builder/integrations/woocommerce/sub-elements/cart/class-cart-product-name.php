<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Theme\Integrations\WooCommerce\Elements;

use Thrive\Theme\Integrations\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Cart_Product_Name
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Cart_Product_Name extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Product Title', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.cart tbody .product-name';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( is_array( $config ) ) {
				$components['typography']['config'][ $control ]['important'] = true;
			}
		}

		return $components;
	}

}

return new Cart_Product_Name( 'wc-cart-product-name' );
