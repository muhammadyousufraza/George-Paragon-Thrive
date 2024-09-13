<?php

namespace TVA\Automator;

use InvalidArgumentException;
use Thrive\Automator\Items\Data_Object;
use TVA_Order;
use TVA_Order_Item;
use TVA_SendOwl;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Order_Data
 */
class Order_Data extends Data_Object {

	/**
	 * Get the data-object identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'order_data';
	}

	public static function get_nice_name() {
		return 'Apprentice order';
	}

	/**
	 * Array of field object keys that are contained by this data-object
	 *
	 * @return array
	 */
	public static function get_fields() {
		return [ 'product_name', 'access_type_slug', 'order_amount', 'payment_processor', 'payment_date' ];
	}

	public static function create_object( $param ) {
		if ( empty( $param ) ) {
			throw new InvalidArgumentException( 'No parameter provided for Order_Data object' );
		}

		$order_item = null;
		if ( is_a( $param, 'TVA_Order_Item' ) ) {
			$order_item = $param;
		} elseif ( is_numeric( $param ) ) {
			$order_item = new TVA_Order_Item( $param );
		}

		if ( $order_item ) {
			$order               = new TVA_Order( $order_item->get_order_id() );
			$sendowl_product_ids = TVA_SendOwl::get_products_ids();
			$wc_product          = null;
			if ( function_exists( 'wc_get_product' ) ) {
				$wc_product = wc_get_product( $order_item->get_gateway_order_item_id() );
			}

			$access_type      = $order_item->get_access_type( $order, $sendowl_product_ids, $wc_product );
			$access_type_slug = $order->is_woocommerce() ? 'woocommerce-product' : $order_item->get_access_type_slug( $access_type );

			return [
				'product_name'      => $order_item->get_product_name(),
				'access_type'       => $access_type,
				'access_type_slug'  => $access_type_slug,
				'order_amount'      => $order->get_price_gross(),
				'payment_processor' => $order->get_gateway(),
				'payment_date'      => $order_item->get_created_at(),
			];
		}

		return $order_item;
	}
}
