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
 * Class Product_Pagination_Next
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\Shop
 */
class Product_Pagination_Next extends Product_Pagination_Item {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Prev/Next Item', 'thrive-cb' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		$identifier = Main::get_sub_element_identifier( 'product-pagination-next' );

		return Main::get_shop_element_identifier( $identifier );
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['typography']['disabled_controls'] = array_merge( $components['typography']['disabled_controls'], [ 'TextStyle' ] );

		return $components;
	}
}

return new Product_Pagination_Next( 'product-pagination-next' );
