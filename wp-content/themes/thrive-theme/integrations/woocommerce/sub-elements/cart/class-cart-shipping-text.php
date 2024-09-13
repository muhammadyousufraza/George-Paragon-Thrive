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
 * Class Cart_Shipping_Text
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Cart_Shipping_Text extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Text', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.woocommerce-shipping-destination';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['typography']['config']['FontColor']['important'] = true;
		$components['layout']['disabled_controls']                    = [
			'Display',
			'Alignment',
			'.tve-advanced-controls',
			'margin-top',
		];

		return $components;
	}
}

return new Cart_Shipping_Text( 'wc-cart-shipping-text' );
