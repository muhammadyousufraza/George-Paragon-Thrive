<?php

require_once plugin_dir_path( __FILE__ ) . 'class-tcb-menu-item-element.php';

/**
 * Class TCB_Label_Disabled_Element
 *
 * Non edited label element. For inline text we use typography control
 */
class TCB_Megamenu_Item_Element extends TCB_Menu_Item_Element {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Mega Menu Item', 'thrive-cb' );
	}

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tcb-mega-drop li a';
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
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$typography_defaults = [
			'css_prefix' => '',
			'css_suffix' => '',
			'important'  => true,
		];

		return array(
			'megamenu_item' => array(
				'config' => array(
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
					'ImageSide'    => [
						'extends' => 'ButtonGroup',
					],
					'ColorPicker'  => array(
						'css_suffix' => ' .m-icon',
						'config'     => array(
							'label'     => __( 'Icon Color', 'thrive-cb' ),
							'important' => true,
						),
					),
					'Slider'       => array(
						'css_suffix' => ' .m-icon',
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
			'typography'    => [
				'disabled_controls' => [
					'.tve-advanced-controls',
				],
				'config'            => [
					'FontColor'     => $typography_defaults,
					'FontSize'      => $typography_defaults,
					'FontFace'      => $typography_defaults,
					'TextStyle'     => $typography_defaults,
					'LineHeight'    => $typography_defaults,
					'LetterSpacing' => $typography_defaults,
					'TextTransform' => $typography_defaults,
				],
			],
			'background'    => [
				'config' => [
					'ColorPicker' => [
						'config' => [
							'important' => false,
						],
					],
				],
			],
			'layout'        => [
				'disabled_controls' => [
					'.tve-advanced-controls',
					'Alignment',
					'Display',
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
}
