<?php

/**
 * Class TQB_Structure_Page
 * A page of a Quiz Structure: Splash|Optin|Result
 *
 * @property array related_categories
 */
class TQB_Structure_Page {

	/**
	 * @var WP_Post|null
	 */
	protected $_post;

	public function __construct( $post ) {

		$this->_post = $post instanceof WP_Post ? $post : get_post( (int) $post );
	}

	/**
	 * Save defaults meta to a post
	 *
	 * @return bool
	 */
	public function save_default_metas() {

		if ( ! $this->_post instanceof WP_Post ) {
			return false;
		}

		return true;
	}

	/**
	 * @return WP_Post|null
	 */
	public function to_json() {

		return $this->_post;
	}

	/**
	 * Clones the properties of current structure page to a new post
	 *
	 * @param WP_Post|int $new_post
	 *
	 * @return bool
	 */
	public function clone_to( $new_post ) {
		return true;
	}

	/**
	 * Deletes the current post and its dependencies
	 *
	 * @return bool
	 */
	public function delete() {
		return true;
	}
}
