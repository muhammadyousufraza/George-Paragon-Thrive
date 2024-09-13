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
 * Class Cart_Related_Product_Button
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Cart_Related_Product_Button extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Add To Cart', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.cross-sells a.button';
	}

	/**
	 * Whether or not the this element can be edited while under :hover state
	 *
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}
}

return new Cart_Related_Product_Button( 'wc-cart-related-product-button' );
