<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

use TVA\Access\History_Table;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class TVA_Order
 */
class TVA_Order {

	const PAID     = 'paid';
	const MANUAL   = 'manual';
	const IMPORTED = 'imported';

	/**
	 * The order id
	 *
	 * @var null|int
	 */
	protected $ID = null;

	/**
	 * @var string unique string identifier
	 */
	protected $number;

	/**
	 * The user ID
	 *
	 * @var int
	 */
	protected $user_id = 0;

	/**
	 * The status of the order, should be a
	 * version of the constants in this class
	 *
	 * @var int
	 */
	protected $status = 0;

	/**
	 * Payment ID
	 *
	 * @var int
	 */
	protected $payment_id = 0;

	/**
	 * Payment Order ID
	 *
	 * @var int
	 */
	protected $gateway_order_id = 0;

	/**
	 * Gateway name
	 *
	 * @var string
	 */
	protected $gateway = '';

	/**
	 * The payment method
	 *
	 * @var string
	 */
	protected $payment_method = '';

	/**
	 * The fee from the gateway
	 *
	 * @var int
	 */
	protected $gateway_fee = 0;

	/**
	 * The email of the buyer
	 *
	 * @var string
	 */
	protected $buyer_email = '';

	/**
	 * The buyer's name
	 *
	 * @var string
	 */
	protected $buyer_name = '';

	/**
	 * The Buyer's address1
	 *
	 * @var string
	 */
	protected $buyer_address1 = '';

	/**
	 * The Buyer's address2
	 *
	 * @var string
	 */
	protected $buyer_address2 = '';

	/**
	 * The Buyer's city
	 *
	 * @var string
	 */
	protected $buyer_city = '';

	/**
	 * The Buyer's region
	 *
	 * @var string
	 */
	protected $buyer_region = '';

	/**
	 * The Buyer's postcode
	 *
	 * @var string
	 */
	protected $buyer_postcode = '';

	/**
	 * The Buyer's country code
	 *
	 * @var string
	 */
	protected $buyer_country = '';

	/**
	 * The billing address1
	 *
	 * @var string
	 */
	protected $billing_address1 = '';

	/**
	 * The billing address2
	 *
	 * @var string
	 */
	protected $billing_address2 = '';

	/**
	 * The billing city
	 *
	 * @var string
	 */
	protected $billing_city = '';

	/**
	 * The billing region
	 *
	 * @var string
	 */
	protected $billing_region = '';

	/**
	 * The billing postcode
	 *
	 * @var string
	 */
	protected $billing_postcode = '';

	/**
	 * The billing country code
	 *
	 * @var string
	 */
	protected $billing_country = '';

	/**
	 * The shipping address1
	 *
	 * @var string
	 */
	protected $shipping_address1 = '';

	/**
	 * Shipping address2
	 *
	 * @var string
	 */
	protected $shipping_address2 = '';

	/**
	 * @var string
	 */
	protected $shipping_city = '';

	/**
	 * Shipping region
	 *
	 * @var string
	 */
	protected $shipping_region = '';

	/**
	 * Shipping post code
	 *
	 * @var string
	 */
	protected $shipping_postcode = '';

	/**
	 * Shipping country
	 *
	 * @var string
	 */
	protected $shipping_country = '';

	/**
	 * The IP address of the buyer
	 *
	 * @var string
	 */
	protected $buyer_ip_address = '';

	/**
	 * Order is free or not
	 *
	 * @var int
	 */
	protected $is_gift = 0;

	/**
	 * The net price of the order
	 *
	 * @var int
	 */
	protected $price = 0;

	/**
	 * The Gross Price of the order
	 *
	 * @var int
	 */
	protected $price_gross = 0;

	/**
	 * The currency of the order
	 *
	 * @var string
	 */
	protected $currency = '';

	/**
	 * When was the order first created
	 *
	 * @var string
	 */
	protected $created_at = '0000-00-00 00:00:00';

	/**
	 * When was the order last updated
	 *
	 * @var string
	 */
	protected $updated_at = '0000-00-00 00:00:00';

