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
 * Class Cart_Item_Image
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\MiniCart
 */
class Cart_Item_Image extends Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Cart Item Image', 'thrive-cb' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.woocommerce-mini-cart-item > a > img';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = $this->_components( true );

		$components['shadow']     = [
			'config' => [
				/* sometimes the 'box-shadow' set from woo can be stronger than this, so we give it an '!important' to help it */
				'important'         => true,
				/* only the drop-shadow makes sense for images, disable the rest */
				'disabled_controls' => [ 'inner', 'text' ],
			],
		];
		$components['background'] = [ 'hidden' => true ];

		return $components;
	}
}

return new Cart_Item_Image( 'wc-cart-item-image' );
