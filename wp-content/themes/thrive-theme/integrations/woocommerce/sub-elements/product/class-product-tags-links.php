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
 * Class Product_Tags_Links
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Product_Tags_Links extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Product Tag Links', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.product_meta .tagged_as a';
	}
}

return new Product_Tags_Links( 'wc-product-tags-links' );
