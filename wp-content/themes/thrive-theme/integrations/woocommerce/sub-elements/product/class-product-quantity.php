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
 * Class Product_Quantity
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Product_Quantity extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Product Quantity', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.woocommerce-grouped-product-list-item__quantity, :not(.woocommerce-grouped-product-list-item__quantity) > .quantity';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( is_array( $config ) ) {
				$components['typography']['config'][ $control ]['css_suffix'] = [ ' button', '' ];
			}
		}

		$components['typography']['disabled_controls']    = [ '[data-value="tcb-typography-letter-spacing"]' ];
		$components['background']['config']['css_suffix'] = [ ' input', '' ];

		return $components;
	}
}

return new Product_Quantity( 'wc-product-quantity' );
