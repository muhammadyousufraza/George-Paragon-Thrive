<?php

class TCB_Login_Form_Input_Element extends TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Form Input', 'thrive-cb' );
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve-login-form-input';
	}

	/**
	 * Hide the element in the sidebar menu
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	public function own_components() {
		$controls_default_config_text = [
			'css_suffix' => [
				' input',
				' input::placeholder',
				' textarea',
				' textarea::placeholder',
			],
		];

		$controls_default_config = [
			'css_suffix' => [
				' input',
				' textarea',
			],
		];

		return array(
			'login_form_input' => array(
				'config' => array(
					'ShowLabel'   => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Show label', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Switch',
					),
					'icon_side'   => array(
						'rem_ic_css_suf' => ' input', //Remove Icon Css Suffix
						'css_suffix'     => ' .thrv_icon',
						'config'         => array(
							'name'    => __( 'Icon Side', 'thrive-cb' ),
							'buttons' => array(
								array(
									'value' => 'left',
									'text'  => __( 'Left', 'thrive-cb' ),
								),
								array(
									'value' => 'right',
									'text'  => __( 'Right', 'thrive-cb' ),
								),
							),
						),
					),
					'placeholder' => array(
						'config' => array(
							'label' => __( 'Placeholder', 'thrive-cb' ),
						),
					),
				),
			),
			'typography'       => [
				'config' => [
					'FontSize'      => $controls_default_config_text,
					'FontColor'     => $controls_default_config_text,
					'TextAlign'     => $controls_default_config_text,
					'TextStyle'     => $controls_default_config_text,
					'TextTransform' => $controls_default_config_text,
					'FontFace'      => $controls_default_config_text,
					'LineHeight'    => $controls_default_config_text,
					'LetterSpacing' => $controls_default_config_text,
				],
			],
			'layout'           => [
				'disabled_controls' => [
					'Width',
					'Height',
					'Alignment',
					'.tve-advanced-controls',
					'hr',
				],
				'config'            => [
					'MarginAndPadding' => $controls_default_config,
				],
			],
			'borders'          => [
				'config' => [
					'Borders' => $controls_default_config,
					'Corners' => $controls_default_config,
				],
			],
			'animation'        => [
				'hidden' => true,
			],
			'background'       => [
				'config' => [
					'ColorPicker' => $controls_default_config,
					'PreviewList' => $controls_default_config,
				],
			],
			'shadow'           => [
				'config' => $controls_default_config,
			],
			'responsive'       => [
				'hidden' => true,
			],
			'styles-templates' => [
				'hidden' => true,
			],
		);
	}

	public function has_hover_state() {
		return true;
	}
}
