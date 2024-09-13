<?php

class TCB_TQB_Question_Text extends TCB_TQB_QNA_Text {

	public function name() {
		return __( 'Quiz Question', 'thrive-quiz-builder' );
	}

	public function identifier() {
		return '.tqb-question-text';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {

		$components = parent::own_components();

		if ( ! empty( $components['qna_text']['config']['FontColor'] ) ) {
			$components['qna_text']['config']['FontColor']['css_suffix'] = '.tqb-question-text';
		}

		return $components;
	}
}
