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
 * Class Cart_Widget_Title
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\Widgets
 */
class Cart_Widget_Title extends Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Cart Title', 'thrive-cb' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.widget_shopping_cart .widget-title';
	}
}

return new Cart_Widget_Title( 'wc-cart-widget-title' );
