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
 * Class Recently_Viewed_Products_Product_Price
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\Widgets
 */
class Recently_Viewed_Products_Product_Price extends Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Product Price', 'thrive-cb' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.widget_recently_viewed_products .woocommerce-Price-amount';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components                                    = parent::_components();
		$components['typography']['disabled_controls'] = [ 'TextAlign', 'TextTransform', '.tve-advanced-controls' ];

		return $components;
	}
}

return new Recently_Viewed_Products_Product_Price( 'wc-recently-viewed-products-product-price' );
