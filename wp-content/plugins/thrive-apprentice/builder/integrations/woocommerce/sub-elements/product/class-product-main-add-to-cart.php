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
 * Class Product_Main_Add_To_Cart
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Product_Main_Add_To_Cart extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Add to Cart', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.single_add_to_cart_button';
	}

	/**
	 * Whether or not the this element can be edited while under :hover state
	 *
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['typography'] ['disabled_controls'] = [ 'TextAlign', '.tve-advanced-controls' ];

		$components['layout'] ['disabled_controls'] = [ 'Height', 'Width', 'Alignment' ];

		return $components;
	}
}

return new Product_Main_Add_To_Cart( 'wc-product-main-add-to-cart' );
