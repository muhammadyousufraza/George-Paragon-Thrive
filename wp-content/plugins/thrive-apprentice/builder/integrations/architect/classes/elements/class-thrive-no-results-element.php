<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( ! class_exists( 'TCB_ContentBox_Element' ) ) {
	require_once TVE_TCB_ROOT_PATH . 'inc/classes/elements/class-tcb-contentbox-element.php';
}

class Thrive_No_Results_Element extends TCB_ContentBox_Element {
	/**
	 * Name of the element.
	 *
	 * @return string
	 */
	public function name() {
		return __( 'No Results', 'thrive-theme' );
	}

	/**
	 * WordPress element identifier.
	 *
	 * @return string
	 */
	public function identifier() {
		return '.main-no-results.thrv_contentbox_shortcode';
	}

	/**
	 * Hide this in the sidebar.
	 */
	public function hide() {
		return true;
	}

	/**
	 * Component and control config.
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		/* re-use the content box component */
		$components                                    = array_merge( [ 'no_results' => $components['contentbox'] ], $components );
		$components['no_results']['disabled_controls'] = [ 'ToggleURL', '.cb-link' ];

		unset( $components['contentbox'], $components['shared-styles'] );

		return $components;
	}
}

return new Thrive_No_Results_Element( 'no_results' );
