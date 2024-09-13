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
 * Class Cart_Shipping_Method
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Cart_Shipping_Method extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Shipping Method', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '#shipping_method label:not(.checked)';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['layout']['disabled_controls']      = [
			'margins',
			'Display',
			'Alignment',
			'.tve-advanced-controls',
			'Width',
			'Height',
			'margin-top',
			'padding-top',
		];
		$components['typography'] ['disabled_controls'] = [ 'TextAlign' ];

		return $components;
	}
}

return new Cart_Shipping_Method( 'wc-cart-shipping-method' );
