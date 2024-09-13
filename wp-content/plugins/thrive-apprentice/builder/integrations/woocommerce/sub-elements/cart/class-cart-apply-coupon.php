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
 * Class Cart_Apply_Coupon
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Cart_Apply_Coupon extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Button', 'thrive-theme' );
	}

	/**
	 * Whether or not the this element can be edited while under :hover state
	 *
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.actions .coupon button';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['typography'] = Main::get_general_typography_config();

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( is_array( $config ) ) {
				$components['typography']['config'][ $control ]['css_suffix'] = '';
				$components['typography']['config'][ $control ]['important']  = true;
			}
		}
		$components['borders']['config']['Borders']['important'] = true;
		$components['borders']['config']['Corners']['important'] = true;

		return $components;
	}
}

return new Cart_Apply_Coupon( 'wc-cart-apply-coupon' );
