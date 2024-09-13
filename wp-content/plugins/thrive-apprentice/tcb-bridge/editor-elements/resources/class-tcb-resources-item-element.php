<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}


class TCB_Resources_Item_Element extends TCB_Element_Abstract {
	/**
	 * @var string
	 */
	protected $_tag = 'resources_item';

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Lesson resources item', 'thrive-apprentice' );
	}


	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tva-resource-item';
	}


	public function hide() {
		return true;
	}

	/**
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}

	public function own_components() {
		$prefix_config = [ 'css_prefix' => tcb_selection_root() . ' ' ];

		return [
			'borders'          => [
				'config' => [
					'Borders' => [
						'important' => true,
					],
					'Corners' => [
						'important' => true,
					],
				],
			],
			'layout'           => [
				'disabled_controls' => [
					'Display',
					'Float',
					'Position',
					'Alignment',
					'.tve-advanced-controls',
				],
				'config'            => [
					'Width'  => [
						'important' => true,
					],
					'Height' => [
						'important' => true,
					],
				],
			],
			'background'       => [
				'config' => [
					'ColorPicker' => $prefix_config,
					'PreviewList' => $prefix_config,
				],
			],
			'scroll'           => [
				'hidden' => true,
			],
			'animation'        => [
				'hidden' => true,
			],
			'typography'       => [
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

return new TCB_Resources_Item_Element();
