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
 * Class Product_Add_On_Dropdown
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Product_Add_On_Dropdown extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Select', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.wc-pao-addon-select';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['layout']['disabled_controls']     = [ 'Alignment' ];
		$components['typography']['disabled_controls'] = [ 'TextAlign', '[data-value="tcb-typography-line-height"]' ];

		$components['background']       = [ 'hidden' => true ];
		$components['styles-templates'] = [ 'hidden' => true ];
		$components['responsive']       = [ 'hidden' => true ];
		$components['animation']        = [ 'hidden' => true ];
		$components['shadow']           = [ 'hidden' => true ];

		return $components;
	}
}

return new Product_Add_On_Dropdown( 'wc-product-add-on-dropdown' );
