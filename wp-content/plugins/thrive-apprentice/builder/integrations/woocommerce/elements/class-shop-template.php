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

if ( ! class_exists( 'Thrive_Theme_Cloud_Element_Abstract' ) ) {
	require_once ARCHITECT_INTEGRATION_PATH . '/classes/class-thrive-theme-cloud-element-abstract.php';
}

/**
 * Class Shop_Template
 * @package Thrive\Theme\Integrations\WooCommerce\Elements
 */
class Shop_Template extends \Thrive_Theme_Cloud_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Shop', 'thrive-theme' );
	}

	/**
	 * Set WooCommerce as alternate text for search
	 *
	 * @return string
	 */
	public function alternate() {
		return 'woocommerce';
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'woo';
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.shop-template-wrapper';
	}

	/**
	 * This element is a shortcode
	 *
	 * @return bool
	 */
	public function is_shortcode() {
		return true;
	}

	/**
	 * Return the shortcode tag of the element.
	 *
	 * @return string
	 */
	public static function shortcode() {
		return WooCommerce\Shortcodes\Shop_Template::SHORTCODE;
	}

	/**
	 * If an element has selector or a data-css will be generated
	 *
	 * @return bool
	 */
	public function has_selector() {
		return true;
	}

	public function own_components() {
		$components = [
			'typography'       => [ 'hidden' => true ],
			'animation'        => [ 'hidden' => true ],
			'shadow'           => [ 'hidden' => true ],
			'responsive'       => [ 'hidden' => true ],
			'styles-templates' => [ 'hidden' => true ],
			'layout'           => [
				'disabled_controls' => [],
			],

		];

		$components['shop-template'] = [
			'config' => [
				'PostsPerPage'                => [
					'config'  => [
						'min'   => '1',
						'max'   => '24',
						'um'    => [],
						'label' => __( 'Products per page', 'thrive-theme' ),
					],
					'extends' => 'Slider',
				],
				'Columns'                     => [
					'config'  => [
						'min'   => '1',
						'max'   => '8',
						'um'    => [],
						'label' => __( 'Columns', 'thrive-theme' ),
					],
					'extends' => 'Slider',
				],
				'OrderBy'                     => [
					'config'  => [
						'name'    => __( 'Order by', 'thrive-theme' ),
						'options' => [
							[
								'name'  => __( 'Product title', 'thrive-theme' ),
								'value' => 'title',
							],
							[
								'name'  => __( 'Popularity', 'thrive-theme' ),
								'value' => 'popularity',
							],
							[
								'name'  => __( 'Product ID', 'thrive-theme' ),
								'value' => 'id',
							],
							[
								'name'  => __( 'Published date', 'thrive-theme' ),
								'value' => 'date',
							],
							[
								'name'  => __( 'Last modified date', 'thrive-theme' ),
								'value' => 'modified',
							],
							[
								'name'  => __( 'Menu order', 'thrive-theme' ),
								'value' => 'menu_order',
							],
							[
								'name'  => __( 'Price', 'thrive-theme' ),
								'value' => 'price',
							],
							[
								'name'  => __( 'Random', 'thrive-theme' ),
								'value' => 'rand',
							],
						],
						'default' => 'rand',
					],
					'extends' => 'Select',
				],
				'Order'                       => [
					'config'  => [
						'name'    => __( 'Order', 'thrive-theme' ),
						'options' => [
							[
								'name'  => __( 'ASC', 'thrive-theme' ),
								'value' => 'asc',
							],
							[
								'name'  => __( 'DESC', 'thrive-theme' ),
								'value' => 'desc',
							],
						],
						'default' => 'desc',
					],
					'extends' => 'Select',
				],
				'result-count-visibility'     => [
					'config'  => [
						'name'    => '',
						'label'   => __( 'Result count', 'thrive-theme' ),
						'default' => true,
					],
					'extends' => 'Switch',
				],
				'catalog-ordering-visibility' => [
					'config'  => [
						'name'    => '',
						'label'   => __( 'Catalog ordering', 'thrive-theme' ),
						'default' => true,
					],
					'extends' => 'Switch',
				],
				'sale-flash-visibility'       => [
					'config'  => [
						'name'    => '',
						'label'   => __( 'Sale flash ', 'thrive-theme' ),
						'default' => true,
					],
					'extends' => 'Switch',
				],
				'title-visibility'            => [
					'config'  => [
						'name'    => '',
						'label'   => __( 'Title', 'thrive-theme' ),
						'default' => true,
					],
					'extends' => 'Switch',
				],
				'rating-visibility'           => [
					'config'  => [
						'name'    => '',
						'label'   => __( 'Rating', 'thrive-theme' ),
						'default' => true,
					],
					'extends' => 'Switch',
				],
				'price-visibility'            => [
					'config'  => [
						'name'    => '',
						'label'   => __( 'Price', 'thrive-theme' ),
						'default' => true,
					],
					'extends' => 'Switch',
				],
				'cart-visibility'             => [
					'config'  => [
						'name'    => '',
						'label'   => __( 'Add to cart', 'thrive-theme' ),
						'default' => true,
					],
					'extends' => 'Switch',
				],
				'pagination-visibility'       => [
					'config'  => [
						'name'    => '',
						'label'   => __( 'Pagination', 'thrive-theme' ),
						'default' => true,
					],
					'extends' => 'Switch',
				],
				'Alignment'                   => [
					'config'  => [
						'name'    => __( 'Alignment', 'thrive-theme' ),
						'buttons' => [
							[
								'icon'    => 'a_left',
								'value'   => 'left',
								'tooltip' => __( 'Align Left', 'thrive-theme' ),
							],
							[
								'icon'    => 'a_center',
								'value'   => 'center',
								'default' => true,
								'tooltip' => __( 'Align Center', 'thrive-theme' ),
							],
							[
								'icon'    => 'a_right',
								'value'   => 'right',
								'tooltip' => __( 'Align Right', 'thrive-theme' ),
							],
						],
					],
					'extends' => 'ButtonGroup',
				],
				'ImageSize'                   => [
					'config'  => [
						'default' => '100',
						'min'     => '0',
						'max'     => '100',
						'label'   => __( 'Image Size', 'thrive-theme' ),
						'um'      => [ '%' ],
						'css'     => 'width',
					],
					'extends' => 'Slider',
				],
			],
		];

		return $components;
	}

	/**
	 * Element category that will be displayed in the sidebar
	 * @return string
	 */
	public function category() {
		return WooCommerce\Helpers::get_products_category_label();
	}
}

return new Shop_Template( 'shop-template' );
