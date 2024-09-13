<?php

class TCB_TQB_Progress_Bar extends TCB_Element_Abstract {
	public function name() {
		return __( 'Quiz Progress bar', 'thrive-quiz-builder' );
	}

	public function identifier() {
		return '.tqb-progress-container';
	}

	public function hide() {
		return true;
	}

	public function own_components() {
		return array(
			'tqb_progress_bar' => array(
				'config' => array(
					'Palettes'            => array(
						'config'    => array(),
						'important' => true,
						'to'        => '.tqb-progress',
					),
					'ProgressBarPosition' => array(
						'config'     => array(
							'name'    => '',
							'label'   => __( 'Progress Bar Position', 'thrive-quiz-builder' ),
							'default' => true,
							'options' => array(
								array(
									'name'  => 'Above question',
									'value' => __( 'position_top', 'thrive-quiz-builder' ),
								),
								array(
									'name'  => 'Below question',
									'value' => __( 'position_bottom', 'thrive-quiz-builder' ),
								),
							),
						),
						'css_suffix' => '',
						'css_prefix' => '',
						'extends'    => 'Select',
					),
					'ProgressBarType'     => array(
						'config'     => array(
							'name'    => '',
							'label'   => __( 'Progress Type', 'thrive-quiz-builder' ),
							'default' => true,
							'options' => array(
								array(
									'name'  => 'Percent completed',
									'value' => __( 'percentage_completed', 'thrive-quiz-builder' ),
								),
								array(
									'name'  => 'Percentage remaining',
									'value' => __( 'percentage_remaining', 'thrive-quiz-builder' ),
								),
							),
						),
						'css_suffix' => '',
						'css_prefix' => '',
						'extends'    => 'Select',
					),
					'ProgressBarLabel'    => array(
						'config'     => array(
							'name'    => '',
							'label'   => __( 'Progress bar Label', 'thrive-quiz-builder' ),
							'default' => true,
						),
						'css_suffix' => '',
						'css_prefix' => '',
					),
				),
			),
			'typography'       => array( 'hidden' => true ),
			'animation'        => array( 'hidden' => true ),
			'responsive'       => array( 'hidden' => true ),
			'styles-templates' => array( 'hidden' => true ),
			'layout'           => array(
				'disabled_controls' => array( 'Width', 'Height', 'hr', 'Alignment', 'Display', '.tve-advanced-controls' ),
			),
		);
	}
}
