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
 * Class Product_Search
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\Widgets
 */
class Product_Search extends Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Search Input', 'thrive-cb' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.widget_product_search .search-field';
	}
}

return new Product_Search( 'wc-product-search' );
