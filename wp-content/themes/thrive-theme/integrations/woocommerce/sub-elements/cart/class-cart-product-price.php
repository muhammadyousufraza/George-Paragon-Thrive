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
 * Class Cart_Product_Price
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Cart_Product_Price extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Product Price', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.woocommerce-cart-form bdi';
	}
}

return new Cart_Product_Price( 'wc-cart-product-price' );
