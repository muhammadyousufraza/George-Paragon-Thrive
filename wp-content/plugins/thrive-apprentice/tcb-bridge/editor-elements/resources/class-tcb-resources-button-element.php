<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( ! class_exists( 'TCB_Button_Element' ) ) {
	require_once TVE_TCB_ROOT_PATH . 'inc/classes/elements/class-tcb-button-element.php';
}

class TCB_Resources_Button_Element extends TCB_Button_Element {
	/**
	 * @var string
	 */
	protected $_tag = 'resources_button';

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tva-resource-button';
	}

	/**
	 * @return bool
	 */
	public function has_hover_state() {
		return true;
	}


	public function own_components() {
		$components = parent::own_components();

		$components['button']['disabled_controls']               = [ '.tcb-button-link-container', 'Align', 'ButtonWidth' ];
		$components['borders']['config']['Borders']['important'] = true;
		$components['borders']['config']['Corners']['important'] = true;

		$components['scroll']           = [
			'hidden' => true,
		];
		$components['animation']        = [
			'hidden' => true,
		];
		$components['responsive']       = [
			'hidden' => true,
		];
		$components['styles-templates'] = [
			'hidden' => true,
		];

		return $components;
	}

	public function hide() {
		return true;
	}
}

return new TCB_Resources_Button_Element();
