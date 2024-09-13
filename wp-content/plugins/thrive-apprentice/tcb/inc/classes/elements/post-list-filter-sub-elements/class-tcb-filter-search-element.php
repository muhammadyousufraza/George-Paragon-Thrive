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
 * Class TCB_Filter_Search_Element
 */
class TCB_Filter_Search_Element extends TCB_Search_Form_Element {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Filter Search Option', 'thrive-cb' );
	}

	/**
	 * Filter Button element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.tcb-filter-search';
	}

	/**
	 * Hide element from sidebar menu
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
	}
}
