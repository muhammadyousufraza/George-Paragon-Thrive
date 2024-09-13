<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TCB_Pagination_None extends TCB_Pagination {

	/**
	 * Get the pagination content for the current type.
	 *
	 * @return string|null
	 */
	public function get_content() {
		return '';
	}

	/**
	 * Get the label for this type.
	 *
	 * @return string|void
	 */
	public function get_label() {
		return __( 'None', 'thrive-cb' );
	}
}
