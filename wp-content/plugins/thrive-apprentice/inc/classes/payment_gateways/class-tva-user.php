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
 * Class TVA_User
 */
class TVA_User {
	protected $ID = null;

	/**
	 * @var null
	 */
	protected $user_email = null;

	/**
	 * @var null
	 */
	protected $user_url = null;

	/**
	 * @var null
	 */
	protected $user_registered = null;

	/**
	 * @var null
	 */
	protected $display_name = null;

	/**
	 * @var array
	 */
	protected $orders = array();

	/**
	 * Database object
	 *
	 * @var WP_Query|wpdb
	 */
	protected $wpdb;

	/**
	 * TVA_User constructor.
	 *
	 * @param null $id
	 */
	public function __construct( $id = null ) {

		global $wpdb;

		$this->wpdb = $wpdb;
		/**
		 * Skip everything else if we don't have any order id
		 */
		if ( ! $id ) {
			return;
		}

		$this->set_ID( $id );
		$this->get_data();
	}

	/**
	 * Get the data
	 */
	protected function get_data() {
		$user_data = get_userdata( $this->get_ID() );

		if ( ! empty( $user_data ) ) {
			$this->set_data( $user_data->data );
		}
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

		foreach ( $data as $key => $value ) {

			if ( property_exists( $this, $key ) ) {
				$fn = 'set_' . $key;
				$this->$fn( $value );
			}
		}

		$this->set_orders();
	}

	/**
	 * Add an Order
	 *
	 * @param TVA_Order_Item $item
	 */
	public function set_order( TVA_Order $item ) {
		$this->orders[] = $item;
	}

	protected function set_orders() {
		if ( $this->ID ) {
			$sql    = 'SELECT * FROM ' . $this->wpdb->prefix . TVA_Const::DB_PREFIX . TVA_Const::ORDERS_TABLE_NAME . ' WHERE user_id = %d';
			$orders = $this->wpdb->get_results( $this->wpdb->prepare( $sql, array( $this->ID ) ), ARRAY_A );

			if ( ! empty( $orders ) ) {
				foreach ( $orders as $order ) {
					$has_item = false;
					foreach ( $this->get_orders() as $set_order ) {
						if ( $set_order->get_id() == $order['ID'] ) {
							$has_item = true;
						}
					}
					if ( ! $has_item ) {
						$item = new TVA_Order( $order['ID'] );
						$item->set_data( $order );
						$this->set_order( $item );
					}
				}
			}
		}
	}

	/**
	 * Return all orders
	 *
	 * @return array
	 */
	public function get_orders() {
		return $this->orders;
	}

	/**
	 * Return all orders by status
	 *
	 * @param $status
	 *
	 * @return array
	 */
	public function get_orders_by_status( $status ) {
		$orders = array();
		foreach ( $this->orders as $order ) {
			/** @var TVA_Order $order */
			if ( $order->get_status() === $status ) {
				$orders[] = $order;
			}
		}

		return $orders;
	}

	/**
	 * @return TVA_Order[]
	 */
	public function get_manual_orders() {

		$orders = array();

		foreach ( $this->orders as $order ) {
			/** @var TVA_Order $order */
			if ( $order->is_manual() ) {
				$orders[] = $order;
			}
		}

		return $orders;
	}

	/**
	 * @return mixed
	 */
	public function get_ID() {
		return $this->ID;
	}

	/**
	 * @param mixed $ID
	 */
	public function set_ID( $ID ) {
		$this->ID = $ID;
	}

	/**
	 * @return null
	 */
	public function get_user_email() {
		return $this->user_email;
	}

	/**
	 * @param null $user_email
	 */
	public function set_user_email( $user_email ) {
		$this->user_email = $user_email;
	}

	/**
	 * @return null
	 */
	public function get_user_url() {
		return $this->user_url;
	}

	/**
	 * @param null $user_url
	 */
	public function set_user_url( $user_url ) {
		$this->user_url = $user_url;
	}

	/**
	 * @return null
	 */
	public function get_user_registered() {
		return $this->user_registered;
	}

	/**
	 * @param null $user_registered
	 */
	public function set_user_registered( $user_registered ) {
		$this->user_registered = $user_registered;
	}

	/**
	 * @return null
	 */
	public function get_display_name() {
		return $this->display_name;
	}

	/**
	 * @param null $display_name
	 */
	public function set_display_name( $display_name ) {
		$this->display_name = $display_name;
	}

