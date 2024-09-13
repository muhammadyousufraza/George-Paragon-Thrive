<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Integrations\WooCommerce\Shortcodes\Product_Categories;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Element
 *
 * @package TCB\Integrations\WooCommerce\Shortcodes\Product_Categories
 */
class Element extends \TCB_Element_Abstract {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Product Categories', 'thrive-cb' );
	}

	/**
	 * @return string
	 */
	public function icon() {
		return 'product-categories';
	}

	/**
	 *
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'woocommerce, products, categories';
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return Main::IDENTIFIER;
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = array(
			'typography'         => [ 'hidden' => true ],
			'animation'          => [ 'hidden' => true ],
			'shadow'             => [ 'hidden' => true ],
			'responsive'         => [ 'hidden' => true ],
			'styles-templates'   => [ 'hidden' => true ],
			'product-categories' => array(
				'config' => array(
					'Limit'                     => array(
						'config'  => array(
							'min'   => '1',
							'max'   => '24',
							'um'    => [],
							'label' => __( 'Categories to be displayed', 'thrive-cb' ),
						),
						'extends' => 'Slider',
					),
					'Columns'                   => array(
						'config'  => array(
							'min'   => '1',
							'max'   => '8',
							'um'    => [],
							'label' => __( 'Columns', 'thrive-cb' ),
						),
						'extends' => 'Slider',
					),
					'OrderBy'                   => array(
						'config'  => array(
							'name'    => __( 'Order by', 'thrive-cb' ),
							'options' => array(
								array(
									'name'  => __( 'Name', 'thrive-cb' ),
									'value' => 'name',
								),
								array(
									'name'  => __( 'ID', 'thrive-cb' ),
									'value' => 'id',
								),
								array(
									'name'  => __( 'Slug', 'thrive-cb' ),
									'value' => 'slug',
								),
								array(
									'name'  => __( 'Menu order', 'thrive-cb' ),
									'value' => 'menu_order',
								),
							),
							'default' => 'rand',
						),
						'extends' => 'Select',
					),
					'Order'                     => array(
						'config'  => array(
							'name'    => __( 'Order', 'thrive-cb' ),
							'options' => array(
								array(
									'name'  => __( 'A to Z', 'thrive-cb' ),
									'value' => 'asc',
								),
								array(
									'name'  => __( 'Z to A', 'thrive-cb' ),
									'value' => 'desc',
								),
							),
							'default' => 'desc',
						),
						'extends' => 'Select',
					),
					'TextLayout'                => array(
						'config'  => array(
							'name'       => __( 'Text Layout', 'thrive-cb' ),
							'full-width' => true,
							'buttons'    => array(
								array(
									'value'   => 'text_before_image',
									'text'    => __( 'Before Image', 'thrive-cb' ),
									'default' => true,
								),
								array(
									'value'   => 'text_on_image',
									'text'    => __( 'On Image', 'thrive-cb' ),
									'default' => true,
								),
								array(
									'value' => 'text_after_image',
									'text'  => __( 'After Image', 'thrive-cb' ),
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'TextPosition'              => array(
						'config'  => array(
							'name'       => __( 'Text Position', 'thrive-cb' ),
							'full-width' => true,
							'buttons'    => array(
								array(
									'icon'  => 'top',
									'value' => 'top',
								),
								array(
									'icon'  => 'vertical',
									'value' => 'center',
								),
								array(
									'icon'  => 'bot',
									'value' => 'bottom',
								),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'title-visibility'          => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Title', 'thrive-cb' ),
							'default' => true,
						),
						'extends' => 'Switch',
					),
					'product-number-visibility' => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Number of products', 'thrive-cb' ),
							'default' => true,
						),
						'extends' => 'Switch',
					),
					'empty-category'            => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Hide empty categories', 'thrive-cb' ),
							'default' => true,
						),
						'extends' => 'Switch',
					),
					'Alignment'                 => array(
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
					'ImageSize'                 => array(
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
					'ParentCategory'            => array(
						'config'  => array(
							'name'       => __( 'Show child categories of a parent category', 'thrive-cb' ),
							'full-width' => true,
							'options'    => array_merge(
								array(
									array(
										'name'  => __( 'Display top level categories', 'thrive-cb' ),
										'value' => 0,
									),
								),
								Main::get_product_categories_for_select()
							),
						),
						'extends' => 'Select',
					),
				),
			),
			'layout'             => [
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

return new Element( 'product-categories' );
