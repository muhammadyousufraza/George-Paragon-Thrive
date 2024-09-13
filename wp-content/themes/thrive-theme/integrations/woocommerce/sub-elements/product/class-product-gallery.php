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
 * Class Product_Gallery
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Product_Gallery extends WooCommerce\Elements\Abstract_Sub_Element {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Product Gallery', 'thrive-theme' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.woocommerce-product-gallery';
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['product-gallery'] = [
			'config' => [
				'DisplayMagnifier' => [
					'config'  => [
						'name'    => '',
						'label'   => __( 'Magnifier', 'thrive-theme' ),
						'default' => true,
					],
					'extends' => 'Switch',
				],
				'GalleryWidth'     => [
					'config'  => [
						'min'   => '24',
						'max'   => '1024',
						'label' => __( 'Gallery Width', 'thrive-theme' ),
						'um'    => [ 'px' ],
						'css'   => 'width',
					],
					'extends' => 'Slider',
				],
				'Columns'          => [
					'config'  => [
						'min'   => WooCommerce\Shortcodes\Product_Template::GALLERY_MIN_COLUMNS,
						'max'   => WooCommerce\Shortcodes\Product_Template::GALLERY_MAX_COLUMNS,
						'um'    => [],
						'label' => __( 'Columns', 'thrive-theme' ),
					],
					'extends' => 'Slider',
				],
			],
		];

		$components['layout'] ['disabled_controls'] = [ 'Height', 'Width', 'Alignment' ];

		$components['typography'] = [ 'hidden' => true ];
		$components['background'] = [ 'hidden' => true ];

		return $components;
	}
}

return new Product_Gallery( 'wc-product-gallery' );
