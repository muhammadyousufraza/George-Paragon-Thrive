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
 * Class Cart_Widget_Price_Subtotal
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\Widgets
 */
class Cart_Widget_Price_Subtotal extends Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Subtotal', 'thrive-cb' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.widget_shopping_cart .woocommerce-mini-cart__total .woocommerce-Price-amount';
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

return new Cart_Widget_Price_Subtotal( 'wc-cart-widget-price-subtotal' );
