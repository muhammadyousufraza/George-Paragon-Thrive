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
 * Class Cart_Icon
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\MiniCart
 */
class Cart_Icon extends Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Cart Icon', 'thrive-cb' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.tcb-woo-mini-cart-icon';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = $this->_components( true );

		$components['wc-cart-icon'] = array(
			'config' => array(
				'color' => array(
					'config'  => array(
						'default' => '000',
						'label'   => __( 'Color', 'thrive-cb' ),
					),
					'extends' => 'ColorPicker',
				),
				'size'  => array(
					'css_suffix' => ' > svg',
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

		$components['borders'] = [ 'hidden' => true ];

		return $components;
	}
}

return new Cart_Icon( 'wc-cart-icon' );
