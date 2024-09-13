<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Theme\Integrations\WooCommerce\Elements;

use TCB\Integrations\WooCommerce\Shortcodes\Shop\Main;
use Thrive\Theme\Integrations\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Cart_Coupon_Input
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Cart_Coupon_Input extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Coupon Input', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.coupon input';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['borders']['config']['Borders']['important'] = true;
		$components['borders']['config']['Corners']['important'] = true;

		return $components;
	}

}

return new Cart_Coupon_Input( 'wc-cart-coupon-input' );
