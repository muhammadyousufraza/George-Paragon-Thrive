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
 * Class Cart_Widget_Product_Addon_Value
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\Widgets
 */
class Cart_Widget_Product_Addon_Value extends Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Product Addon Value', 'thrive-cb' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.widget_shopping_cart .woocommerce-mini-cart-item .variation dd p';
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

return new Cart_Widget_Product_Addon_Value( 'wc-cart-widget-product-addon-value' );
