<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Integrations\WooCommerce\Shortcodes\MiniCart;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Cart_Wrapper
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\MiniCart
 */
class Cart_Wrapper extends Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Cart Container', 'thrive-cb' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.tcb-woo-mini-cart-content .widget_shopping_cart_content';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		return $this->_components( true );
	}
}

return new Cart_Wrapper( 'wc-cart-wrapper' );
