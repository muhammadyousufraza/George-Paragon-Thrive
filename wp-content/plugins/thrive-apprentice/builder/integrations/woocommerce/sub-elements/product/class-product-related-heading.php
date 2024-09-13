<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Theme\Integrations\WooCommerce\Elements;

use Thrive\Theme\Integrations\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Product_Related_Heading
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Product_Related_Heading extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Product Related Heading', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.related.products > h2';
	}
}

return new Product_Related_Heading( 'wc-product-related-heading' );
