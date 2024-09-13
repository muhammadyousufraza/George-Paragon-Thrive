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
 * Class Checkout_Button
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\MiniCart
 */
class Checkout_Button extends Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Checkout Button', 'thrive-cb' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.woocommerce-mini-cart__buttons .checkout';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		return $this->_components();
	}
}

return new Checkout_Button( 'wc-checkout-button' );
