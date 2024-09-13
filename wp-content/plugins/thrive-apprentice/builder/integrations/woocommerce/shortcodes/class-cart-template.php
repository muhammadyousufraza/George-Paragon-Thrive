<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Theme\Integrations\WooCommerce\Shortcodes;

use TCB\Integrations\WooCommerce\Shortcodes\MiniCart\Main as MiniCart;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Cart_Template
 *
 * @package Thrive\Theme\Integrations\WooCommerce\Shortcodes
 */
class Cart_Template {

	const SHORTCODE = 'thrive_cart_template';

	public static function init() {
		add_shortcode( static::SHORTCODE, [ __CLASS__, 'render' ] );
	}

	/**
	 * Render cart template
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	public static function render( $attr = [] ) {
		$classes = [ 'cart-template-wrapper', THRIVE_WRAPPER_CLASS ];

		if ( \Thrive_Utils::is_inner_frame() || \Thrive_Utils::during_ajax() ) {
			$classes[] = 'tcb-selector-no_clone';

			/* in the editor, we want to display some products even if the cart is currently empty */
			if ( empty( wc()->cart->get_cart() ) ) {
				MiniCart::generate_dummy_cart();

				$cart = \WC_Shortcodes::cart();

				/* empty the cart to remove the products that we just added */
				WC()->cart->empty_cart();
			}
		}

		if ( empty( $cart ) ) {
			$cart = \WC_Shortcodes::cart();
		}

		return \TCB_Utils::wrap_content( $cart, 'div', '', $classes, \Thrive_Utils::create_attributes( $attr ) );
	}
}

return Cart_Template::class;
