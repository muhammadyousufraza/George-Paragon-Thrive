<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

use TVA\Product;
use TVA\Product_Migration;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class TVA_Order_Item
 */
class TVA_Order_Item {

	/**
	 * The order id
	 *
	 * @var null|int
	 */
	protected $ID = null;

	/**
	 * The order ID
	 *
	 * @var int
	 */
	protected $order_id = 0;

	/**
	 * @var int
	 */
	protected $status = 1;

	/**
	 * The Gateway Order ID
	 *
	 * @var int
	 */
	protected $gateway_order_id = 0;

	/**
	 * The Gateway order item id
	 *
	 * @var int
	 */
	protected $gateway_order_item_id = 0;

	/**
	 * @var string
	 */
	protected $product_id = 0;
	/**
	 * @var string
	 */
	protected $product_type = '';
	/**
	 * @var string
	 */
	protected $product_name = '';
	/**
	 * @var int
	 */
	protected $product_price = 0;
	/**
	 * @var int
	 */
	protected $quantity = 1;
	/**
	 * @var int
	 */
	protected $unit_price = '0';
	/**
	 * @var int
	 */
	protected $total_price = '0';

	/**
	 * @var int
	 */
	protected $currency = '';

	/**
	 * @var string
	 */
	protected $valid_until = '0000-00-00 00:00:00';
	/**
	 * @var string
	 */
	protected $created_at = '0000-00-00 00:00:00';

	/**
	 * Database object
	 *
	 * @var WP_Query|wpdb
	 */
	protected $wpdb;

	/**
	 * @var integer[]
	 */
	protected $courses;

	/**
	 * TVA_Order_Item constructor.
	 *
	 * @param null $ID
	 */
	public function __construct( $ID = null ) {
		global $wpdb;

		$this->wpdb = $wpdb;
		/**
		 * Skip everything else if we don't have any order id
		 */
		if ( ! $ID ) {
			$this->set_created_at( date( 'Y-m-d H:i:s' ) );

			return;
		}

		$this->set_ID( $ID );
		$this->get_data();
	}

	/**
	 * Get the data from the DB
	 */
	protected function get_data() {
		$sql             = 'SELECT * FROM ' . $this->wpdb->prefix . TVA_Const::DB_PREFIX . TVA_Const::ORDER_ITEMS_TABLE_NAME . ' WHERE ID = %d';
		$order_item_data = $this->wpdb->get_row( $this->wpdb->prepare( $sql, [ $this->ID ] ), ARRAY_A );

		if ( ! empty( $order_item_data ) ) {
			$this->set_data( $order_item_data );
		}
	}

	/**
	 * Return an array of all the object's properties, besides WPDB
	 *
	 * @return array
	 */
	public function get_all_object_properties() {
		$vars = get_object_vars( $this );
		unset( $vars['wpdb'] );

		return $vars;
	}

	/**
	 * Set the data
	 *
	 * @param $data
	 */
	public function set_data( $data ) {
		if ( is_object( $data ) ) {
			$data = (array) $data;
		}

		/**
		 * We don't need to map the data here because both the IPN data and DB data either come
		 * with the correct fields or need to be constructed beforehand because it's set in
		 * multiple arrays of data
		 */
		foreach ( $data as $key => $value ) {
			if ( property_exists( $this, $key ) ) {
				$fn = 'set_' . $key;
				$this->$fn( $value );
			}
		}
	}

	/**
	 * Sets instance properties
	 * - un-serialize param if necessary
	 *
	 * @param array|string $course_id serialized
	 */
	public function set_courses( $course_id ) {

		$this->courses = $course_id;
	}

	/**
	 * @return integer[]
	 */
	public function get_courses() {
		return $this->courses;
	}

	/**
	 * Save the data
	 *
	 * @return bool
	 */
	public function save() {
		$order_id = $this->get_order_id();

		if ( empty( $order_id ) ) {
			return false;
		}
		$data            = get_object_vars( $this );
		$data['courses'] = maybe_serialize( $data['courses'] );
		unset( $data['wpdb'] );
		unset( $data['ID'] );

		$types = [
			'%d',
			'%d',
			'%s',
			'%d',
			'%s',
			'%d',
			'%s',
			'%s',
			'%s',
			'%d',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
		];

		if ( ! empty( $this->product_id ) ) {
			/**
			 * We need to invalidate cache on a new purchase
			 */
			TVA_Course_V2::delete_count_enrolled_users_cache( 0 );
			Product::delete_count_users_with_access_cache( $this->product_id );
		}

		if ( ! $this->get_id() ) {

			do_action( 'tva_before_sendowl_insert_order_item', $data, $types, $this );

			$result = $this->wpdb->insert(
				$this->wpdb->prefix . TVA_Const::DB_PREFIX . TVA_Const::ORDER_ITEMS_TABLE_NAME,
				$data,
				$types
			);

			if ( $result ) {
				$this->set_id( $this->wpdb->insert_id );
			}
		} else {

			do_action( 'tva_before_sendowl_update_order_item', $data, $types, $this );

			$result = $this->wpdb->update(
				$this->wpdb->prefix . TVA_Const::DB_PREFIX . TVA_Const::ORDER_ITEMS_TABLE_NAME,
				$data,
				[ 'ID' => $this->get_id() ],
				$types,
				[ '%d' ]
			);
		}

		do_action( 'tva_after_sendowl_order_item_db', $data, $types, $this );

		return $result;
	}

