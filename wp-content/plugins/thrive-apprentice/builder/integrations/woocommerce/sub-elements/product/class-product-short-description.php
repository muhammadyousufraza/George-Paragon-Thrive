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
 * Class Product_Short_Description
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Product_Short_Description extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Product Short Description', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.woocommerce-product-details__short-description';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$elements = [ ' p', ' a', ' ul', ' ul > li', ' ol', ' ol > li', ' h1', ' h2', ' h3', ' h4', ' h5', ' h6', ' blockquote > p', ' pre' ];

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( is_array( $config ) ) {
				$components['typography']['config'][ $control ]['css_suffix'] = $elements;
			}
		}

		$components['typography']['config']['css_suffix'] = $elements;

		$components['layout']['disabled_controls'] = [ 'Alignment' ];

		$components['styles-templates'] = [ 'hidden' => true ];
		$components['responsive']       = [ 'hidden' => true ];
		$components['animation']        = [ 'hidden' => true ];
		$components['shadow']           = [ 'hidden' => true ];

		return $components;
	}
}

return new Product_Short_Description( 'wc-product-short-description' );
