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

class TCB_Assessment_Result_List_Element extends \TCB_Element_Abstract {
	protected $_tag = 'assessment_result_list';

	public function name() {
		return esc_html__( 'Result List', 'thrive-apprentice' );
	}

	public function hide() {
		return true;
	}

	public function identifier() {
		return Result::LIST_IDENTIFIER;
	}

	/**
	 * Element components
	 *
	 * @return array
	 */
	public function own_components() {
		return [
			'assessment_result_list' => [
				'config' => [],
			],
			'animation'              => [
				'hidden' => true,
			],
			'responsive'             => [
				'hidden' => true,
			],
			'styles-templates'       => [
				'hidden' => true,
			],
		];
	}
}