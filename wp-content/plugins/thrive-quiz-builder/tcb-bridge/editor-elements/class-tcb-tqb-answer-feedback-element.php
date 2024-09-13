<?php

class TCB_TQB_Answer_Feedback extends TCB_Element_Abstract {
	public function name() {
		return __( 'Answer Feedback Container', 'thrive-quiz-builder' );
	}

	public function identifier() {
		return '.tqb-answer-feedback';
	}

	public function hide() {
		return true;
	}

	public function active_state_config() {
		return '.tqb-answer-inner-wrapper';
	}

	public function own_components() {
		return array(
			'typography' => array( 'hidden' => true ),
			'animation'  => array( 'hidden' => true ),
			'responsive' => array( 'hidden' => true ),
		);
	}
}
