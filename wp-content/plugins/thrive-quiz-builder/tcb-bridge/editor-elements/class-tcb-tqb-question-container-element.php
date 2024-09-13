<?php

class TCB_TQB_Question_Container_Element extends TCB_Element_Abstract {
	public function name() {
		return __( 'Question Container', 'thrive-quiz-builder' );
	}

	public function identifier() {
		return '.tqb-question-container';
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
			'typography'       => array( 'hidden' => true ),
			'animation'        => array( 'hidden' => true ),
			'responsive'       => array( 'hidden' => true ),
			'styles-templates' => array( 'hidden' => true ),
		);
	}
}
