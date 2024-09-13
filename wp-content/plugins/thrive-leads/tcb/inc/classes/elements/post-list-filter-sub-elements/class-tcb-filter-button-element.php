<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Filter_Button_Element
 */
class TCB_Filter_Button_Element extends TCB_Button_Element {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Filter Button Option', 'thrive-cb' );
	}

	/**
	 * Hide element from sidebar menu
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	/**
	 * Filter Button element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tcb-filter-button';
	}

	/**
	 * Allow this element to be also styled for active state
	 *
	 * The active state class is .tcb-active-state
	 *
	 * @return string
	 */
	public function active_state_config() {
		return true;
	}

	/**
	 * Read more components - more or less the same as the ones from the button
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['button']['disabled_controls']    = [ '.tcb-button-link-container', 'SecondaryText', 'ButtonSize', 'Align' ];
		$components['animation']['disabled_controls'] = [ '.btn-inline.anim-link' ];

		$components['scroll'] = [ 'hidden' => true ];

		$components = array_merge( $components, $this->shared_styles_component() );
		/* hide the Save button */
		$components['shared-styles']['disabled_controls'] = [ '.save-as-global-style' ];

		return $components;
	}
}