	/**
	 * @var string
	 */
	protected $type;

	/**
	 * All the items attached to this order
	 *
	 * @var TVA_Order_Item[] $order_items
	 */
	protected $order_items = array();

	/**
	 * Database object
	 *
	 * @var WP_Query|wpdb
	 */
	protected $wpdb;

	/**
	 * TVA_Order constructor.
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
			$this->set_created_at( current_datetime()->format( 'Y-m-d H:i:s' ) );

			return;
		}

		$this->set_ID( $ID );
		$this->get_data();
	}

	/**
	 * Gets the DB table name for orders
	 *
	 * @return string
	 */
	public static function get_table_name() {

		global $wpdb;

		return $wpdb->prefix . TVA_Const::DB_PREFIX . TVA_Const::ORDERS_TABLE_NAME;
	}

	/**
	 * Get the data from the DB and populates instance properties
	 */
	protected function get_data() {

		$sql        = 'SELECT * FROM ' . self::get_table_name() . ' WHERE ID = %d';
		$order_data = $this->wpdb->get_row( $this->wpdb->prepare( $sql, array( $this->ID ) ), ARRAY_A );

		if ( ! empty( $order_data ) ) {
			$this->set_data( $order_data );
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

		$data = $this->prepare_data( $data );

		foreach ( $data as $key => $value ) {

			if ( property_exists( $this, $key ) ) {
				$fn = 'set_' . $key;
				$this->$fn( $value );
			}
		}

		$this->set_order_items();
	}

	/**
	 * Map and prepare the data for setting in the object
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	protected function prepare_data( $data ) {

		foreach ( $data as $key => $value ) {
			/**
			 * Try to map the data
			 */
			if ( $key == 'id' ) {
				$data['payment_id'] = $value;
			}

			if ( $key == 'settled_currency' ) {
				$data['currency'] = $value;
			}

			if ( $key == 'price_at_checkout' ) {
				$data['price'] = $value;
			}

			if ( $key == 'settled_gateway_fee' ) {
				$data['gateway_fee'] = $value;
			}

			if ( $key == 'settled_gross' ) {
				$data['price_gross'] = $value;
			}

			if ( $key == 'sendowl_order_id' ) {
				$data['gateway_order_id'] = $value;
			}

			if ( $key == 'cart' ) {
				$data['updated_at'] = date( 'Y-m-d H:i:s', strtotime( $value->completed_checkout_at ) );
			}

			/**
			 * check if it's a gift
			 */
			if ( ! isset( $data['is_gift'] ) ) {
				$data['is_gift'] = empty( $data['gift_order'] ) ? 0 : 1;
			}
		}

