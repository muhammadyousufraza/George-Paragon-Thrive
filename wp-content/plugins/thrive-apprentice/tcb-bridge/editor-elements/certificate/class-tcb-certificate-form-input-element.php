<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package ${NAMESPACE}
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TCB_Certificate_Form_Input_Element extends TCB_Element_Abstract {

	protected $_tag = 'tva_certificate_form_input';

	public function name() {
		return __( 'Certificate Form Input', 'thrive-apprentice' );
	}

	public function identifier() {
		return '.tva-certificate-form-input';
	}

	public function hide() {
		return true;
	}

	public function has_hover_state() {
		return true;
	}

	public function own_components() {
		$controls_default_config_text = array(
			'css_suffix' => array(
				' input',
				' input::placeholder',
				' textarea',
				' textarea::placeholder',
			),
		);

		$controls_default_config = array(
			'css_suffix' => array(
				' input',
				' textarea',
			),
		);

		return array(
			'certificate_form_input' => array(
				'config' => array(
					'placeholder' => array(
						'config' => array(
							'label' => __( 'Placeholder', 'thrive-apprentice' ),
						),
					),
				),
			),
			'typography'             => array(
				'config' => array(
					'FontSize'      => $controls_default_config_text,
					'FontColor'     => $controls_default_config_text,
					'TextAlign'     => $controls_default_config_text,
					'TextStyle'     => $controls_default_config_text,
					'TextTransform' => $controls_default_config_text,
					'FontFace'      => $controls_default_config_text,
					'LineHeight'    => $controls_default_config_text,
					'LetterSpacing' => $controls_default_config_text,
				),
			),
			'layout'                 => array(
				'disabled_controls' => array(
					'Width',
					'Height',
					'Alignment',
					'.tve-advanced-controls',
					'hr',
				),
				'config'            => array(
					'MarginAndPadding' => $controls_default_config,
				),
			),
			'borders'                => array(
				'config' => array(
					'Borders' => $controls_default_config,
					'Corners' => $controls_default_config,
				),
			),
			'animation'              => array(
				'hidden' => true,
			),
			'background'             => array(
				'config' => array(
					'ColorPicker' => $controls_default_config,
					'PreviewList' => $controls_default_config,
				),
			),
			'shadow'                 => array(
				'config' => $controls_default_config,
			),
			'responsive'             => array(
				'hidden' => true,
			),
			'styles-templates'       => array(
				'hidden' => true,
			),
		);
	}
}
