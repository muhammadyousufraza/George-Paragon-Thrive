<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Integrations\WooCommerce\Shortcodes\Shop;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Product_Category_Title
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\Shop
 */
class Product_Category_Title extends \TCB_Element_Abstract {

	/**
	 * Element name
	 *
	 * @return string|void
	 */
	public function name() {
		return __( 'Product Category Title', 'thrive-cb' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.woocommerce-loop-category__title';
	}

	/**
	 * Element is not visible in the sidebar
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	public function own_components() {
		$components = parent::own_components();

		$components['animation']['hidden']        = true;
		$components['shadow']['hidden']           = true;
		$components['responsive']['hidden']       = true;
		$components['styles-templates']['hidden'] = true;

		$components['layout']['disabled_controls'] = [ 'Display', 'Alignment', '.tve-advanced-controls' ];

		$components['typography'] = Main::get_general_typography_config();

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( is_array( $config ) ) {
				$components['typography']['config'][ $control ]['css_suffix'] = '';
				$components['typography']['config'][ $control ]['important']  = true;
			}
		}

		$components['typography']['config']['css_suffix'] = '';
		$components['typography']['config']['css_prefix'] = '';

		return $components;
	}

	/**
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}
}

return new Product_Category_Title( 'product-category-title' );
