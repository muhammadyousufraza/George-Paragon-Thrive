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
 * Class Cart_Item_Properties
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\MiniCart
 */
class Cart_Item_Properties extends Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Cart Item Properties', 'thrive-cb' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.tcb-woo-mini-cart-content .woocommerce-mini-cart-item .quantity,.tcb-woo-mini-cart-content .woocommerce-mini-cart-item .variation ';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = $this->_components();

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( in_array( $control, [ 'css_suffix', 'css_prefix' ] ) ) {
				continue;
			}
			/* make sure typography elements also apply on the link inside the tag */
			$components['typography']['config'][ $control ]['css_suffix'] = [ '', ' dt', 'span', 'p' ];
		}

		return $components;
	}
}

return new Cart_Item_Properties( 'wc-cart-item-properties' );
