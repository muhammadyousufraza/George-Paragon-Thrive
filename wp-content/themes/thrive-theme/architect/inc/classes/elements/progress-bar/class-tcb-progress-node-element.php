<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Progress_Node_Element
 *
 */
class TCB_Progress_Node_Element extends TCB_Element_Abstract {

	public function name() {
		return __( 'Node', 'thrive-cb' );
	}

	public function identifier() {
		return '.tve-progress-node';
	}

	public function hide() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function expanded_state_config() {
		return true;
	}

	/**
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function expanded_state_apply_inline() {
		return true;
	}

	/**
	 *
	 * @inheritDoc
	 */
	public function expanded_state_label() {
		return __( 'Completed', 'thrive-cb' );
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return [
			'typography' => [ 'hidden' => true ],
			'responsive' => [ 'hidden' => true ],
			'animation'  => [ 'hidden' => true ],
			'layout'     => [
				'disabled_controls' => [ 'Alignment', 'Display', '.tve-advanced-controls', 'Width', 'Height' ],
			],
			'borders'    => [
				'disabled_controls' => [],
				'config'            => [
					'Corners' => [
						'overflow' => false,
					],
				],
			],
		];
	}
}
