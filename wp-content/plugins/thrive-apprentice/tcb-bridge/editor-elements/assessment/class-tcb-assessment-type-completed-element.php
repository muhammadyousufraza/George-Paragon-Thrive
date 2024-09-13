<?php

namespace TVA\Architect\Assessment;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TCB_Assessment_Type_Completed_Element extends TCB_Assessment_Type_Element {
	protected $_tag = 'assessment_type_completed';

	public function name() {
		return esc_html__( 'Assessment type', 'thrive-apprentice' );
	}

	public function hide() {
		return true;
	}

	public function identifier() {
		return Main::TYPE_IDENTIFIER . '[data-type="' . Main::TYPE_RESULTS . '"]';
	}

	public function own_components() {
		return array_merge( [
			'assessment_type_completed' => [
				'config' => [
					'DisplayHistory' => [
						'config'  => [
							'name'  => '',
							'label' => __( 'Display assessments history', 'thrive-apprentice' ),
						],
						'extends' => 'Switch',
					],
					'HideLatest'     => array(
						'config'  => array(
							'name'  => '',
							'label' => __( 'Hide latest assessments from history', 'thrive-apprentice' ),
						),
						'extends' => 'Switch',
					),
					'NumberOfItems'  => array(
						'config'  => array(
							'name'      => __( 'Number of assessments', 'thrive-apprentice' ),
							'default'   => 5,
							'maxlength' => 20,
							'min'       => 1,
						),
						'extends' => 'Input',
					),
				],
			],
		], parent::own_components() );
	}
}
