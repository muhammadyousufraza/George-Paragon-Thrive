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
 * Class Cart_Shipping_Address
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Cart_Shipping_Address extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Address', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.woocommerce-shipping-destination strong';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['layout']['hidden'] = true;

		return $components;
	}
}

return new Cart_Shipping_Address( 'wc-cart-shipping-address' );
