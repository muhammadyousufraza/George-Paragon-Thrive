<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Theme\Integrations\WooCommerce\Elements;

use Thrive\Theme\Integrations\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Cart_Remove_Product
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Cart_Remove_Product extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Remove Product', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.cart tbody a.remove';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['typography']['hidden'] = true;

		$components['product-remove-main-options'] = [
			'config' => [
				'color' => [
					'config'  => [
						'default' => '000',
						'label'   => __( 'Color', 'thrive-theme' ),
					],
					'extends' => 'ColorPicker',
				],
				'size'  => [
					'config'  => [
						'min'   => '1',
						'max'   => '100',
						'um'    => [ 'px' ],
						'label' => __( 'Size', 'thrive-theme' ),
					],
					'extends' => 'Slider',
				],
			],
		];

		return $components;
	}
}

return new Cart_Remove_Product( 'wc-cart-remove-product' );
