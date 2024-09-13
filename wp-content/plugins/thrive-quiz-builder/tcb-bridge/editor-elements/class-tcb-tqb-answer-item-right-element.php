<?php

class TCB_TQB_Answer_Right_Item extends TCB_TQB_Answer_Item {
	public function name() {
		return __( 'Right Answer Item', 'thrive-quiz-builder' );
	}

	public function identifier() {
		return '.tqb-right';
	}
}
