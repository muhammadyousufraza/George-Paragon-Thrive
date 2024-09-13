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
 * Class Cart_Details
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Cart_Details extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Cart Details', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.cart_totals';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components               = parent::own_components();
		$components['typography'] = [ 'hidden' => true ];

		return $components;
	}
}

return new Cart_Details( 'wc-cart-details' );
