<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * TCB_Filter_Dynamic_List_Text_Element
 *
 * This is a default element used for displaying only default menus for a component
 * It is not displayed in the sidebar elements
 */
class TCB_Filter_Dynamic_List_Text_Element extends TCB_Text_Element {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Filter Inline Text', 'thrive-cb' );
	}

	/**
	 * Default element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tcb-filter-list-text';
	}

	/**
	 * Either to display or not the element in the sidebar menu
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	/**
	 * Whether or not the this element can be edited while under :hover state
	 *
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return [
			'text'             => [
				'config' => [
					'ToggleColor'  => [
						'config'  => [
							'css_suffix' => ' a',
							'name'       => __( 'Color', 'thrive-cb' ),
							'buttons'    => [
								[ 'value' => 'inherit', 'text' => __( 'Inherit', 'thrive-cb' ), 'default' => true ],
								[ 'value' => 'specific', 'text' => __( 'Specific', 'thrive-cb' ) ],
							],
						],
						'extends' => 'Tabs',
					],
					'FontColor'    => [
						'css_prefix' => tcb_selection_root() . ' ',
						'read_from'  => 'head',
						'config'     => [
							'default' => '000',
							'label'   => ' ',
							'options' => [
								'output' => 'object',
							],
						],
						'extends'    => 'ColorPicker',
					],
					'BgColor'      => [
						'css_prefix' => tcb_selection_root() . ' ',
						'read_from'  => 'head',
						'config'     => [
							'default' => '000',
							'label'   => __( 'Highlight', 'thrive-cb' ),
							'options' => [
								'output' => 'object',
							],
						],
						'extends'    => 'ColorPicker',
					],
					'ToggleFont'   => [
						'config'  => [
							'name'    => __( 'Font', 'thrive-cb' ),
							'buttons' => [
								[ 'value' => 'inherit', 'text' => __( 'Inherit', 'thrive-cb' ), 'default' => true ],
								[ 'value' => 'specific', 'text' => __( 'Specific', 'thrive-cb' ) ],
							],
						],
						'extends' => 'Tabs',
					],
					'FontFace'     => [
						'css_prefix' => tcb_selection_root() . ' ',
						'read_from'  => 'head',
						'config'     => [
							'label'     => ' ',
							'template'  => 'controls/font-manager',
							'inline'    => true,
							'important' => true,
						],
					],
					'ToggleSize'   => [
						'config'  => [
							'name'    => __( 'Size', 'thrive-cb' ),
							'buttons' => [
								[ 'value' => 'inherit', 'text' => __( 'Inherit', 'thrive-cb' ), 'default' => true ],
								[ 'value' => 'specific', 'text' => __( 'Specific', 'thrive-cb' ) ],
							],
						],
						'extends' => 'Tabs',
					],
					'FontSize'     => [
						'css_prefix' => tcb_selection_root() . ' ',
						'read_from'  => 'head',
						'config'     => [
							'default' => '16',
							'min'     => '1',
							'max'     => '100',
							'label'   => '',
							'um'      => [ 'px', 'em' ],
							'css'     => 'fontSize',
						],
						'extends'    => 'FontSize',
					],
					'TextStyle'    => [
						'css_prefix' => tcb_selection_root() . ' ',
						'read_from'  => 'head',
						'config'     => [
							'important' => true,
							'buttons'   => [
								'underline'    => [
									'data' => [ 'style' => 'text-decoration-line' ],
								],
								'line-through' => [
									'data' => [ 'style' => 'text-decoration-line' ],
								],
							],
						],
					],
					'Effect'       => [
						'css_prefix' => tcb_selection_root() . ' ',
						'read_from'  => 'head',
						'config'     => [
							'label' => __( 'Effect', 'thrive-cb' ),
						],
						'extends'    => 'StyleChange',
					],
					'EffectPicker' => [
						'css_prefix' => tcb_selection_root() . ' ',
						'read_from'  => 'head',
						'config'     => [
							'label'   => __( 'Choose link effect', 'thrive-cb' ),
							'default' => 'none',
						],
					],
					'EffectColor'  => [
						'css_prefix' => tcb_selection_root() . ' ',
						'read_from'  => 'head',
						'config'     => [
							'label'   => __( 'Effect Color', 'thrive-cb' ),
							'options' => [
								'output'      => 'object',
								'showGlobals' => false,
							],
						],
						'extends'    => 'ColorPicker',
					],
					'EffectSpeed'  => [
						'css_prefix' => tcb_selection_root() . ' ',
						'read_from'  => 'head',
						'label'      => __( 'Effect Speed', 'thrive-cb' ),
						'config'     => [
							'default' => '0.2',
							'min'     => '0.05',
							'step'    => '0.05',
							'max'     => '1',
							'label'   => __( 'Speed', 'thrive-cb' ),
							'um'      => [ 's' ],
						],
						'extends'    => 'Slider',
					],
				],
			],
			'layout'           => [ 'hidden' => true ],
			'typography'       => [ 'hidden' => true ],
			'animation'        => [ 'hidden' => true ],
			'responsive'       => [ 'hidden' => true ],
			'styles-templates' => [ 'hidden' => true ],
			'shadow'           => [ 'hidden' => true ],
		];
	}
}

