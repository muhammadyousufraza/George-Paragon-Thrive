<?php

namespace TVE\Dashboard\Automator;

use Exception;
use Thrive\Automator\Items\Data_Object;
use function wc_get_order;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Woo_Order_Data
 */
class Woo_Order_Data extends Data_Object {

	/**
	 * Get the data-object identifier
	 *
	 * @return string
	 */
	public static function get_id() {
		return 'woo_order_data';
	}

	public static function get_nice_name() {
		return 'WooCommerce order';
	}

	/**
	 * Array of field object keys that are contained by this data-object
	 *
	 * @return array
	 */
	public static function get_fields() {
		return array(
			'woo_order_id',
			'order_number',
			'order_key',
			'customer_id',
			'billing_first_name',
			'billing_last_name',
			'billing_company',
			'billing_address_1',
			'billing_address_2',
			'billing_city',
			'billing_state',
			'billing_postcode',
			'billing_country',
			'billing_email',
			'billing_phone',

			'shipping_first_name',
			'shipping_last_name',
			'shipping_company',
			'shipping_address_1',
			'shipping_address_2',
			'shipping_city',
			'shipping_state',
			'shipping_postcode',
			'shipping_country',

			'payment_method',
			'payment_method_title',
			'transaction_id',
			'customer_ip_address',
			'customer_user_agent',
			'created_via',
			'customer_note',
			'date_completed',

			'date_paid',
			'cart_hash',
			'get_parent_id',
			'order_currency',
			'order_version',
			'prices_include_tax',
			'date_created',
			'date_modified',

			'order_status',
			'discount_total',
			'shipping_total',
			'shipping_tax',
			'cart_tax',
			'grand_total',
			'total_tax',
			'coupon_used',
			'item_count',
			'has_free_item',
		);
	}

	public static function create_object( $param ) {
		if ( empty( $param ) ) {
			throw new Exception( 'No parameter provided for Woo_Order_Data object' );
		}

		$order = null;
		if ( is_a( $param, 'WC_Order' ) ) {
			$order = $param;
		} elseif ( is_numeric( $param ) ) {
			$order = wc_get_order( $param );
		}

		if ( $order ) {
			return array(
				'woo_order_id'       => $order->get_id(),
				'order_number'       => $order->get_order_number(),
				'order_key'          => $order->get_order_key(),
				'customer_id'        => $order->get_customer_id(),
				'billing_first_name' => $order->get_billing_first_name(),
				'billing_last_name'  => $order->get_billing_last_name(),
				'billing_company'    => $order->get_billing_company(),
				'billing_address_1'  => $order->get_billing_address_1(),
				'billing_address_2'  => $order->get_billing_address_2(),
				'billing_city'       => $order->get_billing_city(),
				'billing_state'      => $order->get_billing_state(),
				'billing_postcode'   => $order->get_billing_postcode(),
				'billing_country'    => $order->get_billing_country(),
				'billing_email'      => $order->get_billing_email(),
				'billing_phone'      => $order->get_billing_phone(),

				'shipping_first_name' => $order->get_shipping_first_name(),
				'shipping_last_name'  => $order->get_shipping_last_name(),
				'shipping_company'    => $order->get_shipping_company(),
				'shipping_address_1'  => $order->get_shipping_address_1(),
				'shipping_address_2'  => $order->get_shipping_address_2(),
				'shipping_city'       => $order->get_shipping_city(),
				'shipping_state'      => $order->get_shipping_state(),
				'shipping_postcode'   => $order->get_shipping_postcode(),
				'shipping_country'    => $order->get_shipping_country(),

				'payment_method'       => $order->get_payment_method(),
				'payment_method_title' => $order->get_payment_method_title(),
				'transaction_id'       => $order->get_transaction_id(),
				'customer_ip_address'  => $order->get_customer_ip_address(),
				'customer_user_agent'  => $order->get_customer_user_agent(),
				'created_via'          => $order->get_created_via(),
				'customer_note'        => $order->get_customer_note(),
				'date_completed'       => $order->get_date_completed(),
				'date_paid'            => $order->get_date_paid(),
				'cart_hash'            => $order->get_cart_hash(),
				'parent_id'            => $order->get_parent_id(),
				'order_currency'       => $order->get_currency(),
				'order_version'        => $order->get_version(),
				'prices_include_tax'   => $order->get_prices_include_tax(),
				'date_created'         => $order->get_date_created(),
				'date_modified'        => $order->get_date_modified(),
				'order_status'         => $order->get_status(),
				'discount_total'       => $order->get_discount_total(),
				'shipping_total'       => $order->get_shipping_total(),
				'shipping_tax'         => $order->get_shipping_tax(),
				'cart_tax'             => $order->get_cart_tax(),
				'grand_total'          => $order->get_total(),
				'total_tax'            => $order->get_total_tax(),
				'coupon_used'          => $order->get_coupon_codes(),
				'item_count'           => $order->get_item_count(),
				'has_free_item'        => $order->has_free_item(),
			);
		}

		return $order;
	}
}
