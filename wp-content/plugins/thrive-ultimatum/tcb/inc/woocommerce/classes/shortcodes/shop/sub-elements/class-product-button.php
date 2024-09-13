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
 * Class Product_Button
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\Shop
 */
class Product_Button extends \TCB_Element_Abstract {

	/**
	 * Element name
	 *
	 * @return string|void
	 */
	public function name() {
		return __( 'Add to cart', 'thrive-cb' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		$identifier = Main::get_sub_element_identifier( 'product-button' );

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

	/**
	 * Add to cart button has hover state
	 *
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}

	public function own_components() {
		$components = parent::own_components();

		$components['animation']['hidden']        = true;
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

		/* this is less specific because the color property has to be changed while the cart button does the loading animation */
		$components['typography']['config']['FontColor']['important'] = false;

		$components['typography']['config']['css_suffix'] = '';
		$components['typography']['config']['css_prefix'] = '';
		$components['typography']['disabled_controls']    = [ 'TextAlign', '.tve-advanced-controls' ];

		return $components;
	}
}

return new Product_Button( 'product-button' );
