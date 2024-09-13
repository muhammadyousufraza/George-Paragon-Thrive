<?php

if ( ! class_exists( 'TCB_Menu_Element', false ) ) {
	require_once __DIR__ . '/class-tcb-menu-element.php';
}

/**
 * Class TCB_Hamburger_Menu_Element
 */
class TCB_Hamburger_Menu_Element extends TCB_Menu_Element {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Menu Element', 'thrive-cb' );
	}

	/**
	 * Section element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_wrapper:not(.tve-regular) .tve-ham-wrap > .tve_w_menu';
	}

	/**
	 * There is no need for HTML for this element since we need it only for control filter
	 *
	 * @return string
	 */
	protected function html() {
		return '';
	}

	public function hide() {
		return true;
	}

	public function own_components() {
		$components = parent::own_components();

		unset( $components['background'] );
		unset( $components['borders'] );
		unset( $components['shadow'] );

		$components['conditional-display'] = [ 'hidden' => true ];
		$components['responsive']          = [ 'hidden' => true ];
		$components['layout']              = [ 'hidden' => true ];

		unset( $components['menu']['config']['MenuWidth']['css_suffix'] );

		return $components;
	}
}
