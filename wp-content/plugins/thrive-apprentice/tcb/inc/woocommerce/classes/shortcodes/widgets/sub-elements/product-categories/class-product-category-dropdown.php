<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Integrations\WooCommerce\Shortcodes\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Product_Category_Dropdown
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\Widgets
 */
class Product_Category_Dropdown extends Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Product Category', 'thrive-cb' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.widget_product_categories .select2-container--default';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components                                    = parent::_components();
		$components['typography']['disabled_controls'] = [ '.tve-advanced-controls' ];

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( is_array( $config ) ) {
				$components['typography']['config'][ $control ]['css_suffix'] = [ '', '.select2-selection' ];
			}
		}

		return $components;
	}
}

return new Product_Category_Dropdown( 'wc-product-category-dropdown' );
