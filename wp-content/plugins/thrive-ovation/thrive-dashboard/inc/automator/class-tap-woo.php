<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVE\Dashboard\Automator;
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

use function get_user_by;
use function wc_get_order;
use function wc_get_products;

class Woo {

	public static function init() {
		static::hooks();
	}

	public static function hooks() {
		add_filter( 'td_automator_should_load_file', [ __CLASS__, 'should_load_files' ], 10, 2 );

		if ( static::exists() ) {
			add_action( 'woocommerce_order_refunded', array(
				__CLASS__,
				'do_woocommerce_refund_product_action',
			) );

			add_action( 'woocommerce_order_status_completed', array(
				__CLASS__,
				'do_woocommerce_product_purchase_completed',
			) );

			add_action( 'woocommerce_order_status_processing', array(
				__CLASS__,
				'do_woocommerce_product_purchase_processing',
			) );
		}
	}

	public static function should_load_files( $load, $filename ) {
		if ( strpos( basename( $filename, '.php' ), '-woo-' ) !== false && ! static::exists() ) {
			$load = false;
		}

		return $load;
	}

	/**
	 * Get all products with variations included
	 *
	 * @return array
	 */
	public static function get_products() {
		$products = [];

		foreach ( wc_get_products( array( 'limit' => - 1 ) ) as $product ) {
			$products[] = $product;
			if ( $product->get_type() === 'variable' ) {
				foreach ( $product->get_children() as $variation_id ) {
					$products[] = wc_get_product( $variation_id );
				}
			}
		}


		return $products;
	}


	public static function exists() {
		return class_exists( 'WooCommerce', false );
	}

	public static function do_woocommerce_refund_product_action( $order_id ) {
		$order         = wc_get_order( $order_id );
		$user          = get_user_by( 'id', $order->get_report_customer_id() );
		$billing_email = $order->get_billing_email();
		foreach ( $order->get_items() as $product ) {
			if ( $product->get_quantity() != 0 ) {
				do_action( 'thrive_woo_product_refund', $product, $user, $billing_email );
			}
		}
	}

	public static function do_woocommerce_product_purchase_completed( $order_id ) {
		$order         = wc_get_order( $order_id );
		$user          = get_user_by( 'id', $order->get_customer_id() );
		$billing_email = $order->get_billing_email();
		foreach ( $order->get_items() as $product ) {
			do_action( 'thrive_woo_product_purchase_completed', $product, $user, $billing_email );
		}
	}

	public static function do_woocommerce_product_purchase_processing( $order_id ) {
		$order         = wc_get_order( $order_id );
		$user          = get_user_by( 'id', $order->get_customer_id() );
		$billing_email = $order->get_billing_email();
		foreach ( $order->get_items() as $product ) {
			do_action( 'thrive_woo_product_purchase_processing', $product, $user, $billing_email );
		}
	}
}
