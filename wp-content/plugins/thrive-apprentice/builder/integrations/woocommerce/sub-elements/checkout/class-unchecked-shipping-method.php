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
class Unchecked_Shipping_Method extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Shipping method', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '#shipping_method li :not(.checked)';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		return $components;
	}
}

return new Unchecked_Shipping_Method( 'wc-unchecked-shipping-method' );
