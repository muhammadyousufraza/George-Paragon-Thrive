<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Data_Object;
use Thrive\Automator\Items\Trigger;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Order_Refunded
 */
class Woo_Order_Refunded extends Trigger {
	/**
	 * Get the trigger identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'woocommerce/order_refunded';
	}

	/**
	 * Get the trigger hook
	 *
	 * @return string
	 */
	public static function get_wp_hook() {
		return 'woocommerce_order_status_refunded';
	}

	/**
	 * Get the trigger provided params
	 *
	 * @return array
	 */
	public static function get_provided_data_objects() {
		return array( 'user_data', 'woo_order_data', 'email_data' );
	}

	/**
	 * Get the number of params
	 *
	 * @return int
	 */
	public static function get_hook_params_number() {
		return 2;
	}

	/**
	 * Get the name of the app to which the hook belongs
	 *
	 * @return string
	 */
	public static function get_app_id() {
		return Woo_App::get_id();
	}

	/**
	 * Get the trigger name
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'WooCommerce order refunded';
	}

	/**
	 * Get the trigger description
	 *
	 * @return string
	 */
	public static function get_description() {
		return 'This trigger will be fired whenever a WooCommerce order is refunded';
	}

	/**
	 * Get the trigger logo
	 *
	 * @return string
	 */
	public static function get_image() {
		return 'woo-refund-order';
	}

	public function process_params( $params = array() ) {
		$data = array();

		if ( ! empty( $params[1] ) ) {
			$data_object_classes = Data_Object::get();

			$order = $params[1];

			$data['user_data']      = empty( $data_object_classes['user_data'] ) ? null : new $data_object_classes['user_data']( $order->get_user() );
			$data['woo_order_data'] = empty( $data_object_classes['woo_order_data'] ) ? $order : new $data_object_classes['woo_order_data']( $order );
			$data['email_data']     = empty( $data_object_classes['email_data'] ) ? null : new $data_object_classes['email_data']( $order->get_billing_email() );
		}

		return $data;
	}

}
