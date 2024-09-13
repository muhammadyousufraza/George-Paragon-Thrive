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
 * Class Cart_Related_Product_Image
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Cart_Related_Product_Image extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Product Image', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.cross-sells .product a img:not(.avatar)';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components['layout'] ['disabled_controls'] = [ 'Height', 'Width', 'Alignment' ];

		$components['styles-templates'] = [ 'hidden' => true ];
		$components['responsive']       = [ 'hidden' => true ];
		$components['typography']       = [ 'hidden' => true ];
		$components['background']       = [ 'hidden' => true ];
		$components['animation']        = [ 'hidden' => true ];
		$components['shadow']           = [ 'hidden' => true ];

		return $components;
	}
}

return new Cart_Related_Product_Image( 'wc-cart-related-product-image' );
