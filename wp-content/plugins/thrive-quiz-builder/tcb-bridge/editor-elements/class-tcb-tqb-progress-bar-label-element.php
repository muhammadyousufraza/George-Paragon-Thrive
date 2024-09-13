<?php

class TCB_TQB_Progress_Bar_Text extends TCB_TQB_QNA_Text {

	public function name() {
		return __( 'Progress bar label', 'thrive-quiz-builder' );
	}

	public function identifier() {
		return '.tqb-progress-label';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {

		$components                                  = parent::own_components();
		$components['layout']['disabled_controls']   = array( 'Display', '.tve-advanced-controls' );
		$components['qna_text']['disabled_controls'] = array( 'TextTransform', 'TextAlign' );

		if ( ! empty( $components['qna_text']['config']['FontColor'] ) ) {
			$components['qna_text']['config']['FontColor']['css_suffix'] = '.tqb-progress-label span';
		}

		return $components;
	}
}
