<?php

class TCB_TQB_Label_Element extends TCB_Element_Abstract{

	public function name() {
		return __( 'Thrive Label', 'thrive-quiz-builder' );
	}

	public function identifier() {
		return '.tqb-label-container';
	}

	public function hide() {
		return true;
	}

	public function own_components() {
		return array(
			'typography' => array( 'hidden' => true ),
			'animation'  => array( 'hidden' => true ),
			'responsive' => array( 'hidden' => true ),
		);
	}
}
