<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( ! class_exists( 'TCB_Icon_Element' ) ) {
	require_once TVE_Ult_Const::plugin_path( 'tcb/inc/classes/elements/class-tcb-icon-element.php' );
}

class TCB_Ultimatum_Close_Bar_Element extends TCB_Icon_Element {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Close Ultimatum', 'thrive-ult');
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return '';
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tve-ult-bar-close-editable';
	}

	public function hide() {
		return true;
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['icon']['disabled_controls'] = array( 'ToggleURL', 'link' );
		$components['animation']                 = array( 'hidden' => true );
		$components['responsive']                = array( 'hidden' => true );
		$components['scroll']                    = array( 'hidden' => true );

		return $components;
	}
}
