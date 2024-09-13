<?php

class TVA_Woocommerce_Order {

	/**
	 * @var WC_Order
	 */
	protected $woocommerce_order;

	/**
	 * TVA_Woocommerce_Order constructor.
	 *
	 * @param WC_Order $woocommerce_order
	 */
	public function __construct( $woocommerce_order ) {
		$this->woocommerce_order = $woocommerce_order;
	}

	/**
	 * Checks if any of the WC_Product(s) has TA courses or bundles assigned
	 * - returns tva item ids
	 *
	 * @param WC_Product[]
	 *
	 * @return array
	 */
	public function has_tva_items( $wc_products = array() ) {

		$products = array();

		/** @var TVA_Woocommerce_Integration $woocommerce_integration */
		$woocommerce_integration = tva_integration_manager()->get_integration( 'woocommerce' );

		/** @var WC_Product $wc_product */
		foreach ( $wc_products as $wc_product ) {

			if ( 'WC_Product_Subscription_Variation' === get_class( $wc_product ) ) {
				$wc_product = wc_get_product( $wc_product->get_parent_id() );
			}

			$products = array_merge( $products, $woocommerce_integration->get_assigned_products( $wc_product->get_id() ) );
		}

		return array(
			'products' => array_unique( array_map( 'intval', $products ) ),
		);
	}

	/**
	 * Checks if the WooCommerce order has products of specific types
	 *
	 * @param array $types
	 *
	 * @return false|WC_Product[]
	 */
	public function has_product_by_types( $types ) {

		$products = array();

		/** @var WC_Order_Item_Product $order_item */
		foreach ( $this->woocommerce_order->get_items() as $order_item ) {
			$wc_product = $order_item->get_product();
			if ( 'WC_Product_Subscription_Variation' === get_class( $wc_product ) ) {
				$wc_product = wc_get_product( $wc_product->get_parent_id() );
			}
			$class = get_class( $wc_product );
			if ( in_array( $class, $types, true ) ) {
				$products[] = $wc_product;
			}
		}

		return empty( $products ) ? false : $products;
	}

	/**
	 * @return TVA_Order
	 */
	public function get_tva_order() {

		$tva_order = TVA_Order::get_order(
			array(
				'gateway_order_id' => $this->woocommerce_order->get_id(),
				'gateway'          => TVA_Const::WOOCOMMERCE_GATEWAY,
			)
		);

		$tva_order->set_user_id( $this->woocommerce_order->get_customer_id() );
		$tva_order->set_gateway_order_id( $this->woocommerce_order->get_id() );
		$tva_order->set_gateway( TVA_Const::WOOCOMMERCE_GATEWAY );
		$tva_order->set_type( TVA_Order::PAID );
		$tva_order->set_price_gross( $this->woocommerce_order->get_total() );

		return $tva_order;
	}

	/**
	 * Ensures that all apprentice products are saved as TVA_Order_Item on TVA_Order
	 * - does not remove extra order items
	 *
	 * @param TVA_Order      $tva_order
	 * @param \TVA\Product[] $tva_products
	 * @param WC_Product     $wc_product
	 */
	public function ensure_products_items( $tva_order, $tva_products, $wc_product ) {

		foreach ( $tva_products as $tva_product ) {
			$tva_order_item = $tva_order->get_order_item_by_product_id( (int) $tva_product->get_id() );
			if ( false === $tva_order_item instanceof TVA_Order_Item ) {

				$tva_order_item = new TVA_Order_Item();
				$tva_order_item->set_product_id( $tva_product->get_id() );
				$tva_order_item->set_order_id( $tva_order->get_id() );
				$tva_order_item->set_gateway_order_id( $tva_order->get_gateway_order_id() );
				$tva_order_item->set_product_name( $tva_product->get_name() );
				$tva_order_item->set_gateway_order_item_id( $wc_product->get_id() );
				$tva_order_item->set_product_price( $wc_product->get_price() );

				$tva_order->set_order_item( $tva_order_item );
			}
		}
	}

	/**
	 * Updates the TVA_Order status accordingly with WC_Order status
	 *
	 * @param TVA_Order $tva_order
	 */
	public function ensure_status( $tva_order ) {

		$status            = TVA_Const::STATUS_PENDING;
		$completed_statues = array(
			'completed',
		);

		if ( true === in_array( $this->woocommerce_order->get_status(), $completed_statues, true ) ) {
			$status = TVA_Const::STATUS_COMPLETED;
		}

		$tva_order->set_status( $status );
	}
}
