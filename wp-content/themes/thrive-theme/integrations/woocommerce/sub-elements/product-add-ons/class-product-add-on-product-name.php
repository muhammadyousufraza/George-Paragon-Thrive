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
 * Class Product_Add_On_Product_Name
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Product_Add_On_Product_Name extends WooCommerce\Elements\Abstract_Sub_Element {
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
		return '.product-addon-totals li:not(.wc-pao-subtotal-line) .wc-pao-col1';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['layout']['disabled_controls']     = [ 'Alignment' ];
		$components['typography']['disabled_controls'] = [ '[data-value="bold"]' ];

		$components['styles-templates'] = [ 'hidden' => true ];
		$components['responsive']       = [ 'hidden' => true ];
		$components['animation']        = [ 'hidden' => true ];
		$components['shadow']           = [ 'hidden' => true ];

		return $components;
	}
}

return new Product_Add_On_Product_Name( 'wc-product-add-on-product-name' );
