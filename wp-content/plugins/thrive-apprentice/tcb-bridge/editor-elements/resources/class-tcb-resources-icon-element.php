<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( ! class_exists( 'TCB_Icon_Element' ) ) {
	require_once TVE_TCB_ROOT_PATH . 'inc/classes/elements/class-tcb-icon-element.php';
}

class TCB_Resources_Icon_Element extends TCB_Icon_Element {
	/**
	 * @var string
	 */
	protected $_tag = 'resources_icon';

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tva-resource-icon';
	}

	/**
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}


	public function own_components() {
		$components = parent::own_components();

		$components['icon']['disabled_controls'] = [ 'ToggleURL', 'link', 'IconPicker' ];
		$components['scroll']                    = [
			'hidden' => true,
		];
		$components['animation']                 = [
			'hidden' => true,
		];
		$components['responsive']                = [
			'hidden' => true,
		];
		$components['styles-templates']          = [
			'hidden' => true,
		];

		return $components;
	}


	public function hide() {
		return true;
	}
}

return new TCB_Resources_Icon_Element();
