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
 * Class Products_Review_Title
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\Widgets
 */
class Products_Review_Title extends Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Recent Reviews Title', 'thrive-cb' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.widget_recent_reviews .widget-title';
	}
}

return new Products_Review_Title( 'wc-products-review-title' );
