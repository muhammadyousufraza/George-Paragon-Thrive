<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Action;
use function wc_create_refund;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Refund_Order
 */
class Woo_Refund_Order extends Action {
	/**
	 * Get the action identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'woo/refundorder';
	}

	/**
	 * Get the action name/label
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'Refund WooCommerce order';
	}

	/**
	 * Get the action description
	 *
	 * @return string
	 */
	public static function get_description() {
		return 'Refund the WooCommerce order';
	}

	/**
	 * Get the action logo
	 *
	 * @return string
	 */
	public static function get_image() {
		return 'woo-refund-order';
	}

	/**
	 * Get the name of app to which action belongs
	 *
	 * @return string
	 */
	public static function get_app_id() {
		return Woo_App::get_id();
	}

	/**
	 * Array of action-field keys, required for the action to be setup
	 *
	 * @return array
	 */
	public static function get_required_action_fields() {
		return array();
	}

	/**
	 * Get an array of keys with the required data-objects
	 *
	 * @return array
	 */
	public static function get_required_data_objects() {
		return array( 'woo_order_data' );
	}

	public function do_action( $data ) {
		global $automation_data;
		$order_data = $automation_data->get( 'woo_order_data' );
		if ( empty( $order_data ) ) {
			return false;
		}

		$order = wc_get_order( $order_data->get_value( Woo_Order_Id::get_id() ) );

		if ( empty( $order ) ) {
			return false;
		}
		wc_create_refund( array( 'order_id' => $order_data->get_value( Woo_Order_Id::get_id() ) ) );
	}

}
