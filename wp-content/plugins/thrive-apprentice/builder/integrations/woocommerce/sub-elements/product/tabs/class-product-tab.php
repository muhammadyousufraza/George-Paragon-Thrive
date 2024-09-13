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
 * Class Product_Tab
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Product_Tab extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Product Tab', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.wc-tabs > li';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( is_array( $config ) ) {
				$components['typography']['config'][ $control ]['css_suffix'] = [ '', ' a' ];
			}
		}

		$components['borders']['config']['Borders']['important'] = true;
		$components['borders']['config']['Corners']['important'] = true;

		return $components;
	}

	/**
	 * Whether or not the this element can be edited while under :hover state
	 *
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}
}

return new Product_Tab( 'wc-product-tab' );
