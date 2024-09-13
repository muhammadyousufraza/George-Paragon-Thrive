<?php

/**
 * Class TVA_Manual_Integration
 * - used when admin gives manual access to a course for a customer
 */
class TVA_Manual_Integration extends TVA_ThriveCart_Integration {

	public function get_customer_access_items( $customer ) {
		return array();
	}

	/**
	 * A user has access if:
	 * - order has status completed
	 * - order is manual
	 * - order item has status 1
	 *
	 * @param array $rule
	 *
	 * @return bool
	 */
	public function is_rule_applied( $rule ) {

		$allowed  = false;
		$tva_user = tva_access_manager()->get_tva_user();
		$course   = tva_access_manager()->get_product();

		if ( ! $tva_user || ! $course ) {
			return false;
		}

		$course_id = $course->get_id();

		/** @var TVA_Order $order */
		foreach ( $tva_user->get_orders() as $order ) {

			if ( $order->get_status() === TVA_Const::STATUS_COMPLETED && $order->is_manual() ) {

				foreach ( $order->get_order_items() as $order_item ) {

					if ( $order_item->get_status() === 1 && (int) $order_item->get_product_id() === $course_id ) {
						$this->set_order( $order );
						$this->set_order_item( $order_item );
						$allowed = true;
						break;
					}
				}
			}
		}

		return $allowed;
	}
}
