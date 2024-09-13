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
 * Class Product_Category_Links
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Product_Category_Links extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Product Category Links', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.product_meta .posted_in a';
	}
}

return new Product_Category_Links( 'wc-product-category-links' );
