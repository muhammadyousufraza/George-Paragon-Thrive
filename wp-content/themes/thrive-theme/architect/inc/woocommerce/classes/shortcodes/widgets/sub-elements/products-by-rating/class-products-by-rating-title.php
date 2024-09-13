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
 * Class Products_By_Rating_Title
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\Widgets
 */
class Products_By_Rating_Title extends Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Top Rated Products Title', 'thrive-cb' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.widget_top_rated_products .widget-title';
	}
}

return new Products_By_Rating_Title( 'wc-products-by-rating-title' );
