<?php

/**
 * Class TVA_Woocommerce_Integration
 *
 * @project: thrive-apprentice
 */
class TVA_Woocommerce_Integration extends TVA_ThriveCart_Integration {

	/**
	 * List of WC Product types for which this integration is applied
	 *
	 * @var string[]
	 */
	protected $allowed_products_types = array(
		'WC_Product_Simple',
		'simple',
		'WC_Product_Subscription',
		'subscription',
		'WC_Product_Subscription_Variation',
		'subscription_variation',
		'WC_Product_Variable_Subscription',
		'variable-subscription',
	);

	public function before_init_items() {

		add_filter(
			'woocommerce_product_data_tabs',
			static function ( $tabs ) {

				$tabs['tva-courses'] = array(
					'label'    => __( 'Thrive Apprentice', 'thrive-apprentice' ),
					'target'   => 'tva-courses',
					'class'    => array(
						'show_if_simple',
						'show_if_subscription',
					),
					'priority' => 11,
				);

				return $tabs;
			}
		);

		add_action(
			'woocommerce_product_data_panels',
			static function () {
				include dirname( __DIR__ ) . '/../../templates/woocommerce/html-product-data-courses.phtml';
			}
		);

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		add_action( 'woocommerce_after_product_object_save', array( $this, 'save_product' ) );

		add_action( 'woocommerce_order_status_changed', array( $this, 'process_order' ), 11, 4 );

		add_action( 'woocommerce_order_status_changed', array( $this, 'skip_processing_status' ), 10, 4 );

		add_action( 'woocommerce_subscription_status_updated', array( $this, 'subscription_status_changed' ), 10, 3 );
	}

	/**
	 * If an in progress order has all products linked to TA products
	 * then set the order status to completed
	 *
	 * @param int      $order_id
	 * @param string   $from_status
	 * @param string   $to_status
	 * @param WC_Order $wc_order
	 */
	public function skip_processing_status( $order_id, $from_status, $to_status, $wc_order ) {

		if ( 'processing' !== $to_status ) {
			return;
		}

		$tva_wc_order     = new TVA_Woocommerce_Order( $wc_order );
		$allowed_products = $tva_wc_order->has_product_by_types( $this->allowed_products_types );

		if ( empty( $allowed_products ) ) {
			return;
		}

		list( $has_order_products_linked ) = array_values( $tva_wc_order->has_tva_items( $allowed_products ) );

		$has_tva_items = ! empty( $has_order_products_linked );

		if ( $has_tva_items && count( $allowed_products ) === $wc_order->get_item_count() ) {
			$wc_order->update_status( 'completed', 'By Thrive Apprentice' );
		}
	}

	/**
	 * @param WC_Subscription $subscription
	 * @param string          $status_to
	 * @param string          $status_from
	 */
	public function subscription_status_changed( $subscription, $status_to, $status_from ) {

		$related_subscription_orders = $subscription->get_related_orders( 'all' );
		if ( empty( $related_subscription_orders ) ) {
			return;
		}

		foreach ( $related_subscription_orders as $wc_order ) {
			//no wc order could be found for this manually added subscription
			if ( false === $wc_order instanceof WC_Order ) {
				return;
			}

			$tva_wc_order = new TVA_Woocommerce_Order( $wc_order );
			$tva_order    = $tva_wc_order->get_tva_order();

			$allowance_status = array(
				'active',
				'pending-cancel',
				'cancelled',
				'on-hold',
				'expired',
			);

			if ( $tva_order->get_id() && true === in_array( $status_to, $allowance_status, true ) ) {
				$tva_new_status = in_array( $status_to, array( 'cancelled', 'on-hold', 'expired' ) ) ? TVA_Const::STATUS_PENDING : TVA_Const::STATUS_COMPLETED;
				$tva_order->set_status( $tva_new_status );
				$tva_order->save();
				TVA_Logger::set_type( 'WooCommerce Subscription' );
				TVA_Logger::log(
					"WooCommerce subscription #{$subscription->get_id()} with order #{$wc_order->get_id()} status changed from {$status_from} to {$status_to}",
					array(
						'order_id' => $tva_order->get_id(),
					),
					true
				);
			}
		}
	}

