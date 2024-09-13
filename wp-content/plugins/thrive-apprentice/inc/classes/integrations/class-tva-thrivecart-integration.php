<?php

class TVA_ThriveCart_Integration extends TVA_Integration {

	/**
	 * ThriveCart Integration has no item to be checked as WP has for editor/author/roles/etc
	 */
	protected function init_items() {
	}

	/**
	 * There is no need to convert memberships into rule system
	 * - no backwards compat required
	 *
	 * @param mixed $key
	 * @param mixed $value
	 *
	 * @return TVA_Integration_Item|void
	 */
	protected function _get_item_from_membership( $key, $value ) {
	}

	public function is_rule_applied( $rule ) {

		$allowed    = false;
		$tva_user   = tva_access_manager()->get_tva_user();
		$product    = tva_access_manager()->get_product();
		$product_id = $product ? $product->get_id() : null;

		if ( $product_id && true === $tva_user instanceof TVA_User ) {
			$order = $tva_user->has_bought( $product_id );
			if ( $order instanceof TVA_Order ) {
				$this->set_order( $order );
				$this->set_order_item( $order->get_order_item_by_product_id( $product_id ) );
				$allowed = true;
			}
		}

		return $allowed;
	}

	/**
	 * For this do nothing so that the WP Login Form is displayed
	 */
	public function trigger_no_access() {
	}

	/**
	 * Array of curse ids user has purchased
	 *
	 * @param TVA_Customer $customer
	 *
	 * @return integer[]
	 */
	public function get_customer_access_items( $customer ) {

		$ids = array();

		if ( true === $customer instanceof TVA_Customer ) {
			$ids = $customer->get_thrivecart_courses();
		}

		return $ids;
	}
}
