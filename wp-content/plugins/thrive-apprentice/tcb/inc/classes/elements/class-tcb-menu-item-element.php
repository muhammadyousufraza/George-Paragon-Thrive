<?php

require_once plugin_dir_path( __FILE__ ) . 'class-tcb-label-element.php';

/**
 * Class TCB_Label_Disabled_Element
 *
 * Non edited label element. For inline text we use typography control
 */
class TCB_Menu_Item_Element extends TCB_Label_Element {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Menu Item', 'thrive-cb' );
	}

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_widget_menu li';
	}

	/**
	 * There is no need for HTML for this element since we need it only for control filter
	 *
	 * @return string
	 */
	protected function html() {
		return '';
	}

	/**
	 * Removes the unnecessary components from the element json string
	 *
	 * @return array
	 */
	protected function general_components() {
		$general_components                                      = parent::general_components();
		$general_components['animation']['config']['hide_items'] = [ 'animation', 'tooltip', 'link' ];

		unset( $general_components['responsive'], $general_components['styles-templates'] );

		return $general_components;
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'menu_item'  => array(
				'config' => array(
					'HoverEffect'  => array(
						'config'  => array(
							'name'    => __( 'Hover Effect', 'thrive-cb' ),
							'options' => [
								''            => 'None',
								'c-underline' => 'Underline',
								'c-double'    => 'Double line',
								'c-brackets'  => 'Brackets',
								'c-thick'     => 'Thick Underline',
							],
						),
						'extends' => 'Select',
					),
					'StyleChange'  => array(
						'config' => array(
							'label'      => __( 'Item style', 'thrive-cb' ),
							'label_none' => __( 'Choose...', 'thrive-cb' ),
						),
					),
					'StylePicker'  => array(
						'config' => array(
							'label' => __( 'Choose item style', 'thrive-cb' ),
							'items' => $this->get_templates(),
						),
					),
					'HasIconImage' => array(
						'config'  => array(
							'name'    => __( 'Display', 'thrive-cb' ),
							'options' => array(
								'text'       => __( 'Text only', 'thrive-cb' ),
								'icon'       => __( 'Icon only', 'thrive-cb' ),
								'icon-text'  => __( 'Icon and text', 'thrive-cb' ),
								'image'      => __( 'Image only', 'thrive-cb' ),
								'image-text' => __( 'Image and text', 'thrive-cb' ),
							),
						),
						'extends' => 'Select',
					),
					'Display'      => array(
						'config'  => array(
							'name'    => __( 'Show when', 'thrive-cb' ),
							'options' => array(
								'always'     => __( 'Always', 'thrive-cb' ),
								'logged-in'  => __( 'Logged in', 'thrive-cb' ),
								'logged-out' => __( 'Logged out', 'thrive-cb' ),
							),
						),
						'extends' => 'Select',
					),
					'ImageSide'    => [
						'extends' => 'ButtonGroup',
					],
					'ColorPicker'  => array(
						'css_suffix' => ' > a .m-icon',
						'config'     => array(
							'label'   => __( 'Icon Color', 'thrive-cb' ),
							'options' => [ 'noBeforeInit' => false ],
						),
					),
					'Slider'       => array(
						'css_suffix' => ' > a .m-icon',
						'config'     => array(
							'default' => 30,
							'min'     => 1,
							'max'     => 50,
							'label'   => __( 'Size', 'thrive-cb' ),
							'um'      => [ 'px' ],
							'css'     => 'fontSize',
						),
					),
				),
			),
			'typography' => [
				'disabled_controls' => [
					'.tve-advanced-controls',
				],
				'config'            => [
					'FontColor'     => [
						'css_prefix' => '',
						'css_suffix' => ' > a',
						'important'  => true,
					],
					'FontSize'      => [
						'css_prefix' => '',
						'css_suffix' => ' > a',
						'important'  => true,
					],
					'FontFace'      => [
						'css_prefix' => '',
						'css_suffix' => ' > a',
						'important'  => true,
					],
					'TextStyle'     => [
						'css_suffix' => ' > a',
						'important'  => true,
					],
					'LineHeight'    => [
						'css_suffix' => ' > a',
						'css_prefix' => '',
						'important'  => true,
					],
					'LetterSpacing' => [
						'css_suffix' => ' > a',
						'important'  => true,
					],
					'TextTransform' => [
						'css_suffix' => ' > a',
						'important'  => true,
					],
				],
			],
			'background' => [
				'config' => [
					'ColorPicker' => [
						'config' => [
							'important' => false,
						],
					],
				],
			],
			'layout'     => [
				'disabled_controls' => [
					'margin-top',
					'margin-bottom',
					'.tve-advanced-controls',
					'Alignment',
					'Display',
				],
			],
			'borders'    => [
				'config' => [
					'Corners' => [
						'overflow' => false,
					],
				],
			],
		);
	}

	/**
	 * Get all available menu item templates
	 *
	 * @return array
	 */
	public function get_templates() {
		return get_option( 'tve_menu_item_templates', [] );
	}

	/**
	 * @inheritDoc
	 */
	public function active_state_config() {
		return true;
	}
}
