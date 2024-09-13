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
 * Class Product_Wrapper
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\Shop
 */
class Product_Wrapper extends \TCB_Element_Abstract {

	/**
	 * Element name
	 *
	 * @return string|void
	 */
	public function name() {
		return __( 'Product', 'thrive-cb' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		$identifier = Main::get_sub_element_identifier( 'product-wrapper' );

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

		$components['layout']['disabled_controls'] = [ 'Display', 'Alignment', '.tve-advanced-controls' ];

		$components['animation']['hidden']        = true;
		$components['responsive']['hidden']       = true;
		$components['typography']['hidden']       = true;
		$components['styles-templates']['hidden'] = true;

		return $components;
	}
}

return new Product_Wrapper( 'product-wrapper' );
