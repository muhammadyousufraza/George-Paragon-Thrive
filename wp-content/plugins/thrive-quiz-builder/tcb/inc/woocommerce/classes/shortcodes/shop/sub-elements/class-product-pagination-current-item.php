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
 * Class Product_Pagination_Current_Item
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\Shop
 */
class Product_Pagination_Current_Item extends \TCB_Element_Abstract {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Active Page', 'thrive-cb' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		$identifier = Main::get_sub_element_identifier( 'product-pagination-current-item' );

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

		$components['typography'] = Main::get_general_typography_config();

		$components['typography']['config']['css_suffix'] = '';
		$components['typography']['disabled_controls']    = [ 'TextTransform', 'TextAlign', '.tve-advanced-controls' ];

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( is_array( $config ) ) {
				$components['typography']['config'][ $control ]['css_suffix'] = '';
			}
		}

		$components['typography']['config']['FontColor']['important'] = true;
		$components['typography']['config']['FontSize']['important']  = true;

		$components['layout']['disabled_controls'] = [ 'Display', 'Alignment', '.tve-advanced-controls' ];

		$components['animation']['hidden']        = true;
		$components['responsive']['hidden']       = true;
		$components['styles-templates']['hidden'] = true;

		return $components;
	}
}

return new Product_Pagination_Current_Item( 'product-pagination-current-item' );
