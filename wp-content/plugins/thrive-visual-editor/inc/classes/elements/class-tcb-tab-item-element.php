<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TCB_Tab_Item_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Tab Item', 'thrive-cb' );
	}


	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve_tab_title_item';
	}


	public function hide() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function expanded_state_label() {
		return __( 'Selected', 'thrive-cb' );
	}

	public function own_components() {
		$prefix_config = tcb_selection_root() . ' ';
		$suffix        = ' .tve_tab_title > .tve-tab-text';

		return array(
			'tab_item'   => array(
				'config' => array(
					'TextTypeDropdown' => array(
						'config'  => array(
							'default'     => 'none',
							'name'        => __( 'Change text type', 'thrive-cb' ),
							'label_col_x' => 6,
							'options'     => array(
								array(
									'name'  => __( 'Heading 1', 'thrive-cb' ),
									'value' => 'h1',
								),
								array(
									'name'  => __( 'Heading 2', 'thrive-cb' ),
									'value' => 'h2',
								),
								array(
									'name'  => __( 'Heading 3', 'thrive-cb' ),
									'value' => 'h3',
								),
								array(
									'name'  => __( 'Heading 4', 'thrive-cb' ),
									'value' => 'h4',
								),
								array(
									'name'  => __( 'Heading 5', 'thrive-cb' ),
									'value' => 'h5',
								),
								array(
									'name'  => __( 'Heading 6', 'thrive-cb' ),
									'value' => 'h6',
								),
								array(
									'name'  => __( 'Paragraph', 'thrive-cb' ),
									'value' => 'p',
								),
								array(
									'name'  => __( 'Plain text', 'thrive-cb' ),
									'value' => 'span',
								),
							),
						),
						'extends' => 'Select',
					),
					'HasIcon'          => array(
						'config'  => array(
							'label' => __( 'Show Icon', 'thrive-cb' ),
						),
						'extends' => 'Switch',
					),
					'HasImage'         => array(
						'config'  => array(
							'label' => __( 'Show Tab Image', 'thrive-cb' ),
						),
						'extends' => 'Switch',
					),
					'ColorPicker'      => array(
						'config'     => array(
							'label'   => __( 'Icon Color', 'thrive-cb' ),
							'options' => [ 'noBeforeInit' => false ],
						),
						'css_suffix' => ' .tve-tab-icon',
					),
					'Slider'           => array(
						'config'     => array(
							'default' => 20,
							'min'     => 1,
							'max'     => 100,
							'label'   => __( 'Size', 'thrive-cb' ),
							'um'      => [ 'px' ],
							'css'     => 'fontSize',
						),
						'extends'    => 'Slider',
						'css_suffix' => ' .tve-tab-icon',
						'css_prefix' => $prefix_config,
					),
				),
			),
			'typography' => [
				'config'            => [
					'FontColor'     => [
						'css_suffix' => $suffix,
						'important'  => true,
					],
					'FontSize'      => [
						'css_suffix' => $suffix,
						'important'  => true,
					],
					'TextStyle'     => [
						'css_suffix' => $suffix,
						'css_prefix' => $prefix_config,
						'important'  => true,
					],
					'LineHeight'    => [
						'css_suffix' => $suffix,
						'important'  => true,
					],
					'FontFace'      => [
						'css_suffix' => $suffix,
						'important'  => true,
					],
					'LetterSpacing' => [
						'css_suffix' => $suffix,
						'css_prefix' => $prefix_config,
						'important'  => true,
					],
					'TextTransform' => [
						'css_suffix' => $suffix,

						'important' => true,
					],
				],
				'disabled_controls' => [ 'TextAlign', '.tve-advanced-controls' ],
			],
			'animation'  => [ 'hidden' => true ],
			'layout'     => [
				'disabled_controls' => [
					'Alignment',
					'Display',
					'.tve-advanced-controls',
				],
				'config'            => [
					'MarginAndPadding' => [
						'css_prefix' => $prefix_config,
						'important'  => true,
					],
					'Width'            => [
						'css_prefix' => $prefix_config,
						'important'  => true,
					],
					'Height'           => [
						'css_prefix' => $prefix_config,
						'important'  => true,
					],
				],
			],
			'background' => [
				'config' => [
					'ColorPicker' => [ 'css_prefix' => $prefix_config ],
					'PreviewList' => [ 'css_prefix' => $prefix_config ],
				],
			],
			'borders'    => [
				'config' => [
					'Borders' => [
						'important'  => true,
						'css_prefix' => $prefix_config,
					],
					'Corners' => [
						'important'  => true,
						'css_prefix' => $prefix_config,
					],
				],
			],
			'shadow'     => [ 'config' => [ 'css_prefix' => $prefix_config ] ],
			'responsive' => [ 'hidden' => true ],
		);
	}


	/**
	 * @inheritDoc
	 */
	public function expanded_state_config() {
		return true;
	}


	/**
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}
}
