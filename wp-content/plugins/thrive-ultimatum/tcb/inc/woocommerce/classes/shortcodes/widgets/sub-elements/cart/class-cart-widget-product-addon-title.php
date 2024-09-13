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
 * Class Cart_Widget_Product_Addon_Title
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\Widgets
 */
class Cart_Widget_Product_Addon_Title extends Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Product Addon Title', 'thrive-cb' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.widget_shopping_cart .woocommerce-mini-cart-item .variation dt';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components                                    = parent::_components();
		$components['typography']['disabled_controls'] = [ 'TextAlign', '.tve-advanced-controls' ];

		return $components;
	}
}

return new Cart_Widget_Product_Addon_Title( 'wc-cart-widget-product-addon-title' );
