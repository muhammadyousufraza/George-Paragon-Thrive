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
 * Class View_Cart_Button
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\MiniCart
 */
class View_Cart_Button extends Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'View Cart Button', 'thrive-cb' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.woocommerce-mini-cart__buttons > a:not(.checkout)';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		return $this->_components();
	}
}

return new View_Cart_Button( 'wc-view-cart-button' );
