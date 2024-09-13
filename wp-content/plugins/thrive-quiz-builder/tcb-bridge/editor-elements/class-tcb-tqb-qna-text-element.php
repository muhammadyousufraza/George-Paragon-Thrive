<?php

/**
 * Class TCB_TQB_QNA_Text
 *
 * Base representation of a text element for questions and answers
 */
class TCB_TQB_QNA_Text extends TCB_Element_Abstract {

	public function name() {
		return __( 'QNA Text', 'thrive-quiz-builder' );
	}

	public function identifier() {
		return '';
	}

	public function hide() {
		return true;
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'typography'       => array(
				'config' => array(
					'FontColor'     => array(
						'css_suffix' => ' span',
					),
					'LetterSpacing' => array(
						'css_suffix' => ' span',
					),
					'LineHeight'    => array(
						'css_suffix' => ' span',
					),
					'FontSize'      => array(
						'css_suffix' => ' span',
					),
					'TextStyle'     => array(
						'css_suffix' => ' span',
					),
					'TextAlign'     => array(
						'important' => true,
					),
					'TextTransform' => array(
						'css_suffix' => ' span',
					),
					'FontFace'      => array(
						'css_suffix' => ' span',
					),
				),
			),
			'layout'           => array(
				'hidden' => true,
			),
			'background'       => array(
				'hidden' => true,
			),
			'borders'          => array(
				'hidden' => true,
			),
			'shadow'           => array(
				'hidden' => true,
			),
			'animation'        => array(
				'hidden' => true,
			),
			'responsive'       => array(
				'hidden' => true,
			),
			'styles-templates' => array(
				'hidden' => true,
			),
		);
	}
}
