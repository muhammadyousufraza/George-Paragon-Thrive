<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Integrations\WooCommerce\Shortcodes\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Cart_Widget_Product_Price
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\Widgets
 */
class Cart_Widget_Product_Price extends Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Price', 'thrive-cb' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.widget_shopping_cart .product_list_widget .quantity';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components                                    = parent::_components();
		$components['typography']['disabled_controls'] = [ 'TextAlign', 'TextTransform', '.tve-advanced-controls' ];

		return $components;
	}
}

return new Cart_Widget_Product_Price( 'wc-cart-widget-product-price' );
