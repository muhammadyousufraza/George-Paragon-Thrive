<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Integrations\WooCommerce\Shortcodes\MiniCart;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Cart_Count
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\MiniCart
 */
class Cart_Count extends Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Cart Items Count', 'thrive-cb' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.tcb-woo-mini-cart-count';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = $this->_components( true );

		$components['wc-cart-count'] = array(
			'config' => array(
				'color'               => array(
					'config'  => array(
						'default' => '000',
						'label'   => __( 'Color', 'thrive-cb' ),
					),
					'extends' => 'ColorPicker',
				),
				'size'                => array(
					'config'  => array(
						'min'   => '1',
						'max'   => '100',
						'um'    => [ 'px' ],
						'label' => __( 'Size', 'thrive-cb' ),
					),
					'extends' => 'Slider',
				),
				'horizontal-position' => array(
					'config'  => array(
						'min'   => '-50',
						'max'   => '50',
						'um'    => [ 'px' ],
						'label' => __( 'Horizontal position', 'thrive-cb' ),
					),
					'extends' => 'Slider',
				),
				'vertical-position'   => array(
					'config'  => array(
						'min'   => '-50',
						'max'   => '50',
						'um'    => [ 'px' ],
						'label' => __( 'Vertical position', 'thrive-cb' ),
					),
					'extends' => 'Slider',
				),
			),
		);

		return $components;
	}
}

return new Cart_Count( 'wc-cart-count' );
