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
 * Class Product_Rating
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\Shop
 */
class Product_Rating extends \TCB_Element_Abstract {

	/**
	 * Element name
	 *
	 * @return string|void
	 */
	public function name() {
		return __( 'Product Star Rating', 'thrive-cb' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		$identifier = Main::get_sub_element_identifier( 'product-rating' );

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

		$components['product-star-rating'] = array(
			'config' => array(
				'color' => array(
					'config'  => array(
						'default' => '000',
						'label'   => __( 'Color', 'thrive-cb' ),
					),
					'extends' => 'ColorPicker',
				),
				'size'  => array(
					'config'     => array(
						'min'   => '1',
						'max'   => '100',
						'um'    => [ 'px' ],
						'label' => __( 'Size', 'thrive-cb' ),
					),
					'extends'    => 'Slider',
				),
			),
		);

		$components['typography']['hidden']       = true;
		$components['animation']['hidden']        = true;
		$components['responsive']['hidden']       = true;
		$components['styles-templates']['hidden'] = true;

		$components['layout']['disabled_controls'] = [ 'Width', 'Height', 'Display', 'Alignment', '.tve-advanced-controls' ];

		return $components;
	}
}

return new Product_Rating( 'product-rating' );
