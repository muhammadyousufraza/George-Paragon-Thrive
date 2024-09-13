<?php

class TCB_User_Profile_Input_Element extends TCB_Element_Abstract {

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
		return '.tve-up-input';
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
				' select',
				' select::placeholder',
			],
		];

		$controls_default_config = [
			'css_suffix' => [
				' input',
				' textarea',
				' select',
			],
		];

		return array(
			'up_input'         => array(
				'config' => array(
					'Width' => array(
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
			'layout'           => array(
				'disabled_controls' => [
					'Width',
					'Height',
					'Alignment',
					'.tve-advanced-controls',
					'hr',
				],
				'config'            => array(
					'MarginAndPadding' =>
						array_merge(
							$controls_default_config,
							[ 'margin_suffix' => '' ] ),
				),
			),
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