	/**
	 * Hook after a WC_Order status is changed
	 * - look into each order item for tva items
	 * - generates a tva order with proper order items from items assigned on each wc product
	 * - ensure status
	 *
	 * @param int      $order_id
	 * @param string   $status_from
	 * @param string   $status_to
	 * @param WC_Order $wc_order
	 */
	public function process_order( $order_id, $status_from, $status_to, $wc_order ) {

		/**
		 * - check if the order exists in TVA tables
		 * - if yes, then update the status
		 * - if no, generate a new order
		 */
		$tva_wc_order     = new TVA_Woocommerce_Order( $wc_order );
		$allowed_products = $tva_wc_order->has_product_by_types( $this->allowed_products_types );

		/**
		 * The order doesn't contain any product TA is interested with
		 */
		if ( empty( $allowed_products ) ) {
			return;
		}

		list( $all_order_products ) = array_values( $tva_wc_order->has_tva_items( $allowed_products ) );

		/**
		 * None of the interested products have assigned any TA products to give access to
		 */
		if ( empty( $all_order_products ) ) {
			return;
		}

		$tva_order        = $tva_wc_order->get_tva_order();
		$enrolled_courses = array();

		/** @var WC_Product $wc_product */
		foreach ( $allowed_products as $wc_product ) {

			$app_product_id = $this->get_assigned_products( $wc_product->get_id() );

			if ( ! empty( $app_product_id ) ) {
				$enrolled_courses = array_merge( $enrolled_courses, $app_product_id );
				$app_products     = \TVA\Product::get_items(
					array(
						'include' => $app_product_id,
					)
				);
				$tva_wc_order->ensure_products_items( $tva_order, $app_products, $wc_product );
			}
		}

		$tva_wc_order->ensure_status( $tva_order );

		$tva_order->save();

		$customer = new TVA_Customer( $tva_order->get_user_id() );

		if ( ! empty( $enrolled_courses ) ) {//order has courses
			$customer->trigger_course_purchase( $tva_order, 'WooCommerce' );
		}

		if ( ! empty( $all_order_products ) ) {
			$customer->trigger_product_received_access( $all_order_products );
		}

		if ( $status_to === 'completed' ) {
			$customer->trigger_purchase( $tva_order );
		}

		TVA_Logger::set_type( 'WooCommerce Order' );
		TVA_Logger::log(
			"WooCommerce order #{$wc_order->get_id()} status changed from {$status_from} to {$status_to}",
			array(
				'order_id'          => $tva_order->get_id(),
				'wc_products'       => array_map(
					static function ( $item ) {
						return $item->get_id();
					},
					$allowed_products
				),
				'wc_order_products' => $all_order_products,
			),
			true
		);
	}

	/**
	 * When WC Product is saved
	 * - assign TA items(products) on it
	 * - mind that this callback might be executed from frontend for products with stock quantity,
	 *   issue caught for SUPP-12575 which reset TA products for a Woo product
	 *
	 * @param WC_Product $product
	 */
	public function save_product( $product ) {

		if ( false === in_array( $product->get_type(), $this->allowed_products_types, true ) ) {
			return;
		}


		/**
		 * We need to make sure this applies only to admin requests
		 * save_product can occur also from front-end when product has a stock and the order is placed.
		 * save_product can happen from quick edit screen -> If the products are saved from quick edit screen the Apprentice data won't be sent
		 */
		if ( is_admin() && ! wp_doing_ajax() ) {
			if ( isset( $_POST['tva_products'] ) ) {
				$apprentice_products_ids = (array) $_POST['tva_products'];
				$apprentice_products_ids = array_map( 'intval', $apprentice_products_ids );
				update_post_meta( $product->get_id(), 'tva_products', $apprentice_products_ids );
			} else {
				delete_post_meta( $product->get_id(), 'tva_products' );
			}
		}
	}

	/**
	 * If WC activated then enqueues scripts and styles
	 */
	public function admin_scripts() {

		if ( false === $this->_is_plugin_activated() ) {
			return;
		}

		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		if ( in_array( $screen_id, array( 'product' ), true ) ) {

			wp_enqueue_style( 'tva-woocommerce-styles', TVA_Const::plugin_url( 'admin/includes/dist/tva-woocommerce-styles.css' ) );

			$apprentice_js_file      = defined( 'TVE_DEBUG' ) && TVE_DEBUG ? 'tva-woocommerce.js' : 'tva-woocommerce.min.js';
			$apprentice_js_file_deps = array(
				'jquery',
				'backbone',
			);

			wp_enqueue_script( 'tva-admin-woocommerce', TVA_Const::plugin_url( 'admin/includes/dist/' . $apprentice_js_file ), $apprentice_js_file_deps, TVA_Const::PLUGIN_VERSION, true );

			wp_localize_script(
				'tva-admin-woocommerce',
				'TVA_WC',
				apply_filters(
					'tva_woocommerce_localize',
					array(
						'products'          => \TVA\Product::get_light_items(),
						'assigned_products' => $this->get_assigned_products( wc_get_product()->get_id() ),
						't'                 => array(
							'no_item_found' => esc_html__( 'No item found', 'thrive-apprentice' ),
						),
					)
				)
			);
		}
	}


	/**
	 *  For a WC Product fetches the meta from DB
	 *
	 * @param $wc_product_id
	 *
	 * @return array|mixed
	 */
	public function get_assigned_products( $wc_product_id ) {
		$ids = get_post_meta( $wc_product_id, 'tva_products', true );

		return empty( $ids ) ? array() : $ids;
	}

	/**
	 * For a WC Product fetches the meta from DB
	 *
	 * @param int $wc_product_id
	 *
	 * @return int[]
	 * @deprecated
	 */
	public function get_assigned_courses( $wc_product_id ) {

		$ids = get_post_meta( $wc_product_id, 'tva_courses', true );

		return empty( $ids ) ? array() : $ids;
	}

	protected function init_items() {
	}

	protected function _get_item_from_membership( $key, $value ) {
	}

	public function get_customer_access_items( $customer ) {
	}

	/**
	 * Checks if Woocommerce plugin is installed and activated
	 *
	 * @return bool
	 */
	protected function _is_plugin_activated() {

		$plugin_slug = 'woocommerce/woocommerce.php';

		return is_plugin_active( $plugin_slug );
	}

	public function allow() {
		return $this->_is_plugin_activated();
	}
}
