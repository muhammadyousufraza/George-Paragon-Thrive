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
 * Class Product_Catalog_Ordering
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\Shop
 */
class Product_Catalog_Ordering extends \TCB_Element_Abstract {

	/**
	 * Element name
	 *
	 * @return string|void
	 */
	public function name() {
		return __( 'Catalog Ordering', 'thrive-cb' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		$identifier = Main::get_sub_element_identifier( 'product-catalog-ordering' );

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
		$components['background']['hidden']       = true;
		$components['shadow']['hidden']           = true;
		$components['responsive']['hidden']       = true;
		$components['styles-templates']['hidden'] = true;

		$components['typography'] = Main::get_general_typography_config();

		$components['typography']['disabled_controls'] = array_merge( $components['typography']['disabled_controls'], [ 'Alignment' ] );

		$components['layout']['disabled_controls'] = [ 'Display', 'Alignment', '.tve-advanced-controls' ];

		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( is_array( $config ) ) {
				$components['typography']['config'][ $control ]['css_suffix'] = ' select';
				$components['typography']['config'][ $control ]['important']  = true;
			}
		}

		$components['layout']['config']['MarginAndPadding']['padding_to'] = ' select';

		$components['typography']['config']['css_suffix'] = '';
		$components['typography']['config']['css_prefix'] = '';

		$components['borders'] = [
			'config' => [
				'css_suffix' => ' select',
			],
		];

		return $components;
	}
}

return new Product_Catalog_Ordering( 'product-catalog-ordering' );
