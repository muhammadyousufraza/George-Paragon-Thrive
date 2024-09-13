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

class TCB_Assessment_Video_Preview_Element extends \TCB_Element_Abstract {
	protected $_tag = 'assessment_video_preview';

	public function name() {
		return esc_html__( 'Assessment video preview', 'thrive-apprentice' );
	}

	public function hide() {
		return true;
	}

	public function identifier() {
		return '.tva-assessment-video-preview';
	}
}