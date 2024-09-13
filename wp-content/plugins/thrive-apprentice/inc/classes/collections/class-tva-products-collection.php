<?php
/**
 * Created by PhpStorm.
 * User: Andrei
 * Date: 4/29/2019
 * Time: 1:55 PM
 */

class TVA_Products_Collection extends TVA_Collection {

	protected $id_key = 'id';

	protected $model = 'TVA_Product_Model';

	public function prepare_for_db() {

		$response = array();

		foreach ( $this->get_items() as $item ) {
			/** @var TVA_Product_Model $item */
			$response[] = $item->to_db();
		}

		return $response;
	}

	/**
	 * Get all items which protect a given term
	 *
	 * @param int $term_id
	 *
	 * @return TVA_Products_Collection
	 */
	public function filter_by_term( $term_id = 0 ) {
		return $this->filter(
			function ( $item ) use ( $term_id ) {
				return in_array( $term_id, $item->protected_terms );
			}
		);
	}

	/**
	 * Get all items of type: product
	 *
	 * @return TVA_Products_Collection
	 */
	public function get_products() {
		return $this->filter(
			function ( $item ) {
				return 'product' === $item->type;
			}
		);
	}

	/**
	 * Get all items of type: bundle
	 *
	 * @return TVA_Products_Collection
	 */
	public function get_bundles() {
		return $this->filter(
			function ( $item ) {
				return 'bundle' === $item->type;
			}
		);
	}
}