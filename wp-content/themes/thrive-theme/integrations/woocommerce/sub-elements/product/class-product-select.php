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
 * Class Product_Select
 *
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Product_Select extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Product Select', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		/* the container has to be the element, because the select must have pointer-events:none so the dropdown doesn't open in the editor */
		return '.variations td.value';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components      = parent::own_components();
		$select_selector = ' select';

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( is_array( $config ) ) {
				$components['typography']['config'][ $control ]['css_suffix'] = $select_selector;
			}
		}
		$components['typography'] ['disabled_controls']   = [ 'TextAlign' ];
		$components['typography']['config']['css_suffix'] = $select_selector;
		$components['borders']['config']['css_suffix']    = $select_selector;
		$components['layout']['config']['css_suffix']     = $select_selector;

		$components['layout'] ['disabled_controls'] = [ 'Height', 'Width', 'Alignment' ];

		$components['background'] = [ 'hidden' => true ];

		return $components;
	}
}

return new Product_Select( 'wc-product-select' );
