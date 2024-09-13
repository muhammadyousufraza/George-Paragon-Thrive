<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Sidebar_Close_Icon_Element
 */
class Thrive_Sidebar_Close_Icon_Element extends Thrive_Theme_Element_Abstract {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Close Icon', 'thrive-theme' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve-sidebar-close-icon';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$components = [
			'icon'             => [ 'disabled_controls' => [ 'link' ] ],
			'typography'       => [ 'hidden' => true ],
			'animation'        => [ 'hidden' => true ],
			'shadow'           => [ 'hidden' => true ],
			'responsive'       => [ 'hidden' => true ],
			'styles-templates' => [ 'hidden' => true ],
		];

		$components['borders']['config']['Borders'] = [ 'important' => true ];

		$components['layout']['disabled_controls'] = [ 'Display', 'Alignment' ];

		return $components;
	}

	public function hide() {
		return true;
	}
}

return new Thrive_Sidebar_Close_Icon_Element( 'sidebar-close-icon' );
