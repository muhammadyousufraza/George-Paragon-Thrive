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
 * Class Product_Price
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Product_Price extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Product Price', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.price';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( is_array( $config ) ) {
				$components['typography']['config'][ $control ]['css_suffix'] = [ ' span', '' ];
			}
		}

		$components['typography']['config']['TextAlign']['css_suffix'] = '';

		$components['styles-templates'] = [ 'hidden' => true ];
		$components['responsive']       = [ 'hidden' => true ];
		$components['animation']        = [ 'hidden' => true ];
		$components['shadow']           = [ 'hidden' => true ];

		return $components;
	}
}

return new Product_Price( 'wc-product-price' );
