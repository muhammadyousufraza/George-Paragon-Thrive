<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( ! class_exists( '\TCB_Icon_Element', false ) ) {
	require_once TVE_TCB_ROOT_PATH . 'inc/classes/elements/class-tcb-icon-element.php';
}

/**
 * Class TCB_Menu_Icon_Element_Abstract
 *
 * inherited by TCB_Menu_Icon_Open_Element and TCB_Menu_Icon_Close_Element
 */
abstract class TCB_Menu_Icon_Element_Abstract extends TCB_Icon_Element {
	/**
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['icon']['disabled_controls']   = [ 'ToggleURL', 'link' ];
		$components['layout']['disabled_controls'] = [
			'Float',
			'Width',
			'Height',
			'Display',
			'Position',
			'Overflow',
			'Alignment',
			'ScrollStyle',
		];

		$components['animation']        = [ 'hidden' => true ];
		$components['scroll']           = [ 'hidden' => true ];
		$components['responsive']       = [ 'hidden' => true ];
		$components['styles-templates'] = [ 'hidden' => true ];

		$components['icon']['config']['Slider']['important']  = true;
		$components['icon']['config']['Slider']['css_prefix'] = tcb_selection_root() . ' ';

		$components['icon']['config']['ColorPicker']['important'] = true;

		return $components;
	}
}
