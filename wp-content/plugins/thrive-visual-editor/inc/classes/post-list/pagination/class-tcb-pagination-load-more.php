<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TCB_Pagination_Load_More extends TCB_Pagination {
	const IDENTIFIER = 'tcb-pagination-load-more-button';

	/**
	 * Get the pagination content for the current type.
	 *
	 * @return string|null
	 */
	public function get_content() {
		return tcb_template( 'pagination/load-more-button.php', '', true );
	}

	/**
	 * Get the label for this type.
	 *
	 * @return string|void
	 */
	public function get_label() {
		return __( 'Load More', 'thrive-cb' );
	}
}
