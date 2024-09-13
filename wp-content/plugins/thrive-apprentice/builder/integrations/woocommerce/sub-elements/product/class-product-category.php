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
 * Class Product_Category
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Product_Category extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Product Category', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.product_meta .posted_in';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( is_array( $config ) ) {
				$components['typography']['config'][ $control ]['css_suffix'] = [ ' a', '' ];
			}
		}

		$components['layout'] ['disabled_controls'] = [ 'Alignment' ];

		$components['typography']['config']['TextAlign']['css_suffix'] = '';

		$components['styles-templates'] = [ 'hidden' => true ];
		$components['responsive']       = [ 'hidden' => true ];
		$components['animation']        = [ 'hidden' => true ];
		$components['shadow']           = [ 'hidden' => true ];

		return $components;
	}
}

return new Product_Category( 'wc-product-category' );
