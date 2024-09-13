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
 * Class TCB_Pagination_Load_More_Element
 */
class TCB_Pagination_Load_More_Element extends TCB_Button_Element {
	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Load More Button', 'thrive-cb' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.' . TCB_Pagination_Load_More::IDENTIFIER;
	}

	/**
	 * Hide this element in the sidebar.
	 *
	 * @return string
	 */
	public function hide() {
		return true;
	}

	/**
	 * The 'Load More' components - more or less the same as the ones from the button
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		$components['button']['disabled_controls']    = [ '.tcb-button-link-container-divider', '#tcb-button-link-search-control', '.tcb-button-link-options-container', '.tcb-button-link-container', 'DynamicLink' ];
		$components['animation']['disabled_controls'] = [ '.btn-inline.anim-link', '.btn-inline.anim-popup' ];

		$components['scroll']     = [ 'hidden' => true ];
		$components['responsive'] = [ 'hidden' => true ];

		$components = array_merge( $components, $this->shared_styles_component() );

		/* hide the Save button */
		$components['shared-styles']['disabled_controls'] = [ '.save-as-global-style' ];

		return $components;
	}
}
