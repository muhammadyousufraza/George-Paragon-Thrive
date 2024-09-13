<?php

require_once plugin_dir_path( __FILE__ ) . 'class-tcb-label-element.php';

/**
 * Class TCB_Label_Disabled_Element
 *
 * Non edited label element. For inline text we use typography control
 */
class TCB_Link_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Link Text', 'thrive-cb' );
	}

	public function hide() {
		return true;
	}

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_text_element a, .tcb-styled-list a, .tcb-numbered-list a, .tve-input-option-text a, .thrv-typography-link';
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
	 * Links have hover states
	 *
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}

	/**
	 * @return array|void
	 */
	public function general_components() {
		return array(
			'link'   => array(
				'config' => array(
					'ToggleColor'         => array(
						'config'  => array(
							'name'    => __( 'Color', 'thrive-cb' ),
							'buttons' => array(
								array( 'value' => 'inherit', 'text' => __( 'Inherit', 'thrive-cb' ), 'default' => true ),
								array( 'value' => 'specific', 'text' => __( 'Specific', 'thrive-cb' ) ),
							),
						),
						'extends' => 'Tabs',
					),
					'FontColor'           => [
						'config'  => [
							'default' => '000',
							'label'   => ' ',
							'options' => [
								'output' => 'object',
							],
						],
						'extends' => 'ColorPicker',
					],
					'ToggleColorControls' => array(
						'config'  => array(
							'name'    => __( 'Color type', 'thrive-cb' ),
							'buttons' => array(
								array( 'value' => 'tcb-text-solid-color', 'text' => __( 'Solid', 'thrive-cb' ) ),
								array( 'value' => 'tcb-text-gradient-color', 'text' => __( 'Gradient', 'thrive-cb' ) ),
							),
						),
						'extends' => 'ButtonGroup',
					),
					'FontGradient'        => [
						'config'  => [
							'default' => '000',
							'label'   => __( 'Gradient', 'thrive-cb' ),
							'options' => [
								'output' => 'object',
								'hasInput' => true,
							],
						],
						'extends' => 'GradientPicker',
					],
					'FontBaseColor'       => [
						'css_prefix' => '.thrv_text_element ',
						'config'     => [
							'default' => '000',
							'label'   => __( 'Base color', 'thrive-cb' ),
							'info'    => true,
							'options' => [
								'output' => 'object',
							],
						],
						'extends'    => 'ColorPicker',
					],

					'BgColor'      => array(
						'config'  => array(
							'default' => '000',
							'label'   => __( 'Highlight', 'thrive-cb' ),
							'options' => [
								'output' => 'object',
							],
						),
						'extends' => 'ColorPicker',
					),
					'ToggleFont'   => array(
						'config'  => array(
							'name'    => __( 'Font', 'thrive-cb' ),
							'buttons' => array(
								array( 'value' => 'inherit', 'text' => __( 'Inherit', 'thrive-cb' ), 'default' => true ),
								array( 'value' => 'specific', 'text' => __( 'Specific', 'thrive-cb' ) ),
							),
						),
						'extends' => 'Tabs',
					),
					'FontFace'     => [
						'config' => [
							'label'    => ' ',
							'template' => 'controls/font-manager',
							'inline'   => true,
						],
					],
					'ToggleSize'   => array(
						'config'  => array(
							'name'    => __( 'Size', 'thrive-cb' ),
							'buttons' => array(
								array( 'value' => 'inherit', 'text' => __( 'Inherit', 'thrive-cb' ), 'default' => true ),
								array( 'value' => 'specific', 'text' => __( 'Specific', 'thrive-cb' ) ),
							),
						),
						'extends' => 'Tabs',
					),
					'FontSize'     => [
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
					'TextStyle'    => [
						'config' => [
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
					'Effect'       => array(
						'config'  => array(
							'label' => __( 'Effect', 'thrive-cb' ),
						),
						'extends' => 'StyleChange',
					),
					'EffectPicker' => array(
						'config' => array(
							'label'   => __( 'Choose link effect', 'thrive-cb' ),
							'default' => 'none',
						),
					),
					'EffectColor'  => array(
						'config'  => array(
							'label'   => __( 'Effect Color', 'thrive-cb' ),
							'options' => [
								'output'      => 'object',
								'showGlobals' => false,
							],
						),
						'extends' => 'ColorPicker',
					),
					'EffectSpeed'  => array(
						'label'   => __( 'Effect Speed', 'thrive-cb' ),
						'config'  => array(
							'default' => '0.2',
							'min'     => '0.05',
							'step'    => '0.05',
							'max'     => '1',
							'label'   => __( 'Speed', 'thrive-cb' ),
							'um'      => [ 's' ],
						),
						'extends' => 'Slider',
					),
				),
			),
			'shadow' => [
				'order'  => 140,
				'config' => [
					'disabled_controls' => [ 'drop', 'inner' ],
					'with_froala'       => true,
				],
			],
		);
	}
}
