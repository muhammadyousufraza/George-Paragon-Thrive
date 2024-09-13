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
 * Class Archive_Rule
 *
 * @package TVD\Content_Sets
 * @project : thrive-dashboard
 */
class Archive_Rule extends Rule {

	/**
	 * Returns true if the rule is valid
	 *
	 * NOTE: for now the archive rules is only supported for Authors
	 *
	 * @return bool
	 */
	public function is_valid() {
		$valid = parent::is_valid();

		if ( $this->field !== 'author' ) {
			/**
			 * For now we only support Author field for Archive Rules
			 */
			$valid = false;
		}

		return $valid;
	}

	/**
	 * Should be extended in child classes
	 *
	 * @param string   $query_string
	 * @param bool|int $paged    if non-false, it will return limited results
	 * @param int      $per_page number of results per page. ignored if $paged = false
	 *
	 * @return array
	 */
	public function get_items( $query_string = '', $paged = false, $per_page = 15 ) {
		$this->paged        = $paged;
		$this->per_page     = $per_page;
		$this->query_string = $query_string;

		$items = array();

		if ( $this->field === 'author' ) {
			$items = parent::search_users();
		}

		return $items;
	}

	/**
	 * Test if a rule matches the given params
	 *
	 * @param \WP_User $user
	 *
	 * @return bool
	 */
	public function matches( $user ) {
		if ( is_author() ) {
			return $this->match_value( $user->ID, $user );
		}

		return false;
	}
}
