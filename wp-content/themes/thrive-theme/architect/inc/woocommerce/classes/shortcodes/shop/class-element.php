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
 * Class Element
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\Shop
 */
class Element extends \TCB_Cloud_Template_Element_Abstract {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Shop', 'thrive-cb' );
	}

	/**
	 * @return string
	 */
	public function icon() {
		return 'shop';
	}

	/**
	 *
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'woocommerce, products';
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return Main::IDENTIFIER;
	}

	/**
	 * @return bool
	 */
	public function is_placeholder() {
		return false;
	}

	/**
	 * Show/hide this element ( right now it's being hidden on the shop templates from TTB )
	 *
	 * @return bool
	 */
	public function hide() {
		return apply_filters( 'tcb_woo_shop_hide_element', false );
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = array(
			'typography'       => [ 'hidden' => true ],
			'animation'        => [ 'hidden' => true ],
			'shadow'           => [ 'hidden' => true ],
			'responsive'       => [ 'hidden' => true ],
			'styles-templates' => [ 'hidden' => true ],
			'shop'             => array(
				'config' => array(
					'Limit'                       => array(
						'config'  => array(
							'min'   => '1',
							'max'   => '24',
							'um'    => [],
							'label' => __( 'Products per page', 'thrive-cb' ),
						),
						'extends' => 'Slider',
					),
					'Columns'                     => array(
						'config'  => array(
							'min'   => '1',
							'max'   => '8',
							'um'    => [],
							'label' => __( 'Columns', 'thrive-cb' ),
						),
						'extends' => 'Slider',
					),
					'OrderBy'                     => array(
						'config'  => array(
							'name'    => __( 'Order by', 'thrive-cb' ),
							'options' => array(
								array(
									'name'  => __( 'Product title', 'thrive-cb' ),
									'value' => 'title',
								),
								array(
									'name'  => __( 'Popularity', 'thrive-cb' ),
									'value' => 'popularity',
								),
								array(
									'name'  => __( 'Product ID', 'thrive-cb' ),
									'value' => 'id',
								),
								array(
									'name'  => __( 'Published date', 'thrive-cb' ),
									'value' => 'date',
								),
								array(
									'name'  => __( 'Last modified date', 'thrive-cb' ),
									'value' => 'modified',
								),
								array(
									'name'  => __( 'Menu order', 'thrive-cb' ),
									'value' => 'menu_order',
								),
								array(
									'name'  => __( 'Price', 'thrive-cb' ),
									'value' => 'price',
								),
								array(
									'name'  => __( 'Random', 'thrive-cb' ),
									'value' => 'rand',
								),
							),
							'default' => 'rand',
						),
						'extends' => 'Select',
					),
					'Order'                       => array(
						'config'  => array(
							'name'    => __( 'Order', 'thrive-cb' ),
							'options' => array(
								array(
									'name'  => __( 'ASC', 'thrive-cb' ),
									'value' => 'asc',
								),
								array(
									'name'  => __( 'DESC', 'thrive-cb' ),
									'value' => 'desc',
								),
							),
							'default' => 'desc',
						),
						'extends' => 'Select',
					),
					'result-count-visibility'     => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Result count', 'thrive-cb' ),
							'default' => true,
						),
						'extends' => 'Switch',
					),
					'catalog-ordering-visibility' => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Catalog ordering', 'thrive-cb' ),
							'default' => true,
						),
						'extends' => 'Switch',
					),
					'sale-flash-visibility'       => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Sale flash ', 'thrive-cb' ),
							'default' => true,
						),
						'extends' => 'Switch',
					),
					'title-visibility'            => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Title', 'thrive-cb' ),
							'default' => true,
						),
						'extends' => 'Switch',
					),
					'rating-visibility'           => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Rating', 'thrive-cb' ),
							'default' => true,
						),
						'extends' => 'Switch',
					),
					'price-visibility'            => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Price', 'thrive-cb' ),
							'default' => true,
						),
						'extends' => 'Switch',
					),
					'cart-visibility'             => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Add to cart', 'thrive-cb' ),
							'default' => true,
						),
						'extends' => 'Switch',
					),
					'pagination-visibility'       => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Pagination', 'thrive-cb' ),
							'default' => true,
						),
						'extends' => 'Switch',
					),
					'Alignment'                   => array(
						'config'  => array(
							'name'    => __( 'Alignment', 'thrive-cb' ),
							'buttons' => array(
								array(
									'icon'    => 'a_left',
									'value'   => 'left',
									'tooltip' => __( 'Align Left', 'thrive-cb' ),
								),
								array(
									'icon'    => 'a_center',
									'value'   => 'center',
									'default' => true,
									'tooltip' => __( 'Align Center', 'thrive-cb' ),
								),
								array(
									'icon'    => 'a_right',
									'value'   => 'right',
									'tooltip' => __( 'Align Right', 'thrive-cb' ),
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'ImageSize'                   => array(
						'config'  => array(
							'default' => '100',
							'min'     => '0',
							'max'     => '100',
							'label'   => __( 'Image Size', 'thrive-cb' ),
							'um'      => [ '%' ],
							'css'     => 'width',
						),
						'extends' => 'Slider',
					),
					'cat_operator'                => array(
						'config'  => array(
							'name'       => __( 'Category operator', 'thrive-cb' ),
							'full-width' => true,
							'buttons'    => array(
								array(
									'text'  => __( 'Any', 'thrive-cb' ),
									'value' => 'in',
								),
								array(
									'text'  => __( 'Exclude', 'thrive-cb' ),
									'value' => 'not in',
								),
								array(
									'text'  => __( 'All', 'thrive-cb' ),
									'value' => 'and',
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'tag_operator'                => array(
						'config'  => array(
							'name'       => __( 'Tag operator', 'thrive-cb' ),
							'full-width' => true,
							'buttons'    => array(
								array(
									'text'  => __( 'Any', 'thrive-cb' ),
									'value' => 'in',
								),
								array(
									'text'  => __( 'Exclude', 'thrive-cb' ),
									'value' => 'not in',
								),
								array(
									'text'  => __( 'All', 'thrive-cb' ),
									'value' => 'and',
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'taxonomy'                    => array(
						'config'  => array(
							'name'       => __( 'Taxonomy', 'thrive-cb' ),
							'full-width' => true,
							'options'    => array_merge(
								array(
									array(
										'name'     => __( 'None', 'thrive-cb' ),
										'value'    => '',
										'disabled' => true,
									),
								),
								Main::get_product_taxonomies()
							),
						),
						'extends' => 'Select',
					),
					'terms_operator'              => array(
						'config'  => array(
							'name'       => __( 'Terms operator', 'thrive-cb' ),
							'full-width' => true,
							'buttons'    => array(
								array(
									'text'  => __( 'Any', 'thrive-cb' ),
									'value' => 'in',
								),
								array(
									'text'  => __( 'Exclude', 'thrive-cb' ),
									'value' => 'not in',
								),
								array(
									'text'  => __( 'All', 'thrive-cb' ),
									'value' => 'and',
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
				),
			),
			'layout'           => [
				'disabled_controls' => [],
			],
		);

		return $components;
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return 'WooCommerce';
	}
}

return new Element( 'shop' );
