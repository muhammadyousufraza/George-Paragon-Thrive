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
 * Class Cart_Item
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\MiniCart
 */
class Cart_Item extends Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Cart Item', 'thrive-cb' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.tcb-woo-mini-cart-content .woocommerce-mini-cart-item';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		return $this->_components( true );
	}
}

return new Cart_Item( 'wc-cart-item' );
