<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * TCB_Filter_Dynamic_List_Item_Element
 *
 * This is a default element used for displaying only default menus for a component
 * It is not displayed in the sidebar elements
 */
class TCB_Filter_Dynamic_List_Item_Element extends TCB_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Filter List Item', 'thrive-cb' );
	}

	/**
	 * Default element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tcb-filter-list';
	}

	/**
	 * Either to display or not the element in the sidebar menu
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$default_config = [
			'css_suffix' => '',
			'css_prefix' => '',
		];

		return [
			'typography'       => [
				'config' => [
					'TextStyle'     => $default_config,
					'FontColor'     => $default_config,
					'FontSize'      => $default_config,
					'TextTransform' => $default_config,
					'FontFace'      => $default_config,
					'LineHeight'    => $default_config,
					'LetterSpacing' => $default_config,
				],
			],
			'animation'        => [ 'hidden' => true ],
			'responsive'       => [ 'hidden' => true ],
			'styles-templates' => [ 'hidden' => true ],
			'layout'           => [
				'disabled_controls' => [
					'Alignment',
					'Display',
					'.tve-advanced-controls',
				],
			],
			'shadow'           => [
				'config' => [],
			],
		];
	}

	/**
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}

	public function active_state_config() {
		return true;
	}
}
