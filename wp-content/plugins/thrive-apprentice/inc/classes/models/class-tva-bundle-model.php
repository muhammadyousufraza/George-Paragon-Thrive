<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 5/6/2019
 * Time: 12:42
 */

class TVA_Bundle_Model extends TVA_Product_Model {

	/**
	 * @var string
	 */
	protected $_key = 'bundle';

	/**
	 * @return $this
	 */
	public function set_protected_terms() {
		$protected_items = TVA_Terms_Collection::make(
			tva_term_query()->get_protected_items()
		)->get_sendowl_protected_items()->get_items();

		foreach ( $protected_items as $item ) {
			/** @var TVA_Term_Model */
			$pids = $item->get_sendowl_bundles_ids();

			if ( in_array( $this->id, $pids ) ) {
				$this->protected_terms[] = $item->get_id();
			}
		}

		return $this;
	}
}
