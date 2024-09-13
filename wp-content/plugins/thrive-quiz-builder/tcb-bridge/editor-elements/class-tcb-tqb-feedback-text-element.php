<?php

class TCB_TQB_Answer_Feedback_Text extends TCB_TQB_QNA_Text {

	public function name() {
		return __( 'Answer Text', 'thrive-quiz-builder' );
	}

	public function identifier() {
		return '.tqb-feedback-text';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {

		$components = parent::own_components();

		$components['answer_feedback_text'] = array();

		if ( ! empty( $components['qna_text']['config']['FontColor'] ) ) {
			$components['qna_text']['config']['FontColor']['css_suffix'] = '.tqb-feedback-text';
		}

		return $components;
	}
}
