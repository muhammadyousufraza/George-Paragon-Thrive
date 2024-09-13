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
 * Class Cart_Related_Heading
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Cart_Related_Heading extends WooCommerce\Elements\Abstract_Sub_Element {
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
		return '.cross-sells > h2';
	}
}

return new Cart_Related_Heading( 'wc-cart-related-heading' );
