<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class TVA_SendOwl_Payment_Gateway
 */
class TVA_SendOwl_Payment_Gateway extends TVA_Payment_Gateway_Abstract {

	/**
	 * sendowl account key
	 *
	 * @var array
	 */
	private $signing_key
		= array(
			'key'    => '',
			'secret' => '',
		);

	/**
	 * The order data from the IPN
	 *
	 * @var $order
	 */
	private $order_data;

	/**
	 * @var int
	 */
	private $order_id;

	/**
	 * Veify the signiture in the request
	 *
	 * @return bool
	 */
	protected function verify_request() {
		$this->signing_key = TVA_SendOwl::get_account_keys();

		do_action( 'tva_before_sendowl_verify_request', $this->raw_data, $this->data, $this->server );

		if ( isset( $this->server['HTTP_X_SENDOWL_HMAC_SHA256'] ) ) {
			$hmac_header = $this->server['HTTP_X_SENDOWL_HMAC_SHA256'];

			$calculated_hmac = base64_encode( hash_hmac( 'sha256', $this->raw_data, $this->get_secret_key(), true ) );
			if ( $hmac_header == $calculated_hmac ) {

				$this->order_data = $this->data->order;

				return true;
			}
		}

		TVA_Logger::set_type( 'Request Verification' );
		TVA_Logger::log( 'Verify Request', array( 'failed' => 'HTTP_X_SENDOWL_HMAC_SHA256 Verification Failed' ), true, null );

		do_action( 'tva_after_sendowl_verify_request', $this->raw_data, $this->data, $this->server );

		return false;
	}

	/**
	 * @return mixed
	 */
	private function get_secret_key() {
		return $this->signing_key['secret'];
	}

	/**
	 * Process the order
	 */
	protected function process_notification() {
		do_action( 'tva_before_sendowl_process_notification', $this->raw_data, $this->data, $this->server );

		/**
		 * Order ID verification
		 */
		if ( empty( $this->order_data->tag ) ) {
			TVA_Logger::set_type( 'Process Notification' );
			TVA_Logger::log(
				'Order Identification',
				array(
					'failed' => 'Missing Order ID',
					'order'  => $this->order_data,
				),
				true,
				null
			);

			$this->status  = 400;
			$this->message = 'Missing Order ID';

			return;
		}

		/**
		 * Get Order from order_id (with the data set from the checkout)
		 */
		$this->order_obj = new TVA_Order( $this->get_order_id() );

		/**
		 * Check if order exists otherwise exit
		 */

		$order_obj_id = $this->order_obj->get_id();

		if ( empty( $order_obj_id ) ) {
			TVA_Logger::set_type( 'Process Notification' );
			TVA_Logger::log(
				'Order Identification',
				array(
					'failed' => 'Order does not exist',
					'order'  => maybe_serialize( $this->order_data ),
				),
				true,
				null
			);

			$this->status  = 400;
			$this->message = 'Order does not exist';

			return;
		}

		$order = $this->process_order();

		if ( $order ) {
			$entry = $this->create_transaction_entry();

			if ( $entry ) {
				$this->status  = 200;
				$this->message = 'Success !';

				do_action( 'tva_after_sendowl_process_notification', $this->raw_data, $this->data, $this->server, $order );
			}
		}

	}

	/**
	 * Add the new data to the order
	 */
	protected function process_order() {
		/**
		 * Set the data from the IPN
		 */
		$this->order_obj->set_data( $this->order_data );

		$this->_set_order_status();
		$this->_prepare_order_items();

		$this->order_obj->set_type( TVA_Order::PAID );
		$this->order_obj->set_gateway( TVA_Const::SENDOWL_GATEWAY );

		$result = $this->order_obj->save();

		if ( $this->order_obj->get_status() === TVA_Const::STATUS_FAILED ) {
			TVA_Logger::set_type( 'Process Order' );
			TVA_Logger::log(
				'Order Failed',
				array(
					'failed' => 'Order STATUS was set to FAILED',
					'order'  => maybe_serialize( $this->order_obj ),
				),
				true,
				null
			);
		}

		return $result;
	}

	/**
	 * Set the status of the order
	 */
	private function _set_order_status() {

		if ( 'complete' !== $this->order_data->state && 'free' !== $this->order_data->state ) {
			/**
			 * The order has failed
			 */
			$this->order_obj->set_status( TVA_Const::STATUS_FAILED );

			return;
		}

		if ( empty( $this->order_data->refunded ) ) {
			$this->order_obj->set_status( TVA_Const::STATUS_COMPLETED );

			return;
		}

		$price = $this->order_obj->get_price_gross();

		/**
		 * The order is refunded
		 */
		if ( (int) ceil( $price ) === 0 ) {
			if ( $this->order_obj->get_status() !== TVA_Const::STATUS_REFUND ) {
				/**
				 * SendOwl sends 3 refund requests when the admin issues a refund
				 * This hook logic needs to be called only once
				 */
				do_action( 'tva_sendowl_order_refunded', $this->order_obj );
			}

			$this->order_obj->set_status( TVA_Const::STATUS_REFUND );
		}
	}

