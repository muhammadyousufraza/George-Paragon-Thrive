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

class TCB_Assessment_Result_Header_Element extends \TCB_Element_Abstract {
	protected $_tag = 'assessment_result_header';

	public function name() {
		return esc_html__( 'Result header', 'thrive-apprentice' );
	}

	public function hide() {
		return true;
	}

	public function identifier() {
		return '.tva-assessment-result-state-header';
	}

	/**
	 * @return bool
	 */
	public function expanded_state_config() {
		return true;
	}

	/**
	 * @return bool
	 */
	public function expanded_state_apply_inline() {
		return true;
	}

	/**
	 * For TOC expanded is collapsed because we can
	 *
	 * @return string
	 */
	public function expanded_state_label() {
		return __( 'Collapsed', 'thrive-apprentice' );
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