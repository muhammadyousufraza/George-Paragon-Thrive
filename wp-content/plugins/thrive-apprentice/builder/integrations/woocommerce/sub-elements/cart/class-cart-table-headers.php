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
 * Class Cart_Table_Headers
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Cart_Table_Headers extends WooCommerce\Elements\Abstract_Sub_Element {
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
		return '.cart.shop_table thead';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();
		$suffix     = [ ' th:not(.product-thumbnail):not(.product-remove)', ' +tbody td:before' ];

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( is_array( $config ) ) {
				$components['typography']['config'][ $control ]['css_suffix'] = $suffix;
			}
		}
		$components['borders']['config']['css_suffix']    = $suffix;
		$components['layout']['config']['css_suffix']     = $suffix;
		$components['background']['config']['css_suffix'] = $suffix;

		return $components;
	}
}

return new Cart_Table_Headers( 'wc-cart-table-headers' );
