<?php

class TCB_TQB_Answers_Container_Element extends TCB_Element_Abstract {
	public function name() {
		return __( 'Quiz Answers', 'thrive-quiz-builder' );
	}

	public function identifier() {
		return '.tqb-answers-container';
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
			'layout'           => array(
				'disabled_controls' => array( 'Width', 'Height', 'hr', 'Alignment', 'Display', '.tve-advanced-controls' ),
			),
			'borders'          => array(
				'config' => array(
					'Corners' => array(
						'overflow' => false,
					),
				),
			),
			'typography'       => array(
				'config' => array(
					'css_suffix'    => ' .tqb-span-text',
					'FontColor'     => array(
						'css_suffix' => ' .tqb-span-text',
					),
					'TextAlign'     => array(
						'hidden' => true,
					),
					'FontSize'      => array(
						'css_suffix' => ' .tqb-span-text',
						'important'  => true,
					),
					'TextStyle'     => array(
						'css_suffix' => ' .tqb-span-text',
					),
					'LineHeight'    => array(
						'css_suffix' => ' .tqb-span-text',
					),
					'FontFace'      => array(
						'css_suffix' => ' .tqb-span-text',
					),
					'TextTransform' => array(
						'css_suffix' => ' .tqb-span-text',
					),
					'LetterSpacing' => array(
						'css_suffix' => ' .tqb-span-text',
					),
				),
			),
			'animation'        => array( 'hidden' => true ),
			'responsive'       => array( 'hidden' => true ),
			'styles-templates' => array( 'hidden' => true ),
		);
	}
}
