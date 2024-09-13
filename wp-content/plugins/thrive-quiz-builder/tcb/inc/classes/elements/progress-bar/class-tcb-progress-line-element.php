<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Progress_Line_Element
 *
 */
class TCB_Progress_Line_Element extends TCB_Element_Abstract {

	public function name() {
		return __( 'Progress line', 'thrive-cb' );
	}

	public function identifier() {
		return '.tve-progress-line';
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
			'progress_line' => array(
				'config' => array(
					'ColorPicker' => array(
						'config' => array(
							'label'   => __( 'Color', 'thrive-cb' ),
							'options' => [ 'noBeforeInit' => false ],
						),
					),
					'Candy'       => array(
						'config'  => array(
							'name'    => '',
							'label'   => __( 'Candy stripe animation', 'thrive-cb' ),
							'default' => false,
						),
						'extends' => 'Switch',
					),
				),
			),
			'background'    => [ 'hidden' => true ],
			'typography'    => [ 'hidden' => true ],
			'layout'        => [ 'hidden' => true ],
			'responsive'    => [ 'hidden' => true ],
			'borders'       => [
				'disabled_controls' => [],
				'config'            => [
					'Corners' => [
						'overflow' => false,
					],
				],
			],
			'animation'     => [ 'hidden' => true ],
			'shadow'        => [
				'config' => [
					'disabled_controls' => [ 'text' ],
				],
			],
		);
	}
}