	/**
	 * Count Sendowl customers
	 *
	 * @param array $args
	 *
	 * @return int
	 */
	public static function count_sendowl_customers( $args = array() ) {
		global $wpdb;

		$placeholders = array();

		//table names
		$tbl_orders = $wpdb->prefix . TVA_Const::DB_PREFIX . TVA_Const::ORDERS_TABLE_NAME . ' AS orders ';
		$tbl_users  = $wpdb->base_prefix . 'users AS users ';
		$tbl_items  = $wpdb->prefix . TVA_Const::DB_PREFIX . TVA_Const::ORDER_ITEMS_TABLE_NAME . ' AS items ';

		$left_join = '';
		$group     = '';

		$select     = 'SELECT COUNT( DISTINCT users.ID ) as count ';
		$from       = 'FROM ' . $tbl_users;
		$inner_join = 'INNER JOIN ' . $tbl_orders . ' ON users.ID = orders.user_id ';

		$where          = ' WHERE orders.`status` = %d';
		$placeholders[] = TVA_Const::STATUS_COMPLETED;

		if ( isset( $args['product_id'] ) ) {
			$left_join      = 'LEFT JOIN ' . $tbl_items . ' ON orders.ID = items.order_id ';
			$where          .= ' AND items.product_id = %d ';
			$placeholders[] = (int) $args['product_id'];
		}

		if ( isset( $args['s'] ) && strlen( $args['s'] ) > 0 ) {
			$where .= " AND ( users.display_name LIKE '%%%s%%' OR users.user_email LIKE '%%%s%%' ) ";

			$placeholders[] = $args['s'];
			$placeholders[] = $args['s'];
		}

		$sql    = $select . $from . $inner_join . $left_join . $where . $group;
		$sql    = $wpdb->prepare( $sql, $placeholders );
		$result = $wpdb->get_results( $sql );

		return isset( $result[0] ) ? $result[0]->count : 0;
	}

	/**
	 * @param array  $fields array( array( 'name' => 'user_email', 'values' => $user_emails)
	 * @param string $relation
	 *
	 * @return array
	 */
	public static function get_users_by_fields( $fields = array(), $relation = 'OR' ) {
		global $wpdb;

		$fields = self::validate_user_fields( $fields );

		if ( empty( $fields ) ) {
			return array();
		}

		$select = 'SELECT * FROM ' . $wpdb->base_prefix . 'users AS users ';
		$where  = 'WHERE 1=1 AND ';

		$placeholders = array();

		foreach ( $fields as $key => $field ) {
			if ( $key > 0 ) {
				$where .= ' ' . $relation . ' ( ';
			}

			$where .= ' users.' . $field['name'] . ' IN (';

			foreach ( $field['values'] as $_key => $value ) {
				$where          .= '%s';
				$placeholders[] = $value;

				if ( $_key + 1 < count( $field['values'] ) ) {
					$where .= ', ';
				}
			}

			$where .= ')';

			if ( $key > 0 ) {
				$where .= ' ) ';
			}
		}

		$sql    = $wpdb->prepare( $select . $where, $placeholders );
		$result = $wpdb->get_results( $sql, ARRAY_A );

		return $result;
	}

	/**
	 * @param $fields
	 *
	 * @return array|bool
	 */
	public static function validate_user_fields( $fields ) {
		if ( ! is_array( $fields ) ) {
			return false;
		}

		// WP_User fields
		$allowed_fields = array( 'ID', 'user_email', 'user_login', 'user_nicename', 'user_url' );

		foreach ( $fields as $key => $field ) {

			if ( ! is_array( $field ) || ! isset( $field['name'] ) ) {
				unset( $field[ $key ] );
				continue;
			}

			if ( ! in_array( $field['name'], $allowed_fields ) ) {
				unset( $fields[ $key ] );
				continue;
			}

			if ( ! isset( $field['values'] ) || ! is_array( $field['values'] ) || empty( $field['values'] ) ) {
				unset( $fields[ $key ] );
			}
		}

		return $fields;
	}

	/**
	 * Checks if current user has bought a product with an ID
	 *
	 * @param $product_id
	 *
	 * @return false|TVA_Order
	 */
	public function has_bought( $product_id ) {

		$has_bought = false;

		if ( ! $product_id ) {
			return $has_bought;
		}

		/** @var TVA_Order $order */
		foreach ( $this->get_orders_by_status( TVA_Const::STATUS_COMPLETED ) as $order ) {
			if ( true === $order instanceof TVA_Order
				 && true === ( $order_item = $order->get_order_item_by_product_id_and_status( $product_id, 1 ) ) instanceof TVA_Order_Item
			) {
				return $order;
			}
		}

		return $has_bought;
	}
}