	/**
	 * Delete the data
	 *
	 * @return false|int
	 */
	public function delete() {
		/**
		 * Hook that is fired before the order item has been deleted from the database
		 *
		 * @param TVA_Order_Item $this
		 */
		do_action( 'tva_order_item_deleted', $this );

		return $this->wpdb->delete(
			$this->wpdb->prefix . TVA_Const::DB_PREFIX . TVA_Const::ORDER_ITEMS_TABLE_NAME,
			[ 'ID' => $this->get_id() ],
			[ '%d' ]
		);
	}

	/**
	 * @return int|null
	 */
	public function get_ID() {
		return $this->ID;
	}

	/**
	 * @param int|null $ID
	 */
	public function set_ID( $ID ) {
		$this->ID = (int) $ID;
	}

	/**
	 * @return int
	 */
	public function get_order_id() {
		return $this->order_id;
	}

	/**
	 * @param int $order_id
	 */
	public function set_order_id( $order_id ) {
		$this->order_id = (int) $order_id;
	}

	/**
	 * @return int
	 */
	public function get_status() {
		return (int) $this->status;
	}

	/**
	 * @param $int 0 or 1
	 */
	public function set_status( $int ) {
		$this->status = (int) $int;

		return $this;
	}

	/**
	 * @return int
	 */
	public function get_gateway_order_id() {
		return $this->gateway_order_id;
	}

	/**
	 * @param int $gateway_order_id
	 */
	public function set_gateway_order_id( $gateway_order_id ) {
		$this->gateway_order_id = (int) $gateway_order_id;
	}

	/**
	 * @return int
	 */
	public function get_gateway_order_item_id() {
		return $this->gateway_order_item_id;
	}

	/**
	 * @param int $gateway_order_item_id
	 */
	public function set_gateway_order_item_id( $gateway_order_item_id ) {
		$this->gateway_order_item_id = (int) $gateway_order_item_id;
	}

	/**
	 * @return int|string
	 */
	public function get_product_id() {
		return $this->product_id;
	}

	/**
	 * @param string $product_id
	 */
	public function set_product_id( $product_id ) {

		$this->product_id = Product_Migration::validate_product_id( $product_id );
	}

	/**
	 * @return string
	 */
	public function get_product_type() {
		return $this->product_type;
	}

	/**
	 * @param string $product_type
	 */
	public function set_product_type( $product_type ) {
		$this->product_type = $product_type;
	}

	/**
	 * @return string
	 */
	public function get_product_name() {
		return $this->product_name;
	}

	/**
	 * @param string $product_name
	 */
	public function set_product_name( $product_name ) {
		$this->product_name = $product_name;
	}

	/**
	 * @return int
	 */
	public function get_product_price() {
		return $this->product_price;
	}

	/**
	 * @param int $product_price
	 */
	public function set_product_price( $product_price ) {
		$this->product_price = $product_price;
	}

	/**
	 * @return int
	 */
	public function get_quantity() {
		return $this->quantity;
	}

	/**
	 * @param int $quantity
	 */
	public function set_quantity( $quantity ) {
		$this->quantity = (int) $quantity;
	}

	/**
	 * @return int
	 */
	public function get_unit_price() {
		return $this->unit_price;
	}

	/**
	 * @param int $unit_price
	 */
	public function set_unit_price( $unit_price ) {
		$this->unit_price = $unit_price;
	}

	/**
	 * @return int
	 */
	public function get_total_price() {
		return $this->total_price;
	}

	/**
	 * @param int $total_price
	 */
	public function set_total_price( $total_price ) {
		$this->total_price = $total_price;
	}

	/**
	 * @return int
	 */
	public function get_currency() {
		return $this->currency;
	}

	/**
	 * @param int $currency
	 */
	public function set_currency( $currency ) {
		$this->currency = $currency;
	}

	/**
	 * @return string
	 */
	public function get_valid_until() {
		return $this->valid_until;
	}

	/**
	 * @param string $valid_until
	 */
	public function set_valid_until( $valid_until ) {
		$this->valid_until = $valid_until;
	}

	/**
	 * @return string
	 */
	public function get_created_at() {
		return $this->created_at;
	}

	/**
	 * @param string $created_at
	 */
	public function set_created_at( $created_at ) {
		$this->created_at = $created_at;
	}

	public function __debugInfo() {

		return $this->get_all_object_properties();
	}