		return $data;
	}

	/**
	 * Return all order items
	 *
	 * @return TVA_Order_Item[]
	 */
	public function get_order_items() {
		return $this->order_items;
	}

	/**
	 * Returns an order item by product ID and status
	 *
	 * @param int $product_id
	 * @param int $status
	 *
	 * @return TVA_Order_Item|false
	 */
	public function get_order_item_by_product_id_and_status( $product_id, $status = 1 ) {
		foreach ( $this->order_items as $order_item ) {
			/** @var TVA_Order_Item $order_item */
			if ( (string) $order_item->get_product_id() === (string) $product_id && (int) $order_item->get_status() === (int) $status ) {
				return $order_item;
			}
		}

		return false;
	}

	/**
	 * Return Order item by ID
	 *
	 * @param $product_id
	 *
	 * @return false|TVA_Order_Item
	 */
	public function get_order_item_by_product_id( $product_id ) {
		foreach ( $this->order_items as $order_item ) {
			/** @var TVA_Order_Item $order_item */
			if ( (string) $order_item->get_product_id() === (string) $product_id ) {
				return $order_item;
			}
		}

		return false;
	}

	/**
	 * Add an Order Item
	 *
	 * @param TVA_Order_Item $item
	 */
	public function set_order_item( TVA_Order_Item $item ) {
		$this->order_items[] = $item;
	}

	/**
	 * Remove an Order Item
	 *
	 * @param TVA_Order_Item $item
	 */
	public function unset_order_item( TVA_Order_Item $item ) {
		foreach ( $this->order_items as $key => $order_item ) {
			/** @var TVA_Order_Item $order_item */
			if ( $order_item->get_product_id() == $item->get_product_id() ) {
				unset( $this->order_items[ $key ] );
			}
		}
	}

	/**
	 * Add multiple Order items from data
	 *
	 * @param $order_items
	 */
	public function set_order_items_from_data( $order_items ) {
		foreach ( $order_items as $item ) {
			/**
			 * Make sure we have an order item instance
			 */
			if ( ! $item instanceof TVA_Order_Item ) {
				$data = $item;
				$item = new TVA_Order_Item( $data['ID'] );
				$item->set_data( $data );

			}
			$this->set_order_item( $item );
		}
	}

	/**
	 * Set the Order Items from the DB
	 */
	protected function set_order_items() {
		if ( $this->ID ) {
			$sql         = 'SELECT * FROM ' . $this->wpdb->prefix . TVA_Const::DB_PREFIX . TVA_Const::ORDER_ITEMS_TABLE_NAME . ' WHERE order_id = %d';
			$order_items = $this->wpdb->get_results( $this->wpdb->prepare( $sql, array( $this->ID ) ), ARRAY_A );

			if ( ! empty( $order_items ) ) {
				foreach ( $order_items as $order_item ) {
					$has_item = false;
					foreach ( $this->get_order_items() as $set_order_item ) {
						if ( $set_order_item->get_id() == $order_item['ID'] ) {
							$has_item = true;
						}
					}
					if ( ! $has_item ) {
						$item = new TVA_Order_Item( $order_item['ID'] );
						$item->set_data( $order_item );
						$this->set_order_item( $item );
					}

				}
			}
		}
	}

	/**
	 * Save object data
	 *
	 * @param bool $with_items
	 *
	 * @return false|int
	 */
	public function save( $with_items = true ) {

		$data = get_object_vars( $this );
		unset( $data['wpdb'] );
		unset( $data['order_items'] );
		unset( $data['ID'] );
		$types = array(
			'%s',
			'%d',
			'%d',
			'%s',
			'%d',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
		);
		if ( ! $this->get_id() ) {

			do_action( 'tva_before_sendowl_insert_order', $data, $types, $this );

			$result = $this->wpdb->insert(
				$this->wpdb->prefix . TVA_Const::DB_PREFIX . TVA_Const::ORDERS_TABLE_NAME,
				$data,
				$types
			);

			if ( $result ) {
				$this->set_id( $this->wpdb->insert_id );
			}
		} else {
			do_action( 'tva_before_sendowl_update_order', $data, $types, $this );

			$result = $this->wpdb->update(
				$this->wpdb->prefix . TVA_Const::DB_PREFIX . TVA_Const::ORDERS_TABLE_NAME,
				$data,
				[ 'ID' => $this->get_id() ],
				$types,
				[ '%d' ]
			);
		}

		do_action( 'tva_after_sendowl_order_db', $data, $types, $this );

		if ( $result === false ) {
			return false;
		}

		if ( $with_items ) {

			foreach ( $this->get_order_items() as $order_item ) {
				/** @var TVA_Order_Item $order_item */
				$order_item->set_order_id( $this->get_id() );

				/**
				 * WordPress returns false if you update a row with the same data,
				 * so we need to check this in order to be sure that we need to save
				 * the data and not return a false negative response to the caller
				 */
				$diff = [ 'data' ]; //set some data so diff would save when we don't have an ID
				if ( $order_item->get_id() ) {
					$old_order_item = new TVA_Order_Item( $order_item->get_id() );
					$old_data       = $old_order_item->get_all_object_properties();
					$new_data       = $order_item->get_all_object_properties();

					$diff = array_diff( $new_data, $old_data );
				}

				if ( ! empty( $diff ) ) {
					$result = $order_item->save();

					if ( ! $result ) {
						return false;
					}

					/**
					 * Fires after an order item is saved
					 *
					 * @param TVA_Order_Item $order_item
					 */
					do_action( 'tva_order_item_after_save', $this->get_user_id(), $order_item );
				}
			}
		}

		do_action( 'tva_after_order_saved', $this );

		return $this->get_id();
	}

	/**
	 * @return int|null
	 */
	public function get_id() {
		return $this->ID;
	}

	/**
	 * @param int|null $id
	 */
	public function set_id( $id ) {
		$this->ID = (int) $id;
	}

	/**
	 * Sets a unique string identifier for current order
	 *
	 * @param string $number
	 */
	public function set_number( $number ) {
		$this->number = $number;
	}

	/**
	 * Gets the unique string identifier
	 *
	 * @return string
	 */
	public function get_number() {
		return $this->number;
	}

	/**
	 * @return null
	 */
	public function get_user_id() {
		return $this->user_id;
	}

	public function get_type() {
		return $this->type;
	}

	/**
	 * @param null $user_id
	 */
	public function set_user_id( $user_id ) {
		$this->user_id = (int) $user_id;

		return $this;
	}

	/**
	 * @return int
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * @param $status
	 */
	public function set_status( $status ) {
		$this->status = (int) $status;

		return $this;
	}

	/**
	 * @return null
	 */
	public function get_payment_id() {
		return $this->payment_id;
	}

	/**
	 * @param null $payment_id
	 */
	public function set_payment_id( $payment_id ) {
		$this->payment_id = $payment_id;
	}

	/**
	 * @return null
	 */
	public function get_gateway_order_id() {
		return $this->gateway_order_id;
	}

	/**
	 * @param null $gateway_order_id
	 */
	public function set_gateway_order_id( $gateway_order_id ) {
		$this->gateway_order_id = (int) $gateway_order_id;
	}

	/**
	 * @return string
	 */
	public function get_gateway() {
		return $this->gateway;
	}

	/**
	 * @param null $gateway
	 */
	public function set_gateway( $gateway ) {
		$this->gateway = $gateway;

		return $this;
	}

	/**
	 * @return null
	 */
	public function get_payment_method() {
		return $this->payment_method;
	}

	/**
	 * @param null $payment_method
	 */
	public function set_payment_method( $payment_method ) {
		$this->payment_method = $payment_method;
	}

	/**
	 * @return null
	 */
	public function get_gateway_fee() {
		return $this->gateway_fee;
	}

	/**
	 * @param null $gateway_fee
	 */
	public function set_gateway_fee( $gateway_fee ) {
		$this->gateway_fee = $gateway_fee;
	}

	/**
	 * @return null
	 */
	public function get_buyer_email() {
		return $this->buyer_email;
	}

	/**
	 * @param null $buyer_email
	 */
	public function set_buyer_email( $buyer_email ) {
		$this->buyer_email = $buyer_email;
	}

	/**
	 * @return null
	 */
	public function get_buyer_name() {
		return $this->buyer_name;
	}

	/**
	 * @param null $buyer_name
	 */
	public function set_buyer_name( $buyer_name ) {
		$this->buyer_name = $buyer_name;
	}

	/**
	 * @return null
	 */
	public function get_buyer_address() {
		return $this->buyer_address1 . ' ' . $this->billing_address2;
	}

	/**
	 * @param null $buyer_address1
	 */
	public function set_buyer_address1( $buyer_address1 ) {
		$this->buyer_address1 = $buyer_address1;
	}

	/**
	 * @param null $buyer_address2
	 */
	public function set_buyer_address2( $buyer_address2 ) {
		$this->buyer_address2 = $buyer_address2;
	}

	/**
	 * @return null
	 */
	public function get_buyer_city() {
		return $this->buyer_city;
	}

	/**
	 * @param null $buyer_city
	 */
	public function set_buyer_city( $buyer_city ) {
		$this->buyer_city = $buyer_city;
	}

	/**
	 * @return null
	 */
	public function get_buyer_region() {
		return $this->buyer_region;
	}

	/**
	 * @param null $buyer_region
	 */
	public function set_buyer_region( $buyer_region ) {
		$this->buyer_region = $buyer_region;
	}

	/**
	 * @return null
	 */
	public function get_buyer_postcode() {
		return $this->buyer_postcode;
	}

	/**
	 * @param null $buyer_postcode
	 */
	public function set_buyer_postcode( $buyer_postcode ) {
		$this->buyer_postcode = $buyer_postcode;
	}

	/**
	 * @return null
	 */
	public function get_buyer_country() {
		return $this->buyer_country;
	}

	/**
	 * @param null $buyer_country
	 */
	public function set_buyer_country( $buyer_country ) {
		$this->buyer_country = $buyer_country;
	}

	/**
	 * @return null
	 */
	public function get_billing_address() {
		return $this->billing_address1 . ' ' . $this->billing_address2;
	}

	/**
	 * @param null $billing_address1
	 */
	public function set_billing_address1( $billing_address1 ) {
		$this->billing_address1 = $billing_address1;
	}

	/**
	 * @param null $billing_address2
	 */
	public function set_billing_address2( $billing_address2 ) {
		$this->billing_address2 = $billing_address2;
	}

	/**
	 * @return null
	 */
	public function get_billing_city() {
		return $this->billing_city;
	}

	/**
	 * @param null $billing_city
	 */
	public function set_billing_city( $billing_city ) {
		$this->billing_city = $billing_city;
	}

	/**
	 * @return null
	 */
	public function get_billing_region() {
		return $this->billing_region;
	}

	/**
	 * @param null $billing_region
	 */
	public function set_billing_region( $billing_region ) {
		$this->billing_region = $billing_region;
	}

	/**
	 * @return null
	 */
	public function get_billing_postcode() {
		return $this->billing_postcode;
	}

	/**
	 * @param null $billing_postcode
	 */
	public function set_billing_postcode( $billing_postcode ) {
		$this->billing_postcode = $billing_postcode;
	}

	/**
	 * @return null
	 */
	public function get_billing_country() {
		return $this->billing_country;
	}

	/**
	 * @param null $billing_country
	 */
	public function set_billing_country( $billing_country ) {
		$this->billing_country = $billing_country;
	}

	/**
	 * @return null
	 */
	public function get_shipping_address() {
		return $this->shipping_address1 . ' ' . $this->shipping_address2;
	}

	/**
	 * @param null $shipping_address1
	 */
	public function set_shipping_address1( $shipping_address1 ) {
		$this->shipping_address1 = $shipping_address1;
	}

	/**
	 * @param null $shipping_address2
	 */
	public function set_shipping_address2( $shipping_address2 ) {
		$this->shipping_address2 = $shipping_address2;
	}

	/**
	 * @return null
	 */
	public function get_shipping_city() {
		return $this->shipping_city;
	}

	/**
	 * @param null $shipping_city
	 */
	public function set_shipping_city( $shipping_city ) {
		$this->shipping_city = $shipping_city;
	}

	/**
	 * @return null
	 */
	public function get_shipping_region() {
		return $this->shipping_region;
	}

	/**
	 * @param null $shipping_region
	 */
	public function set_shipping_region( $shipping_region ) {
		$this->shipping_region = $shipping_region;
	}

	/**
	 * @return null
	 */
	public function get_shipping_postcode() {
		return $this->shipping_postcode;
	}

	/**
	 * @param null $shipping_postcode
	 */
	public function set_shipping_postcode( $shipping_postcode ) {
		$this->shipping_postcode = $shipping_postcode;
	}

	/**
	 * @return null
	 */
	public function get_shipping_country() {
		return $this->shipping_country;
	}

	/**
	 * @param null $shipping_country
	 */
	public function set_shipping_country( $shipping_country ) {
		$this->shipping_country = $shipping_country;
	}

	/**
	 * @return null
	 */
	public function get_buyer_ip_address() {
		return $this->buyer_ip_address;
	}

	/**
	 * @param null $buyer_ip_address
	 */
	public function set_buyer_ip_address( $buyer_ip_address ) {
		$this->buyer_ip_address = $buyer_ip_address;
	}

	/**
	 * @return int
	 */
	public function get_is_gift() {
		return $this->is_gift;
	}

	/**
	 * @param int $is_gift
	 */
	public function set_is_gift( $is_gift ) {
		$this->is_gift = (int) $is_gift;
	}

	/**
	 * @return null
	 */
	public function get_price() {
		return $this->price;
	}

	/**
	 * @param null $price
	 */
	public function set_price( $price ) {
		$this->price = $price;
	}

	/**
	 * @return null
	 */
	public function get_price_gross() {
		return $this->price_gross;
	}

	/**
	 * @param null $price_gross
	 */
	public function set_price_gross( $price_gross ) {
		$this->price_gross = $price_gross;
	}

	/**
	 * @return null
	 */
	public function get_currency() {
		return $this->currency;
	}

	/**
	 * @param null $currency
	 */
	public function set_currency( $currency ) {
		$this->currency = $currency;
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

	/**
	 * @return string
	 */
	public function get_updated_at() {
		return $this->updated_at;
	}

	/**
	 * @param string $updated_at
	 */
	public function set_updated_at( $updated_at ) {
		$this->updated_at = $updated_at;
	}

	/**
	 * Order's type
	 * - manual; imported; paid
	 *
	 * @param string $type
	 */
	public function set_type( $type ) {
		$this->type = (string) $type;

		return $this;
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

	public function __debugInfo() {

		return $this->get_all_object_properties();
	}

	/**
	 * Based on filters fetches an order from DB
	 *
	 * @param array $filters column names
	 *
	 * @return TVA_Order
	 */
	public static function get_order( $filters ) {

		/** @var $wpdb wpdb */
		global $wpdb;

		$where = ' 1=1 ';
		foreach ( $filters as $column => $value ) {
			$where .= "AND {$column} =  '{$value}' ";
		}

		$sql      = 'SELECT ID FROM ' . self::get_table_name() . ' WHERE' . $where;
		$order_id = $wpdb->get_var( $sql );

		return new TVA_Order( $order_id );
	}

	/**
	 * Determines if the current order was added manually
	 *
	 * @return bool
	 */
	public function is_manual() {

		return $this->get_type() === TVA_Order::IMPORTED ||
			   $this->get_type() === TVA_Order::MANUAL ||
			   false !== stripos( $this->get_gateway(), 'manual' ) ||
			   false !== stripos( $this->get_gateway(), 'import' );
	}

	/**
	 * Checks if the current instance order has been generated by SendOwl integration
	 *
	 * @return bool
	 */
	public function is_sendowl() {
		return strtolower( $this->get_gateway() ) === strtolower( TVA_Const::SENDOWL_GATEWAY );
	}


	public function is_stripe() {
		return strtolower( $this->get_gateway() ) === strtolower( TVA_Const::STRIPE_GATEWAY );
	}

	/**
	 * Gets and order ID by its unique $number identifier
	 *
	 * @param string $number
	 *
	 * @return integer
	 */
	public static function get_order_id_by_number( $number ) {
		global $wpdb;
		$orders_table = $wpdb->prefix . TVA_Const::DB_PREFIX . TVA_Const::ORDERS_TABLE_NAME;

		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $orders_table WHERE number = %s", [ $number ] ) );
	}

	/**
	 * Checks if current order has type paid
	 *
	 * @return bool
	 */
	public function is_paid() {
		return $this->get_type() === TVA_Order::PAID;
	}

	/**
	 * Checks if current order has gateway woocommerce
	 *
	 * @return bool
	 */
	public function is_woocommerce() {
		return strtolower( $this->get_gateway() ) === strtolower( TVA_Const::WOOCOMMERCE_GATEWAY );
	}

	/**
	 * Calculates a specific string for access item type
	 * - used in edit customer access rights modal
	 *
	 * @param TVA_Order      $order
	 * @param TVA_Order_Item $order_item
	 *
	 * @return string
	 */
	public static function type( $order, $order_item = null ) {

		if ( false === $order instanceof TVA_Order ) {
			return '';
		}

		$type = 'unknown';

		if ( $order->get_type() === TVA_Order::PAID ) { //it's a paid order

			if ( strtolower( $order->get_gateway() ) === strtolower( TVA_Const::SENDOWL_GATEWAY ) && $order_item ) { //it's a sendowl order
				$type = sprintf( $order->get_gateway() . ' %s',
					in_array( $order_item->get_product_id(), TVA_SendOwl::get_products_ids() ) ? ' product' : ' bundle'
				);
			} elseif ( $order_item instanceof TVA_Order_Item && strpos( $order_item->get_product_id(), 'course.bundle' ) !== false ) {
				$type = 'Apprentice bundle';
			} elseif ( strtolower( $order->get_gateway() ) === strtolower( TVA_Const::WOOCOMMERCE_GATEWAY ) && $order_item ) {
				$type = $order->get_gateway();
				if ( function_exists( 'wc_get_product' ) ) {
					$wc_product = wc_get_product( $order_item->get_gateway_order_item_id() );
					$chunks     = explode( '-', $wc_product->get_type() );
					$chunks     = array_map( 'ucfirst', $chunks );
					$wc_product ? $type .= ' ' . implode( ' ', $chunks ) . ' Product' : null;
				}
			} else {
				$type = sprintf( esc_html__( '%s product', 'thrive-apprentice' ), $order->get_gateway() );
			}

		} elseif (
			in_array( $order->get_type(), [ TVA_Order::MANUAL, TVA_Order::IMPORTED ] )
			&& in_array( $order->get_gateway(), [ TVA_Const::SENDOWL_GATEWAY, TVA_Const::THRIVECART_GATEWAY ] )
		) {

			$type = sprintf( esc_html__( '%s product', 'thrive-apprentice' ), $order->get_gateway() );
		} elseif ( strpos( $order_item->get_product_id(), 'bundle' ) !== false ) {
			$type = 'Apprentice bundle';
		} elseif ( in_array( $order->get_type(), [ TVA_Order::MANUAL, TVA_Order::IMPORTED ] ) ) {
			$type = 'Apprentice course';
		}

		return $type;
	}

	/**
	 * * Calculates a specific string for access item source
	 * - used in edit customer access rights modal
	 *
	 * @param TVA_Order $order
	 *
	 * @return string
	 */
	public static function source( $order ) {

		if ( false === $order instanceof TVA_Order ) {
			return '';
		}

		switch ( $order->get_type() ) {
			case TVA_Order::IMPORTED:
				$source = esc_html__( 'Imported', 'thrive-apprentice' );
				break;
			case TVA_Order::PAID:
				$source = esc_html__( 'Purchased', 'thrive-apprentice' );
				break;
			case TVA_Order::MANUAL:
				$source = esc_html__( 'Manually added', 'thrive-apprentice' );
				break;
			default:
				$source = $order->get_type();
		}

		return $source;
	}

	/**
	 * Based on order and order item gets an purchased type slug
	 *
	 * @param TVA_Order      $order
	 * @param TVA_Order_Item $order_item
	 *
	 * @return string|"sendowl"|"thrivecart"|"added-manually"|"sendowl-product"|"sendowl-bundle"
	 */
	public static function purchase_type( $order, $order_item = null ) {

		$icon = strtolower( str_replace( ' ', '-', $order->get_gateway() ) );

		//sendowl gateway
		if ( strtolower( $order->get_gateway() ) === strtolower( TVA_Const::SENDOWL_GATEWAY ) && $order_item ) {
			$icon .= in_array( $order_item->get_product_id(), TVA_SendOwl::get_products_ids() ) ? '-product' : '-bundle';

			//thrivecart gateway
		} elseif ( $order->get_gateway() === TVA_Const::THRIVECART_GATEWAY && $order_item && false === is_wp_error( TVA_Bundle::init_by_number( $order_item->get_product_id() ) ) ) {
			$icon = 'apprentice-bundle';

			//woocommerce gateway with product type appended
		} elseif ( $order->get_gateway() === TVA_Const::WOOCOMMERCE_GATEWAY && $order_item ) {
			if ( function_exists( 'wc_get_product' ) ) {
				$wc_product = wc_get_product( $order_item->get_gateway_order_item_id() );

				$wc_product ? $icon .= '-' . $wc_product->get_type() : null;
			}
		}

		return $icon;
	}

	/**
	 * All access types user can have to course(s)
	 *
	 * @return string[]
	 */
	public static function get_access_types() {
		return [
			'sendowl-product'     => 'SendOwl product',
			'sendowl-bundle'      => 'SendOwl bundle',
			'thrivecart-product'  => 'ThriveCart product',
			'woocommerce-product' => 'Woocommerce product',
		];
	}

	/**
	 *  Get all the order for a specific product
	 *
	 * @param $product_id
	 * @param $user_id
	 * @param $status
	 *
	 * @return array|object|stdClass[]|null
	 */
	public static function get_orders_by_product( $product_id, $user_id = 0, $status = null ) {
		global $wpdb;

		$items_table  = $wpdb->prefix . TVA_Const::DB_PREFIX . TVA_Const::ORDER_ITEMS_TABLE_NAME;
		$orders_table = $wpdb->prefix . TVA_Const::DB_PREFIX . TVA_Const::ORDERS_TABLE_NAME;

		$sql        = 'SELECT orders.* FROM ' . $items_table . ' AS items INNER JOIN ' . $orders_table . ' orders ON items.order_id = orders.ID WHERE items.product_id = %s';
		$sql_params = [ $product_id ];

		if ( $user_id ) {
			$sql          .= ' AND orders.user_id = %d';
			$sql_params[] = $user_id;
		}

		if ( $status ) {
			$sql          .= ' AND orders.status = %d';
			$sql_params[] = $status;
		}

		$sql .= ' GROUP BY items.order_id';

		return $wpdb->get_results( $wpdb->prepare( $sql, $sql_params ), ARRAY_A );
	}

	/**
	 * Get orders which dont exist in the history table
	 *
	 * @param $status
	 *
	 * @return array|object|stdClass[]|null
	 */
	public static function get_orders_without_history( $status = TVA_Const::STATUS_COMPLETED ) {
		global $wpdb;
		$sql_params    = [];
		$orders_table  = $wpdb->prefix . TVA_Const::DB_PREFIX . TVA_Const::ORDERS_TABLE_NAME;
		$history_table = $wpdb->prefix . TVA_Const::DB_PREFIX . History_Table::get_table_name();

		$sql = 'SELECT orders.* FROM ' . $orders_table . ' AS orders LEFT JOIN ' . $history_table . ' AS history ON orders.user_id = history.user_id WHERE history.user_id IS NULL';
		if ( $status ) {
			$sql          .= ' AND orders.status = %d';
			$sql_params[] = $status;
		}

		$sql .= ' GROUP BY orders.buyer_email';

		return $wpdb->get_results( $wpdb->prepare( $sql, $sql_params ), ARRAY_A );
	}

	public static function fix_orders_without_history() {
		add_filter( 'tva_stripe_commit_product_data', '__return_true' );
		$orders = TVA_Order::get_orders_without_history();
		foreach ( $orders as $order_data ) {
			$order   = new TVA_Order();
			$user_id = $order_data['user_id'];
			if ( get_user_by( 'id', $user_id ) === false ) {
				continue;
			}
			$order->set_data( $order_data );
			$order->set_id( $order_data['ID'] );
			$order->save();
		}
		remove_filter( 'tva_stripe_commit_product_data', '__return_true' );
	}

	/**
	 * Get all orders for a specific gateway
	 */
	public static function get_orders_by_gateway( $gateway ) {
		global $wpdb;

		$orders_table = $wpdb->prefix . TVA_Const::DB_PREFIX . TVA_Const::ORDERS_TABLE_NAME;
		$sql          = 'SELECT * FROM ' . $orders_table . ' WHERE gateway = %s GROUP BY user_id';

		return $wpdb->get_results( $wpdb->prepare( $sql, $gateway ), ARRAY_A );
	}
}
