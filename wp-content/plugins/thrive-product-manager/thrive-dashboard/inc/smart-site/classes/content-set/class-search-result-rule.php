<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVD\Content_Sets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Search_Result_Rule
 *
 * @package TVD\Content_Sets
 * @project : thrive-dashboard
 */
class Search_Result_Rule extends Rule {

	/**
	 * @return bool
	 */
	public function is_valid() {
		return true;
	}

	/**
	 * Returns true if the active query is for a search.
	 *
	 * @param \WP_Post|\WP_Term $post_or_term
	 *
	 * @return bool
	 */
	public function matches( $post_or_term ) {
		return is_search();
	}
}
