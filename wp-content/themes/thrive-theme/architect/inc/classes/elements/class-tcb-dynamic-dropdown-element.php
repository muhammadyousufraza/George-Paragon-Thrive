<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TCB_Dynamic_Dropdown_Element extends TCB_Element_Abstract {

	public function name() {
		return __( 'Dropdown Field', 'thrive-cb' );
	}

	/**
	 * @inheritDoc
	 */
	public function expanded_state_apply_inline() {
		return true;
	}

	public function identifier() {
		return '.tve-dynamic-dropdown:not(.tcb-filter-dropdown)';
	}

	public function hide() {
		return true;
	}

	/**
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function expanded_state_config() {
		return true;
	}

	public function own_components() {
		$dropdown_svg   = $this->get_icon_styles();
		$dropdown_icons = [];
		foreach ( $dropdown_svg as $key => & $dropdown_icon ) {
			$dropdown_icons[ $key ] = $dropdown_icon['label'];
			unset( $dropdown_icon['label'] );
		}
		unset( $dropdown_icon );

		$controls_default_config = array(
			'css_suffix' => ' span',
			'css_prefix' => tcb_selection_root() . ' ',
		);

		$components = array(
			'dynamic_dropdown' => array(
				'config' => array(
					'PlaceholderInput'  => [
						'config'  => [
							'label' => 'Placeholder',
						],
						'extends' => 'LabelInput',
					],
					'StyleChange'       => array(
						'config' => array(
							'label'   => __( 'Dropdown style', 'thrive-cb' ),
							'preview' => [
								'key'   => '',
								'label' => 'default',
							],
						),
					),
					'Width'             => array(
						'config'  => array(
							'default' => '0',
							'min'     => '10',
							'max'     => '500',
							'label'   => __( 'Width', 'thrive-cb' ),
							'um'      => [ '%', 'px' ],
							'css'     => 'max-width',
						),
						'extends' => 'Slider',
					),
					'SelectStylePicker' => array(
						'config' => array(
							'label'   => __( 'Choose dropdown style', 'thrive-cb' ),
							'items'   => array(
								'default'  => array(
									'label' => __( 'Default', 'thrive-cb' ),
								),
								'style-1'  => array(
									'label' => __( 'Style 1', 'thrive-cb' ),
								),
								'style-2'  => array(
									'label' => __( 'Style 2', 'thrive-cb' ),
								),
								'style-3'  => array(
									'label' => __( 'Style 3', 'thrive-cb' ),
								),
								'style-4'  => array(
									'label' => __( 'Style 4', 'thrive-cb' ),
								),
								'style-5'  => array(
									'label' => __( 'Style 5', 'thrive-cb' ),
								),
								'style-6'  => array(
									'label' => __( 'Style 6', 'thrive-cb' ),
								),
								'style-7'  => array(
									'label' => __( 'Style 7', 'thrive-cb' ),
								),
								'style-8'  => array(
									'label' => __( 'Style 8', 'thrive-cb' ),
								),
								'style-9'  => array(
									'label' => __( 'Style 9', 'thrive-cb' ),
								),
								'style-10' => array(
									'label' => __( 'Style 10', 'thrive-cb' ),
								),
								'style-11' => array(
									'label' => __( 'Style 11', 'thrive-cb' ),
								),
								'style-12' => array(
									'label' => __( 'Style 12', 'thrive-cb' ),
								),
								'style-13' => array(
									'label' => __( 'Style 13', 'thrive-cb' ),
								),
								'style-14' => array(
									'label' => __( 'Style 14', 'thrive-cb' ),
								),
								'style-15' => array(
									'label' => __( 'Style 15', 'thrive-cb' ),
								),
							),
							'default' => 'default',
						),
					),
					'DropdownIcon'      => array(
						'config' => array(
							'name'    => __( 'Dropdown icon', 'thrive-cb' ),
							'options' => $dropdown_icons,
						),
						'paths'  => $dropdown_svg,
					),
					'DropdownAnimation' => array(
						'config'  => array(
							'name'    => __( 'Dropdown animation', 'thrive-cb' ),
							'options' => array(
								''         => __( 'None (instant)', 'thrive-cb' ),
								'da-fade'  => __( 'Fade In and Out ', 'thrive-cb' ),
								'da-slide' => __( 'Slide Down', 'thrive-cb' ),
								'da-fold'  => __( 'Fold Out', 'thrive-cb' ),
							),
						),
						'extends' => 'Select',
					),
				),
			),
			'typography'       => [
				'disabled_controls' => [ 'TextAlign' ],
				'config'            => [
					'FontSize'      => $controls_default_config,
					'FontColor'     => $controls_default_config,
					'FontFace'      => $controls_default_config,
					'LetterSpacing' => $controls_default_config,
					'LineHeight'    => $controls_default_config,
					'TextAlign'     => $controls_default_config,
					'TextStyle'     => $controls_default_config,
					'TextTransform' => $controls_default_config,
				],
			],
			'layout'           => [
				'disabled_controls' => [
					'Width',
					'Height',
					'Alignment',
					'.tve-advanced-controls',
				],
			],
			'animation'        => [
				'hidden' => true,
			],
			'styles-templates' => [
				'config' => [
					'to' => 'select',
				],
			],
			'responsive'       => [
				'hidden' => true,
			],
			'borders'          => array(
				'config' => array(
					'Borders' => array(
						'css_prefix' => tcb_selection_root() . ' ',
						'important'  => true,
					),
					'Corners' => array(
						'css_prefix' => tcb_selection_root() . ' ',
						'important'  => true,
					),
				),
			),
			'shadow'           => array(
				'config' => array(
					'css_prefix' => tcb_selection_root() . ' ',
					'important'  => true,
				),
			),

		);

		return array_merge( $components, $this->group_component() );
	}

	public function get_icon_styles() {
		return [
			'style_1' => [
				'label' => 'Angle',
				'up'    => '<path d="M151.5 347.8L3.5 201c-4.7-4.7-4.7-12.3 0-17l19.8-19.8c4.7-4.7 12.3-4.7 17 0L160 282.7l119.7-118.5c4.7-4.7 12.3-4.7 17 0l19.8 19.8c4.7 4.7 4.7 12.3 0 17l-148 146.8c-4.7 4.7-12.3 4.7-17 0z"/>',
				'box'   => '0 0 320 512',
			],
			'style_2' => [
				'label' => 'Chevron',
				'up'    => '<path d="M443.5 162.6l-7.1-7.1c-4.7-4.7-12.3-4.7-17 0L224 351 28.5 155.5c-4.7-4.7-12.3-4.7-17 0l-7.1 7.1c-4.7 4.7-4.7 12.3 0 17l211 211.1c4.7 4.7 12.3 4.7 17 0l211-211.1c4.8-4.7 4.8-12.3.1-17z"/>',
				'box'   => '0 0 448 512',
			],
			'style_3' => [
				'label' => 'Caret',
				'up'    => '<path d="M31.3 192h257.3c17.8 0 26.7 21.5 14.1 34.1L174.1 354.8c-7.8 7.8-20.5 7.8-28.3 0L17.2 226.1C4.6 213.5 13.5 192 31.3 192z"/>',
				'box'   => '0 0 320 512',
			],
			'style_4' => [
				'label' => 'Triangle',
				'up'    => '<path d="M272 160H48.1c-42.6 0-64.2 51.7-33.9 81.9l111.9 112c18.7 18.7 49.1 18.7 67.9 0l112-112c30-30.1 8.7-81.9-34-81.9zM160 320L48 208h224L160 320z"/>',
				'box'   => '0 0 320 512',
			],
		];
	}
}
