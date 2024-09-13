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
 * Class Product_Tag_Title
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\Widgets
 */
class Product_Tag_Title extends Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Product Tag Title', 'thrive-cb' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.widget_product_tag_cloud .widget-title';
	}
}

return new Product_Tag_Title( 'wc-product-tag-title' );
