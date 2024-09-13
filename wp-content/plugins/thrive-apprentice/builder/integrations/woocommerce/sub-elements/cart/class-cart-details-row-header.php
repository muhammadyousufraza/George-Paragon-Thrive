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
 * Class Cart_Details_Row_Header
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Cart_Details_Row_Header extends WooCommerce\Elements\Abstract_Sub_Element {
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
		return '.cart_totals tbody th';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();
		$suffix     = [ ' ', ' + td:before' ];

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( is_array( $config ) ) {
				$components['typography']['config'][ $control ]['css_suffix'] = $suffix;
			}
		}
		$components['borders']['config']['css_suffix']    = $suffix;
		$components['layout']['config']['css_suffix']     = $suffix;
		$components['background']['config']['css_suffix'] = $suffix;

		$components['borders']['config']['Borders']['important'] = true;
		$components['borders']['config']['Corners']['important'] = true;

		$components['layout']['disabled_controls'] = [ 'margins', 'Display', 'Alignment', '.tve-advanced-controls' ];

		return $components;
	}
}

return new Cart_Details_Row_Header( 'wc-cart-details-row-header' );
