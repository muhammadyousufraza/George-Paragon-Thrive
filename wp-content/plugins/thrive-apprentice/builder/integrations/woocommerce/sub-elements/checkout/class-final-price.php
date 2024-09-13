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
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Final_Price extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Total price', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.cart-subtotal .amount, .order-total .amount, .tax-total .amount';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		return $components;
	}
}

return new Final_Price( 'wc-final-price' );
