<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Integrations\WooCommerce\Shortcodes\Product_Categories;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Product_Category_Text_Wrapper
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\Product_Categories
 */
class Product_Category_Text_Wrapper extends \TCB_Element_Abstract {
	/**
	 * Element name
	 *
	 * @return string|void
	 */
	public function name() {
		return __( 'Product Category Text Container', 'thrive-cb' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrive-product-category-text-wrapper';
	}

	/**
	 * Element is not visible in the sidebar
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	/**
	 * The product category has hover state
	 *
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}

	public function own_components() {
		$components = parent::own_components();

		$components['layout']['disabled_controls'] = [ 'Display', 'Alignment' ];

		$components['animation']['hidden']        = true;
		$components['responsive']['hidden']       = true;
		$components['typography']['hidden']       = true;
		$components['styles-templates']['hidden'] = true;

		return $components;
	}
}

return new Product_Category_Text_Wrapper( 'product-category-text-wrapper' );
