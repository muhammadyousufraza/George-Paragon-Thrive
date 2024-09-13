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

/**
 * class TCB_Assessment_Quiz_Wrapper_Element
 */
class TCB_Assessment_Quiz_Wrapper_Element extends \TCB_Element_Abstract {
	protected $_tag = 'assessment_quiz_wrapper';

	public function name() {
		return esc_html__( 'Assessment Quiz', 'thrive-apprentice' );
	}

	public function hide() {
		return true;
	}

	public function identifier() {
		return '.tva-tqb-assessment-quiz';
	}
}
