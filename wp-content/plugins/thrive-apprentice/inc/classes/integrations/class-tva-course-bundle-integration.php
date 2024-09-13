<?php

/**
 * Class TVA_Course_Bundle_Integration
 */
class TVA_Course_Bundle_Integration extends TVA_Integration {

	protected function init_items() {
	}

	protected function _get_item_from_membership( $key, $value ) {
		return array();
	}

	public function get_customer_access_items( $customer ) {
		return array();
	}

	/**
	 * Checks if current user has and valid order with for a course bundle
	 * - the bundle should contain current course id
	 *
	 * @param array $rule
	 *
	 * @return bool
	 */
	public function is_rule_applied( $rule ) {

		$allowed  = false;
		$tva_user = tva_access_manager()->get_tva_user();
		$course   = \TVA\Architect\Visual_Builder\tcb_tva_visual_builder()->get_active_course();

		if ( ! $tva_user || ! $course ) {
			return false;
		}

		$course_id = $course->get_id();

		/** @var TVA_Order $order */
		foreach ( $tva_user->get_orders() as $order ) {

			if ( $order->get_status() === TVA_Const::STATUS_COMPLETED ) {

				foreach ( $order->get_order_items() as $order_item ) {

					if ( $order_item->get_status() !== 1 ) {
						continue;
					}

					$bundle = TVA_Bundle::init_by_number( $order_item->get_product_id() );

					if ( $bundle instanceof TVA_Bundle && $bundle->contains_product( $course_id ) ) {
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
