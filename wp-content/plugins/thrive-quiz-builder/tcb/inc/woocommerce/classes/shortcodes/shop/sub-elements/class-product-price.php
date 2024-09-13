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
 * Class Product_Price
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\Shop
 */
class Product_Price extends \TCB_Element_Abstract {

	/**
	 * Element name
	 *
	 * @return string|void
	 */
	public function name() {
		return __( 'Product Price', 'thrive-cb' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		$identifier = Main::get_sub_element_identifier( 'product-price' );

		return Main::get_shop_element_identifier( $identifier );
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

		$components['typography'] = Main::get_general_typography_config();

		$components['layout']['disabled_controls'] = [ 'Display', 'Alignment', '.tve-advanced-controls' ];

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
}

return new Product_Price( 'product-price' );
