<?php

class TCB_TQB_Answer_Wrong_Item extends TCB_TQB_Answer_Item {
	public function name() {
		return __( 'Wrong Answer Item', 'thrive-quiz-builder' );
	}

	public function identifier() {
		return '.tqb-wrong';
	}
}
