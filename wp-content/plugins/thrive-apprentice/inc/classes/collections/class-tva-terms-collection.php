<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 4/24/2019
 * Time: 15:12
 */

class TVA_Terms_Collection extends TVA_Collection {

	/**
	 * @var string
	 */
	protected $model = 'TVA_Term_Model';

	/**
	 * Filters current collection by sendowl protection rule
	 *
	 * @return TVA_Terms_Collection
	 */
	public function get_sendowl_protected_items() {

		$items = array();

		foreach ( $this->get_items() as $item ) {

			/** @var TVA_Term_Model $item */
			if ( $item->is_protected_by_sendowl() ) {
				$items[] = $item;
			}
		}

		return new self( $items );
	}

	/**
	 * Filters the current collection for WordPress protection rule
	 *
	 * @return TVA_Terms_Collection protected by WP user roles
	 */
	public function get_wp_protected_items() {

		$items = array();

		foreach ( $this->get_items() as $item ) {

			/** @var TVA_Term_Model $item */
			if ( $item->is_protected_by_wp() ) {
				$items[] = $item;
			}
		}

		return new self( $items );
	}

}
