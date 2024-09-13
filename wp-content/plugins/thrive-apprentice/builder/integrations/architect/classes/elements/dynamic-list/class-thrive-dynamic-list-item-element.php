<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Thrive_Dynamic_List_Item_Element
 *
 * This is a default element used for displaying only default menus for a component
 * It is not displayed in the sidebar elements
 */
class Thrive_Dynamic_List_Item_Element extends Thrive_Theme_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'List Item', 'thrive-theme' );
	}

	/**
	 * Default element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrive-dynamic-styled-list-item';
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
		return [
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

	/**
	 * This element has no icons
	 *
	 * @return bool
	 */
	public function has_icons() {
		return false;
	}
}

return new Thrive_Dynamic_List_Item_Element( 'dynamic-list-item' );
