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
 * Class Cart_Product_List
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Cart_Product_List extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Product List', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.woocommerce-cart-form table';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components               = parent::own_components();
		$components['typography'] = [ 'hidden' => true ];

		return $components;
	}
}

return new Cart_Product_List( 'wc-cart-product-list' );
