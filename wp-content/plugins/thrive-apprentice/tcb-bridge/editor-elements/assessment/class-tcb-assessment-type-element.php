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

class TCB_Assessment_Type_Element extends \TCB_Element_Abstract {
	protected $_tag = 'assessment_type';

	public function name() {
		return esc_html__( 'Assessment type', 'thrive-apprentice' );
	}

	public function hide() {
		return true;
	}

	public function identifier() {
		return Main::TYPE_IDENTIFIER . ':not([data-type="' . Main::TYPE_RESULTS . '"])';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return [
			'borders'             => [
				'config' => [
					'Borders' => [
						'to'        => '>.tve-content-box-background',
						'important' => true,
					],
					'Corners' => [
						'to' => '>.tve-content-box-background',
					],
				],
			],
			'layout'              => [
				'config'            => [
					'Position' => [
						'important'          => true,
						'disabled_positions' => [ 'auto' ],
					],
					'Height'   => [
						'to'        => '> .tve-cb',
						'important' => true,
					],
				],
				'disabled_controls' => [],
			],
			'background'          => [
				'config' => [
					'to' => '>.tve-content-box-background',
				],
			],
			'shadow'              => [
				'config' => [
					'to' => '>.tve-content-box-background',
				],
			],
			'decoration'          => [
				'config' => [
					'to' => '>.tve-content-box-background',
				],
			],
			'typography'          => [
				'disabled_controls' => [],
				'config'            => [
					'to'             => '> .tve-cb',
					'ParagraphStyle' => [ 'hidden' => false ],
				],
			],
			'scroll'              => [
				'hidden' => true,
			],
			'conditional-display' => [
				'hidden' => true,
			],
			'animation'           => [
				'hidden' => true,
			],
			'responsive'          => [
				'hidden' => true,
			],
			'styles-templates'    => [
				'hidden' => true,
			],
		];
	}
}
