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
 * Class TCB_Text_Element
 */
class TCB_Text_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Text', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'text';
	}


	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'text';
	}

	/**
	 * Text element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_text_element';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return [
			'text'                => [
				'config' => [
					'ToggleControls'             => [
						'config'  => [
							'buttons' => [
								[ 'value' => 'tcb-text-font-size', 'text' => __( 'Font Size', 'thrive-cb' ), 'default' => true ],
								[ 'value' => 'tcb-text-line-height', 'text' => __( 'Line Height', 'thrive-cb' ) ],
								[ 'value' => 'tcb-text-letter-spacing', 'text' => __( 'Letter Spacing', 'thrive-cb' ) ],
							],
						],
						'extends' => 'ButtonGroup',
					],
					'FontSize'                   => [
						'config'  => [
							'default' => '16',
							'min'     => '1',
							'max'     => '100',
							'label'   => '',
							'um'      => [ 'px', 'em' ],
							'css'     => 'fontSize',
						],
						'extends' => 'FontSize',
					],
					'LineHeight'                 => [
						'css_prefix' => tcb_selection_root() . ' ',
						'config'     => [
							'default' => '1',
							'min'     => '1',
							'max'     => '200',
							'label'   => '',
							'um'      => [ 'em', 'px' ],
							'css'     => 'lineHeight',
						],
						'extends'    => 'LineHeight',
					],
					'LetterSpacing'              => [
						'config'  => [
							'default' => 'auto',
							'min'     => '0',
							'max'     => '100',
							'label'   => '',
							'um'      => [ 'px', 'em' ],
							'css'     => 'letterSpacing',
						],
						'extends' => 'Slider',
					],
					'ToggleColorControls'        => [
						'config'  => [
							'name'    => __( 'Color type', 'thrive-cb' ),
							'buttons' => [
								[ 'value' => 'tcb-text-solid-color', 'text' => __( 'Solid', 'thrive-cb' ) ],
								[ 'value' => 'tcb-text-gradient-color', 'text' => __( 'Gradient', 'thrive-cb' ) ],
							],
						],
						'extends' => 'ButtonGroup',
					],
					'FontColor'                  => [
						'config'  => [
							'default' => '000',
							'label'   => __( 'Color', 'thrive-cb' ),
							'options' => [
								'output' => 'object',
							],
						],
						'extends' => 'ColorPicker',
					],
					'FontGradient'               => [
						'config'  => [
							'default' => '000',
							'label'   => __( 'Gradient', 'thrive-cb' ),
							'options' => [
								'output'   => 'object',
								'hasInput' => true,
							],
						],
						'extends' => 'GradientPicker',
					],
					'FontBaseColor'              => [
						'css_prefix' => '.thrv_text_element ',
						'config'     => [
							'default' => '000',
							'label'   => __( 'Effect color', 'thrive-cb' ),
							'info'    => true,
							'options' => [
								'output' => 'object',
							],
						],
						'extends'    => 'ColorPicker',
					],
					'FontBackground'             => [
						'config'  => [
							'default' => '000',
							'label'   => __( 'Highlight', 'thrive-cb' ),
							'options' => [
								'output' => 'object',
							],
						],
						'extends' => 'ColorPicker',
					],
					'HighlightType'              => [
						'config'  => [
							'name'    => __( 'Type', 'thrive-cb' ),
							'options' => [
								'none'              => __( 'None', 'thrive-cb' ),
								''                  => __( 'Basic', 'thrive-cb' ),
								'rounded-line'      => __( 'Simple underline', 'thrive-cb' ),
								'line'              => __( 'Arched underline', 'thrive-cb' ),
								'regular-line'      => __( 'Low highlight', 'thrive-cb' ),
								'circle'            => __( 'Circle', 'thrive-cb' ),
								'cursive-line'      => __( 'Cursive underline', 'thrive-cb' ),
								'dotted-rectangle'  => __( 'Dotted box', 'thrive-cb' ),
								'double-line'       => __( 'Double underline', 'thrive-cb' ),
								'crossed-lines'     => __( 'Crossed Lines', 'thrive-cb' ),
								'wavy'              => __( 'Fluid wave', 'thrive-cb' ),
								'marker-zig-zag'    => __( 'Zig-zag', 'thrive-cb' ),
								'wavy-2'            => __( 'Unruly wave', 'thrive-cb' ),
								'wavy-3'            => __( 'Curly underline', 'thrive-cb' ),
								'squiggle'          => __( 'Playful underline', 'thrive-cb' ),
								'pen-sketch'        => __( 'Contour sketch', 'thrive-cb' ),
								'divergent'         => __( 'Divergent rays', 'thrive-cb' ),
								'bubble'            => __( 'Bubble', 'thrive-cb' ),
								'brush'             => __( 'Brush stroke', 'thrive-cb' ),
								'marker-background' => __( 'Marker highlights', 'thrive-cb' ),
								'clean-rounded'     => __( 'Rounded Background', 'thrive-cb' ),
								'flat-marker'       => __( 'Messy Marker', 'thrive-cb' ),
							],
						],
						'extends' => 'Select',
					],
					'HighlightStrokeWidth'       => [
						'config'  => [
							'min'   => '1',
							'max'   => '15',
							'label' => __( 'Stroke Width', 'thrive-cb' ),
							'um'    => [ 'px' ],
						],
						'extends' => 'Slider',
					],
					'DasharrayLineLength'        => [
						'config'  => [
							'min'     => '1',
							'max'     => '100',
							'default' => '20',
							'label'   => __( 'Line Length', 'thrive-cb' ),
							'um'      => [ 'px' ],
						],
						'extends' => 'Slider',
					],
					'DasharrayGapLength'         => [
						'config'  => [
							'min'     => '0',
							'max'     => '100',
							'default' => '20',
							'label'   => __( 'Gap Length', 'thrive-cb' ),
							'um'      => [ 'px' ],
						],
						'extends' => 'Slider',
					],
					'HighlightPosition'          => [
						'config'  => [
							'name'  => '',
							'label' => __( 'Display Over Text' ),
						],
						'extends' => 'Switch',
					],
					'DeviceHighlightStatus'      => [
						'config'  => [
							'name'     => __( 'Visible on', 'thrive-cb' ),
							'checkbox' => true,
							'buttons'  => [
								[
									'value'   => 'desktop',
									'icon'    => 'desktop',
									'default' => true,
								],
								[
									'value'   => 'tablet',
									'icon'    => 'tablet2',
									'default' => true,
								],
								[
									'value'   => 'mobile',
									'icon'    => 'mobile',
									'default' => true,
								],
							],
						],
						'extends' => 'ButtonGroup',
					],
					'HighlightAnimation'         => [
						'config'  => [
							'name'    => __( 'Animation', 'thrive-cb' ),
							'options' => [
								''                => __( 'None', 'thrive-cb' ),
								'viewport_once'   => __( 'Once', 'thrive-cb' ),
								'viewport_repeat' => __( 'Repeat on entry', 'thrive-cb' ),
								'loop'            => __( 'Loop', 'thrive-cb' ),
							],
						],
						'extends' => 'Select',
					],
					'HighlightAnimationDuration' => [
						'config'  => [
							'default' => '3000',
							'min'     => '1',
							'max'     => '10000',
							'label'   => __( 'Animation Duration', 'thrive-cb' ),
							'um'      => [ 'ms' ],
						],
						'extends' => 'Slider',
					],
					'HighlightAnimationDelay'    => [
						'config'  => [
							'default' => '1',
							'min'     => '1',
							'max'     => '10000',
							'label'   => __( 'Time before repeating', 'thrive-cb' ),
							'um'      => [ 'ms' ],
						],
						'extends' => 'Slider',
					],
					'FontFace'                   => [
						'css_prefix' => tcb_selection_root() . ' ',
						'config'     => [
							'template' => 'controls/font-manager',
							'inline'   => true,
						],
					],
					'TextStyle'                  => [
						'css_prefix' => tcb_selection_root() . ' ',
						'config'     => [
							'important' => true,
						],
					],
					'TextTransform'              => [
						'config'  => [
							'name'    => 'Transform',
							'buttons' => [
								[
									'icon'    => 'none',
									'text'    => '',
									'value'   => 'none',
									'default' => true,
								],
								[
									'icon'  => 'format-all-caps',
									'text'  => '',
									'value' => 'uppercase',
								],
								[
									'icon'  => 'format-capital',
									'text'  => '',
									'value' => 'capitalize',
								],
								[
									'icon'  => 'format-lowercase',
									'text'  => '',
									'value' => 'lowercase',
								],
							],
						],
						'extends' => 'ButtonGroup',
					],
					'LineSpacing'                => [
						'css_prefix' => tcb_selection_root() . ' ',
						'config'     => [
							'important' => true,
						],
					],
					'HeadingToggle'              => [
						'config'  => [
							'label' => __( 'Include heading in table of contents element (if eligible)', 'thrive-cb' ),
						],
						'extends' => 'Switch',
					],
					'HeadingRename'              => [
						'config'  => [
							'label' => __( 'Customize heading label', 'thrive-cb' ),
						],
						'extends' => 'Switch',
					],
					'HeadingAltText'             => [
						'config'  => [
							'placeholder' => __( 'Enter heading to be displayed', 'thrive-cb' ),
						],
						'extends' => 'LabelInput',
					],
				],
			],
			'layout'              => [
				'config'            => [
					'MarginAndPadding' => [],
					'Position'         => [
						'important' => true,
					],
				],
				'disabled_controls' => [
					'Overflow',
					'ScrollStyle',
				],
			],
			'borders'             => [
				'config' => [
					'Borders' => [
						'important' => true,
					],
					'Corners' => [
						'important' => true,
					],
				],
			],
			'shadow'              => [
				'config' => [
					'important'   => true,
					'with_froala' => true,
				],
			],
			'typography'          => [
				'hidden' => true,
			],
			'animation'           => [
				'disabled_controls' => [
					'.btn-inline:not(.anim-animation)',
				],
			],
			'scroll'              => [
				'hidden'            => false,
				'disabled_controls' => [ '[data-value="sticky"]' ],
			],
			'conditional-display' => [
				'hidden' => false,
			],
		];
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return static::get_thrive_basic_label();
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
				'url'  => 'text',
				'link' => 'https://help.thrivethemes.com/en/articles/4425764-how-to-use-the-text-element',
			],
		];
	}
}
