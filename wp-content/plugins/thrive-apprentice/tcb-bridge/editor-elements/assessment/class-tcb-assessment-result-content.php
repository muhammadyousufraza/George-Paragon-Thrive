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

class TCB_Assessment_Result_Content_Element extends \TCB_Element_Abstract {
	protected $_tag = 'assessment_result_content';

	public function name() {
		return esc_html__( 'Result Content', 'thrive-apprentice' );
	}

	public function hide() {
		return true;
	}

	public function identifier() {
		return '.tva-assessment-result-state-content';
	}

	/**
	 * Element components
	 *
	 * @return array
	 */
	public function own_components() {
		return [
			'animation'        => [
				'hidden' => true,
			],
			'layout'           => [
				'hidden' => true,
			],
			'responsive'       => [
				'hidden' => true,
			],
			'styles-templates' => [
				'hidden' => true,
			],
		];
	}
}