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
 * Class Product_Pagination
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\Shop
 */
class Product_Pagination extends \TCB_Element_Abstract {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Pagination', 'thrive-cb' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		$identifier = Main::get_sub_element_identifier( 'product-pagination' );

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

		return array_merge( $components, $this->group_component() );
	}

	/**
	 * Group Edit Properties
	 *
	 * @return array|bool
	 */
	public function has_group_editing() {
		return array(
			'select_values' => array(
				array(
					'value'    => 'product_pagination_navigation_items',
					'selector' => '.tcb-prev-next',
					'name'     => __( 'Prev/Next Items', 'thrive-cb' ),
					'singular' => __( 'Prev/Next Item', 'thrive-cb' ),
					'no_unlock' => true,
				),
				array(
					'value'     => 'product_pagination_numbered_items',
					'selector'  => Main::get_sub_element_identifier( 'product-pagination-item' ),
					'name'      => __( 'Grouped Page Numbers', 'thrive-cb' ),
					'singular'  => __( 'Page Number', 'thrive-cb' ),
					'no_unlock' => true,
				),
			),
		);
	}
}

return new Product_Pagination( 'product-pagination' );
