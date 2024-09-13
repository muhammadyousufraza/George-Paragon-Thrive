<?php

class TCB_TQB_Answer_Icon_Element extends TCB_Element_Abstract {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Answer Icon', 'thrive-cb' );
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return '.tqb-fancy-icon';
	}

	public function hide() {
		return true;
	}

	public function has_hover_state() {
		return true;
	}

	/**
	 * @return array
	 */
	public function own_components() {
		return array(
			'layout'     => array(
				'disabled_controls' => array( 'Width', 'Height', 'hr', 'Alignment', 'Display', '.tve-advanced-controls' ),
			),
			'typography'  => array( 'hidden' => true ),
			'shadow'      => array( 'hidden' => true ),
			'scroll'      => array( 'hidden' => true ),
			'animation'   => array( 'hidden' => true ),
			'responsive'  => array( 'hidden' => true ),
		);
	}
}
