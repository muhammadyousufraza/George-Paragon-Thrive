<?php
/**
 * Created by PhpStorm.
 * User: dan bilauca
 * Date: 15-Apr-19
 * Time: 03:37 PM
 */

/**
 * Class TVA_SendOwl_Bundle_Integration
 * - implements TVA_Integration methods
 */
class TVA_SendOwl_Bundle_Integration extends TVA_SendOwl_Integration {

	protected $_course_membership_meta_name = 'tva_bundle_ids';

	protected $_membership_key = 'sendowl';

	protected function init_items() {

		$items    = array();
		$products = TVA_SendOwl::get_bundles();

		if ( is_array( $products ) && ! empty( $products ) ) {
			foreach ( $products as $product ) {
				try {
					$items[] = new TVA_Integration_Item( $product['id'], $product['name'] );
				} catch ( Exception $e ) {

				}
			}
		}

		$this->set_items( $items );
	}

	protected function _get_item_from_membership( $key, $value ) {

		$product = TVA_SendOwl::get_bundle_by_id( $value );

		if ( ! is_array( $product ) ) {
			return;
		}

		return new TVA_Integration_Item( $product['id'], $product['name'] );
	}

	/**
	 * Array of sendowl product ids user has purchased
	 *
	 * @param TVA_Customer $customer
	 *
	 * @return integer[]
	 */
	public function get_customer_access_items( $customer ) {

		$ids = array();

		if ( true === $customer instanceof TVA_Customer ) {
			$ids = $customer->get_sendowl_bundles();
		}

		return $ids;
	}
}
