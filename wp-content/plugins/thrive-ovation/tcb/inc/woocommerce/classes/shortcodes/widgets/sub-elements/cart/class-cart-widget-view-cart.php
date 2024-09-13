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
 * Class Cart_Widget_View_Cart
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\Widgets
 */
class Cart_Widget_View_Cart extends Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'View Cart', 'thrive-cb' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.widget_shopping_cart .woocommerce-mini-cart__buttons > a:first-child';
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
		$components                                    = parent::_components();
		$components['typography']['disabled_controls'] = [ 'TextAlign', '.tve-advanced-controls' ];
		$components['background']                      = [ 'hidden' => false ];

		return $components;
	}
}

return new Cart_Widget_View_Cart( 'wc-cart-widget-view-cart' );
