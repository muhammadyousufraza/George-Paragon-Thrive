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

class TCB_Assessment_Result_Item_Element extends \TCB_Element_Abstract {
	protected $_tag = 'assessment_result_item';

	public function name() {
		return esc_html__( 'Result Item', 'thrive-apprentice' );
	}

	public function hide() {
		return true;
	}

	public function identifier() {
		return Result::ITEM_IDENTIFIER;
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
			'responsive'       => [
				'hidden' => true,
			],
			'styles-templates' => [
				'hidden' => true,
			],
		];
	}
}