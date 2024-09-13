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
 * Class Cart_Product_Quantity
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Cart_Product_Quantity extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Product Quantity', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.cart tbody .product-quantity .quantity';
	}
}

return new Cart_Product_Quantity( 'wc-cart-product-quantity' );