	public static function get_table_name() {

		global $wpdb;

		return $wpdb->prefix . TVA_Const::DB_PREFIX . TVA_Const::ORDER_ITEMS_TABLE_NAME;
	}

	/**
	 * Fetches order items which have been bought
	 * - order status is bought
	 *
	 * @param array $filters accepts:
	 *                       - integer [user_id]
	 *                       - string [gateway]
	 *                       - array [gateway]
	 *
	 * @return array
	 */
	public static function get_purchased_items( $filters ) {

		global $wpdb;

		$items_table  = $wpdb->prefix . TVA_Const::DB_PREFIX . TVA_Const::ORDER_ITEMS_TABLE_NAME;
		$orders_table = $wpdb->prefix . TVA_Const::DB_PREFIX . TVA_Const::ORDERS_TABLE_NAME;

		$select     = 'SELECT items.product_id FROM ' . $items_table . ' AS items ';
		$inner_join = ' INNER JOIN ' . $orders_table . ' orders ON items.order_id = orders.ID ';

		//status where
		$where        = ' WHERE orders.status = %d and items.status = %d';
		$placeholders = [ 1, 1 ];

		//add user where
		if ( ! empty( $filters['user_id'] ) ) {
			$where          .= ' AND orders.user_id = %d';
			$placeholders[] = (int) $filters['user_id'];
		}

		//add gateway where
		if ( ! empty( $filters['gateway'] ) ) {
			$gateways = ! is_array( $filters['gateway'] ) ? [ $filters['gateway'] ] : $filters['gateway'];
			$where    .= ' AND orders.gateway IN (';
			$temp     = [];
			foreach ( $gateways as $gateway ) {
				$temp[]         = '%s';
				$placeholders[] = $gateway;
			}
			$where .= implode( ',', $temp ) . ') ';
		}

		$group = ' GROUP BY product_id ';

		$sql = $select . $inner_join . $where . $group;

		return $wpdb->get_col( $wpdb->prepare( $sql, $placeholders ) );
	}

	/**
	 * Based on access type calculates a slug string
	 *
	 * @param $access_type string
	 *
	 * @return string
	 */
	public function get_access_type_slug( $access_type ) {
		return strtolower( str_replace( ' ', '-', $access_type ) );
	}

	/**
	 * Calculates a string based on instance and order props
	 * PAID orders
	 * - SendOwl product - paid order from sendowl
	 * - SendOwl bundle - paid order from sendowl
	 * - Apprentice bundle - paid order usually coming from ThriveCart
	 * - WooCommerce Simple Product - paid order with woocommerce gateway with simple product item
	 * - WooCommerce Subscription Product - paid order with woocommerce gateway with subscription product item
	 * - WooCommerce Variable Subscription Product - paid order with woocommerce gateway with variable subscription product item
	 * - {GATEWAY} product - paid order with variable GATEWAY
	 * IMPORTED/MANUALLY ADDED and linked to SendOwl
	 * - Sendowl product - imported or manually added and assigned a sendowl product
	 * DIRECT ASSIGNMENT
	 * - Apprentice bundle - for users which was assigned a apprentice bundle and order is not paid
	 * - Apprentice course - for users which was assigned a course and order is not paid
	 *
	 * @param $order TVA_Order
	 *
	 * @return string
	 */
	public function get_access_type( $order, $sendowl_product_ids = [], $wc_product = null ) {

		if ( $this->is_course_bundle() ) {
			return 'Apprentice bundle';
		}

		$type = '';

		if ( false === $order instanceof TVA_Order ) {
			return $type;
		}

		if ( $order->is_paid() ) {

			if ( $order->is_sendowl() ) {
				$type = sprintf(
					$order->get_gateway() . ' %s',
					in_array( $this->get_product_id(), $sendowl_product_ids ) ? 'product' : 'bundle'
				);
			} elseif ( $order->is_woocommerce() ) {

				$type = $order->get_gateway();

				if ( $wc_product ) {
					$chunks = explode( '-', $wc_product->get_type() );
					$chunks = array_map( 'ucfirst', $chunks );

					$wc_product ? $type .= ' ' . implode( ' ', $chunks ) . ' Product' : null;
				} else {
					$type .= ' product';
				}
			} else {
				$type = sprintf(
				/* translators: %s */
					esc_html__( '%s product', 'thrive-apprentice' ),
					$order->get_gateway()
				);
			}
		} elseif ( $order->is_manual() && $order->is_sendowl() ) {
			/* translators: %s */
			$type = sprintf( esc_html__( '%s product', 'thrive-apprentice' ), $order->get_gateway() );
		} elseif ( $order->is_manual() ) {
			$type = 'Apprentice product';
		}

		return $type;
	}

	/**
	 * Checks if product_id contains `course.bundle` string
	 *
	 * @return bool
	 */
	public function is_course_bundle() {
		return strpos( $this->get_product_id(), 'course.bundle' ) !== false;
	}
}