	/**
	 * Prepare order items from the ipn
	 */
	private function _prepare_order_items() {

		foreach ( $this->order_data->cart->cart_items as $key => $item ) {
			$order_item = $this->order_obj->get_order_item_by_product_id( $item->product->id );

			if ( false === $order_item ) {
				$this->_add_order_item( $item ); // in case of an up sell we may not have all items in the order, so we add them here
			} else {
				$this->_prepare_order_item_data( $order_item, $item );
			}
		}
	}

	/**
	 * Add an order item to the order
	 *
	 * @param $item
	 */
	private function _add_order_item( $item ) {

		$product = TVA_SendOwl::get_product_by_id( $item->product->id );
		$product = $product ? $product : TVA_SendOwl::get_bundle_by_id( $item->product->id );

		if ( ! is_array( $product ) ) {
			return;
		}

		$order_item = new TVA_Order_Item();

		$this->_prepare_order_item_data( $order_item, $item );
		$order_item->set_data( $product );
		$this->order_obj->set_order_item( $order_item );
	}

	/**
	 * Set the required data for the order item
	 *
	 * @param TVA_Order_Item $order_item
	 * @param stdClass       $item
	 */
	private function _prepare_order_item_data( $order_item, $item ) {

		if ( ! is_object( $item ) ) {
			$item = new stdClass();
		}

		$order_item_data = array(
			'gateway_order_id'      => $this->order_data->sendowl_order_id,
			'gateway_order_item_id' => $item->id,
			'product_type'          => $item->product->product_type,
			'product_id'            => $item->product->id,
			'product_name'          => $item->product->name,
			'product_price'         => $item->product->price,
			'currency'              => $this->order_data->settled_currency,
			'quantity'              => $item->quantity,
			'unit_price'            => $item->unit_price,
			'total_price'           => $item->total_price,
		);

		$order_item->set_data( $order_item_data );
	}

	/**
	 * Log the last transaction we have
	 */
	protected function create_transaction_entry() {
		$transaction_data = end( $this->data->order->transactions );

		if ( 'complete' === $this->order_data->state || 'free' === $this->order_data->state ) {
			if ( empty( $this->order_data->refunded ) ) {
				$transaction_type = TVA_Const::STATUS_COMPLETED;
			} else {
				$transaction_type = TVA_Const::STATUS_REFUND;
			}
		} else {
			TVA_Logger::set_type( 'Transaction Entry' );
			TVA_Logger::log(
				'Create transaction',
				array(
					'failed' => 'The Transaction has failed',
					'order'  => maybe_serialize( $transaction_data ),
				),
				true,
				null
			);

			$transaction_type = TVA_Const::STATUS_FAILED;

		}

		$transaction_data = end( $this->order_data->transactions );
		if ( empty( $transaction_data ) ) {
			// SUPP-15508 - this must have been a completely free order - with a discount coupon applied. No actual transaction has been made
			return true;
		}
		$transaction = new TVA_Transaction();

		$data = array(
			'order_id'           => $this->order_obj->get_id(),
			'transaction_id'     => $transaction_data->gateway_transaction_id,
			'currency'           => $transaction_data->payment_currency,
			'price'              => $transaction_data->net_price,
			'price_gross'        => $transaction_data->payment_gross,
			'gateway_fee'        => $transaction_data->payment_gateway_fee,
			'transaction_type'   => $transaction_type,
			'gateway'            => $this->order_data->gateway,
			'card_last_4_digits' => $transaction_data->card_last_4_digits,
			'card_expires_at'    => $transaction_data->card_expires_at,
		);

		$transaction->set_data( $data );

		$result = $transaction->save();

		return $result;
	}

	/**
	 * Log the IPN
	 */
	protected function log_ipn() {

		if ( 'complete' === $this->data->order->state ) {
			if ( empty( $this->data->order->refunded ) ) {
				/**
				 * Completed Status
				 */
				$status         = TVA_Const::STATUS_COMPLETED;
				$payment_status = 'Completed';
			} else {
				/**
				 * Refunded Status
				 */
				$status         = TVA_Const::STATUS_REFUND;
				$payment_status = 'Refund';
			}
		} else {
			/**
			 * Failed Status
			 */
			$status         = TVA_Const::STATUS_FAILED;
			$payment_status = 'Failed';
		}

		$transaction = end( $this->data->order->transactions );

		$data = array(
			'order_id'         => $this->get_order_id(),
			'gateway_order_id' => $this->data->order->sendowl_order_id,
			'gateway'          => $this->data->order->gateway,
			'status'           => $status,
			'payment_status'   => $payment_status,
			'transaction_id'   => $transaction->gateway_transaction_id,
			'ipn_content'      => maybe_serialize( $this->data->order ),
			'created_at'       => date( 'Y-m-d H:i:s' ),
		);

		$this->wpdb->insert(
			$this->wpdb->prefix . TVA_Const::DB_PREFIX . TVA_Const::IPN_TABLE_NAME,
			$data,
			array(
				'%d',
				'%d',
				'%s',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
			)
		);
	}

	protected function get_order_id() {

		if ( empty( $this->order_id ) ) {
			$data           = explode( '|', $this->data->order->tag );
			$this->order_id = TVA_Order::get_order_id_by_number( $data[0] );
		}

		return $this->order_id;
	}
}
