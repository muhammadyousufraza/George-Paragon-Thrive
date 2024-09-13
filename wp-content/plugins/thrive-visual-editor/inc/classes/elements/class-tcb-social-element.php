<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Social_Element
 */
class TCB_Social_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Social Share', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'social';
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'social_share';
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_social.thrv_social_custom';
	}

	/**
	 * The HTML is generated from js
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
		$components = array(
			'social'     => array(
				'config' => array(
					'CustomBranding'      => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Custom branding', 'thrive-cb' ),
							'default' => true,
						),
						'extends' => 'Switch',
					),
					'CssVarChanger'       => array(
						'config'  => array(
							'label'    => __( 'Colors', 'thrive-cb' ),
							'variable' => '--tcb-social-share-master-color',
						),
						'extends' => 'CssVariablesChanger',
					),
					'SocialSharePalettes' => [
						'config'    => [],
						'extends'   => 'Palettes',
						'important' => true,
					],
					'style'               => array(
						'config' => array(
							'label' => __( 'Style', 'thrive-cb' ),
						),
					),
					'stylePicker'         => array(
						'config' => array(
							'label' => __( 'Change style', 'thrive-cb' ),
							'items' => [
								'tve_style_6'  => 'Style 1',
								'tve_style_7'  => 'Style 2',
								'tve_style_8'  => 'Style 3',
								'tve_style_9'  => 'Style 4',
								'tve_style_10' => 'Style 5',
								'tve_style_11' => 'Style 6',
								'tve_style_12' => 'Style 7',
								'tve_style_13' => 'Style 8',
								'tve_style_14' => 'Style 9',
								'tve_style_15' => 'Style 10',
								'tve_style_16' => 'Style 11',
								'tve_style_17' => 'Style 12',
								'tve_style_18' => 'Style 13',
								'tve_style_1'  => 'Style 14',
								'tve_style_2'  => 'Style 15',
								'tve_style_3'  => 'Style 16',
								'tve_style_4'  => 'Style 17',
								'tve_style_5'  => 'Style 18',
							],
						),
					),
					'type'                => array(
						'config' => array(
							'full-width' => true,
							'name'       => __( 'Type', 'thrive-cb' ),
							'buttons'    => array(
								array( 'value' => 'tve_social_ib', 'text' => __( 'Icon only', 'thrive-cb' ) ),
								array( 'value' => 'tve_social_itb', 'text' => __( 'Icon + text', 'thrive-cb' ), 'default' => true ),
								array( 'value' => 'tve_social_cb', 'text' => __( 'Counter', 'thrive-cb' ) ),
							),
						),
					),
					'orientation'         => array(
						'config' => array(
							'name'    => __( 'Orientation', 'thrive-cb' ),
							'buttons' => array(
								array( 'value' => 'h', 'text' => __( 'Horizontal', 'thrive-cb' ), 'default' => true ),
								array( 'value' => 'v', 'text' => __( 'Vertical', 'thrive-cb' ) ),
							),
						),
					),
					'size'                => array(
						'config' => array(
							'default' => '25',
							'min'     => '1',
							'max'     => '60',
							'label'   => __( 'Size and Align', 'thrive-cb' ),
							'um'      => [ 'px' ],
						),
					),
					'Align'               => array(
						'config' => array(
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
								array(
									'text'    => 'FULL',
									'value'   => 'full',
									'tooltip' => __( 'Full Width', 'thrive-cb' ),
								),
							),
						),
					),
					'CommonButtonWidth'   => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Common Button Width', 'thrive-cb' ),
							'default' => true,
						),
						'extends' => 'Switch',
					),
					'preview'             => [
						'config' => [
							'sortable'      => true,
							'settings_icon' => 'pen-regular',
							'tpl'           => 'controls/preview-check-list-item',
						],
					],
					'has_custom_url'      => array(
						'config' => array(
							'label' => __( 'Custom share URL' ),
						),
					),
					'custom_url'          => array(
						'config' => array(
							'placeholder' => __( 'http://', 'thrive-cb' ),
						),
					),
					'counts'              => [
						'config' => [
							'min'     => 0,
							'max'     => 2000,
							'default' => 0,
						],
					],
					'total_share'         => array(
						'config' => array(
							'label' => __( 'Show share count', 'thrive-cb' ),
						),
					),
				),
				'order'  => 1,
			),
			'shadow'     => [ 'hidden' => true ],
			'typography' => [
				'hidden' => true,
			],
			'animation'  => [
				'hidden' => true,
			],
			'layout'     => [
				'disabled_controls' => [
					'Width',
					'Height',
					'Overflow',
					'ScrollStyle',
				],
			],
		);

		return array_merge( $components, $this->group_component() );
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return static::get_thrive_advanced_label();
	}

	/**
	 * Group Edit Properties
	 *
	 * @return array|bool
	 */
	public function has_group_editing() {
		return array(
			'select_values' => array(
				array(
					'value'    => 'social_options',
					'selector' => '.tve_s_item',
					'name'     => __( 'Grouped Social Labels', 'thrive-cb' ),
					'singular' => __( '-- Option Label %s', 'thrive-cb' ),
				),
				array(
					'value'    => 'social_buttons',
					'selector' => '.tve_share_item .tve_s_icon',
					'name'     => __( 'Grouped Social Icons', 'thrive-cb' ),
					'singular' => __( '-- Option Icon %s', 'thrive-cb' ),
				),
			),
		);
	}

	/**
	 * Element info
	 *
	 * @return string|string[][]
	 */
	public function info() {
		return [
			'instructions' => [
				'type' => 'help',
				'url'  => 'social_share',
				'link' => 'https://help.thrivethemes.com/en/articles/4425796-how-to-use-the-social-share-element',
			],
		];
	}
}
