<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Stripe\Orders;

use Stripe\LineItem;
use Stripe\SubscriptionItem;
use TVA_Const;
use TVA_Customer;
use TVA_Order;
use TVA_Order_Item;
use WP_User;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

abstract class Generic {
	/**
	 * Used for subscriptions
	 */
	const RESUMED_STATUS = 5;

	protected $order;

	protected $data;

	protected $status;

	protected static $CACHE = [];

	public function __construct( $data, $status = TVA_Const::STATUS_COMPLETED ) {
		$this->data   = $data;
		$this->status = $status;
		$this->order  = new TVA_Order();
		$this->order->set_status( $status );
		$this->order->set_payment_method( TVA_Const::STRIPE_GATEWAY );
		$this->order->set_gateway( TVA_Const::STRIPE_GATEWAY );
		$this->order->set_type( TVA_Order::PAID );
		$this->process_data();
	}

	abstract public function process_data();

	public function save() {
		$customer = new TVA_Customer( $this->order->get_user_id() );

		if ( $this->order->get_status() === TVA_Const::STATUS_COMPLETED ) {
			foreach ( $this->order->get_order_items() as $order_item ) {
				$existing_orders = TVA_Order::get_orders_by_product( $order_item->get_product_id(), $this->order->get_user_id(), TVA_Const::STATUS_COMPLETED );
				if ( empty( $existing_orders ) ) {
					$customer->trigger_purchase( $this->order );
				}
			}
		}

		$this->order->save();

		$customer->trigger_course_purchase( $this->order, 'Stripe' );
	}

	/**
	 * Get the user by email or try to create a new one
	 *
	 * @param $email
	 * @param $first_name
	 * @param $last_name
	 *
	 * @return false|WP_User
	 */
	public function get_user( $email, $first_name = '', $last_name = '' ) {
		$email = sanitize_email( $email );
		$user  = wp_get_current_user();

		if ( empty( $user->ID ) ) {
			$user = get_user_by( 'email', $email );

			if ( false === $user instanceof WP_User && is_email( $email ) ) {
				$user = tva_ensure_new_user( $email, 'stripe', $first_name, $last_name );
			}
		}

		return $user instanceof WP_User ? $user : false;
	}


	/**
	 * Get line items from the order and add them to the order object
	 *
	 * @param $items
	 *
	 * @return void
	 */
	public function process_line_items( $items ) {
		/**
		 * @var LineItem|SubscriptionItem $item
		 */
		foreach ( $items as $item ) {
			$order_item = new TVA_Order_Item();
			$order_item->set_product_id( $item->price->id ); //save price id as product id
			$order_item->set_product_name( $item->description ?: 'Stripe product' );
			$order_item->set_quantity( $item->quantity );
			$order_item->set_unit_price( $item->price->unit_amount / 100 );
			$order_item->set_product_price( $item->price->unit_amount / 100 );
			$order_item->set_total_price( $item->amount_total / 100 );
			$order_item->set_product_type( $item->price->type );
			$order_item->set_currency( $item->price->currency );
			$this->order->set_order_item( $order_item );
		}
	}

	/**
	 * Go through all orders that contain the product and change their status
	 * e.g subscription is canceled
	 *
	 * @param $items
	 *
	 * @return void
	 */
	public function update_order_status( $items, $user_id = 0 ) {
		/**
		 * @var LineItem|SubscriptionItem $item
		 */
		foreach ( $items as $item ) {
			$this->change_product_status( $item->price->id, $this->status, $user_id );
		}
	}

	/**
	 * Change the status of all orders that contain the product
	 *
	 * @param $product_id
	 * @param $status
	 * @param $user_id
	 *
	 * @return void
	 */
	public function change_product_status( $product_id, $status = TVA_Const::STATUS_FAILED, $user_id = 0 ) {
		$orders = TVA_Order::get_orders_by_product( $product_id, $user_id );

		if ( is_array( $orders ) ) {
			$status = $status === static::RESUMED_STATUS ? TVA_Const::STATUS_COMPLETED : $status;
			foreach ( $orders as $order_data ) {
				$order = new TVA_Order();
				$order->set_data( $order_data );
				$order->set_id( $order_data['ID'] );
				$order->set_status( $status );
				$order->save();

				if ( $status === TVA_Const::STATUS_FAILED ) {
					foreach ( $order->get_order_items() as $order_item ) {
						$order_item_prod_id = $order_item->get_product_id();
						if ( empty( static::$CACHE[ $order_item_prod_id ] ) ) {
							do_action( 'tva_stripe_order_refunded', $order_item );
							static::$CACHE[ $order_item_prod_id ] = true;
						}
					}
				}
			}
		}
	}
}

