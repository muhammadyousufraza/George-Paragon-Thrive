<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}


if ( ! class_exists( 'TCB_Pagination' ) ) {
	require_once TVE_TCB_ROOT_PATH . 'inc/classes/post-list/pagination/class-tcb-pagination.php';
}

class TCB_Pagination_Infinite_Scroll extends TCB_Pagination {
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
		return __( 'Infinite Scroll', 'thrive-theme' );
	}
}
