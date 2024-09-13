<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Dynamic_Styled_List_Element
 */
class Thrive_Dynamic_Styled_List_Element extends Thrive_Theme_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Dynamic Styled List', 'thrive-theme' );
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'dynamic-styled-list';
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrive-dynamic-list';
	}

	/**
	 * HTML layout of the element for when it's dragged in the canvas
	 *
	 * @return string
	 */
	public function html() {
		$extra_attr = '';
		$attr       = [ 'icon' => 'icon-angle-right-light' ];

		foreach ( $attr as $key => $value ) {
			$extra_attr .= 'data-' . $key . '="' . $value . '" ';
		}

		return tcb_template( 'elements/element-placeholder', [
			'icon'       => $this->icon(),
			'class'      => str_replace( [ ',.', ',', '.' ], [ ' ', '', '' ], $this->identifier() ),
			'title'      => __( 'Select List Type', 'thrive-theme' ),
			'extra_attr' => $extra_attr,
		], true );
	}

	/**
	 * Types of lists to display
	 *
	 * @return array
	 */
	public static function get_list_type_options() {
		$options = [
			'category' => [ 'name' => 'Categories List', 'value' => 'categories', 'plural_label' => 'Categories', 'label' => 'Categories List' ],
			'tag'      => [ 'name' => 'Tags List', 'value' => 'tags', 'plural_label' => 'Tags', 'label' => 'Tags List' ],
			'author'   => [ 'name' => 'Authors List', 'value' => 'authors', 'label' => 'Authors List', 'plural_label' => 'Authors' ],
			'month'    => [ 'name' => 'Monthly List', 'value' => 'monthly_list', 'plural_label' => 'Months', 'label' => 'Monthly List' ],
			'meta'     => [ 'name' => 'Meta List', 'value' => 'meta_list', 'label' => 'Meta List', 'plural_label' => 'Metas' ],
			'page'     => [ 'name' => 'Pages List', 'value' => 'pages', 'label' => 'Pages List', 'plural_label' => 'Pages' ],
			'comment'  => [ 'name' => 'Recent Comments List', 'value' => 'comments', 'label' => 'Recent Comments List', 'plural_label' => 'Recent Comments' ],
			'post'     => [ 'name' => 'Recent Posts', 'value' => 'post', 'label' => 'Recent Posts', 'plural_label' => 'Posts' ],
		];

		return $options;
	}

	/**
	 * Default components that most theme elements use
	 *
	 * @return array
	 */
	public function own_components() {
		return [
			'thrive_dynamic_list' => [
				'config' => [
					'Limit'       => [
						'config'  => [
							'default' => '5',
							'min'     => '1',
							'max'     => '100',
							'label'   => __( 'Max items to display', 'thrive-theme' ),
							'um'      => [],
						],
						'extends' => 'Slider',
					],
					'ListLayout'  => [
						'config'  => [
							'name'    => __( 'Layout', 'thrive-theme' ),
							'buttons' => [
								[
									'icon'    => '',
									'text'    => 'Vertical',
									'value'   => 'vertical',
									'default' => true,
								],
								[
									'icon'  => '',
									'text'  => 'Horizontal',
									'value' => 'horizontal',
								],
							],
						],
						'extends' => 'ButtonGroup',
					],
					'EnableIcons' => [
						'config'  => [
							'name'    => '',
							'label'   => __( 'Enable Icons for List Items', 'thrive-theme' ),
							'default' => true,
						],
						'extends' => 'Switch',
					],
					'ModalPicker' => [
						'config' => [
							'label' => __( 'Change all icons', 'thrive-theme' ),
						],
					],
					'ListSpacing' => [
						'css_suffix' => ' .thrive-dynamic-styled-list-item',
						'config'     => [
							'default' => '20',
							'min'     => '1',
							'max'     => '100',
							'label'   => __( 'List Item Spacing', 'thrive-theme' ),
							'um'      => [ 'px', 'em' ],
							'css'     => 'margin-bottom',

						],
						'extends'    => 'Slider',
					],
				],
			],
			'typography'          => [
				'disabled_controls' => [
					'[data-value="tcb-typography-line-height"] ',
					'.tve-advanced-controls',
					'p_spacing',
					'h1_spacing',
					'h2_spacing',
					'h3_spacing',
					'[data-view="LineHeight"]',
				],
				'config'            => [
					'TextAlign' => [
						'css_suffix'   => ' .thrive-dynamic-styled-list-item',
						'property'     => 'justify-content',
						'property_val' => [
							'left'    => 'flex-start',
							'center'  => 'center',
							'right'   => 'flex-end',
							'justify' => 'space-evenly',
						],
					],
				],
			],
			'layout'              => [
				'disabled_controls' => [
					'Display',
					'.tve-advanced-controls',
				],
			],
			'background'          => [
				'disabled_controls' => [
					'.video-bg',
				],
			],
			'animation'           => [
				'disabled_controls' => [
					'.btn-inline:not(.anim-animation):not(.anim-popup)',
				],
			],
			'responsive'          => [ 'hidden' => true ],
			'shadow'              => [ 'hidden' => true ],
			'styles-templates'    => [ 'hidden' => true ],
		];
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
		return 'thrive_dynamic_list';
	}
}

return new Thrive_Dynamic_Styled_List_Element( 'thrive_dynamic_list' );
