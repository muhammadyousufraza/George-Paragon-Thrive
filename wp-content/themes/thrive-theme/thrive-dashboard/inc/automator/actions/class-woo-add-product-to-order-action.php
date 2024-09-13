<?php

namespace TVE\Dashboard\Automator;

use Thrive\Automator\Items\Action;
use function wc_get_order;
use function wc_get_product;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Add_Product_To_Order
 */
class Woo_Add_Product_To_Order extends Action {

	private $products;

	/**
	 * Get the action identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'woo/producttoorder';
	}

	/**
	 * Get the action name/label
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'Add WooCommerce product to order';
	}

	/**
	 * Get the action description
	 *
	 * @return string
	 */
	public static function get_description() {
		return 'Add a WooCommerce product to an existing order';
	}

	/**
	 * Get the action logo
	 *
	 * @return string
	 */
	public static function get_image() {
		return 'woo-add-product-to-order';
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
		return array( 'woo_products' );
	}

	/**
	 * Get an array of keys with the required data-objects
	 *
	 * @return array
	 */
	public static function get_required_data_objects() {
		return array( 'woo_order_data' );
	}

	public function prepare_data( $data = array() ) {
		if ( ! empty( $data['extra_data'] ) ) {
			$data = $data['extra_data'];
		}
		$this->products = $data['woo_products']['value'];
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
		foreach ( $this->products as $product_id ) {
			$product = wc_get_product( $product_id );

			if ( $product ) {
				$order->add_product( $product );
			}
		}
	}

}
