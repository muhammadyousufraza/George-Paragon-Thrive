<?php

class TCB_TQB_Answer_Text extends TCB_TQB_QNA_Text {

	public function name() {
		return __( 'Answer Text', 'thrive-quiz-builder' );
	}

	public function identifier() {
		return '.tqb-answer-text';
	}

	public function has_hover_state() {
		return true;
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {

		$components = parent::own_components();

		if ( ! empty( $components['qna_text']['config']['FontColor'] ) ) {
			$components['qna_text']['config']['FontColor']['css_suffix'] = '.tqb-answer-text';
		}

		return $components;
	}
}
