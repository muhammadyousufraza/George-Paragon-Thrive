<?php

class TCB_Form_State_Element extends TCB_Element_Abstract {

	public function name() {
		return __( 'Form State', 'thrive-cb' );
	}

	public function identifier() {
		return '.tve-form-state';
	}

	/**
	 * Hide Element From Sidebar Menu
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	public function own_components() {

		return [
			'typography' => [ 'hidden' => true, ],
			'animation'  => [ 'hidden' => true, ],
			'responsive' => [ 'hidden' => true, ],
			'background' => [
				'config' => [],
			],
			'shadow'     => [
				'config' => [],
			],
			'layout'     => [
				'disabled_controls' => [ 'Width', 'Height', 'Display', 'Alignment', 'Float', 'Position', 'PositionFrom' ],
			],
			'borders'    => [
				'config' => [
					'Borders' => [],
					'Corners' => [],
				],
			],
		];
	}
}
