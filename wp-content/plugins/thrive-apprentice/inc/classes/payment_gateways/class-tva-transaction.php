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
 * Class TVA_Transaction
 */
class TVA_Transaction {

	/**
	 * @var null
	 */
	protected $ID = null;
	/**
	 * @var int
	 */
	protected $order_id = 0;
	/**
	 * @var string
	 */
	protected $transaction_id = '';
	/**
	 * @var string
	 */
	protected $currency = '';
	/**
	 * @var int
	 */
	protected $price = 0;
	/**
	 * @var int
	 */
	protected $price_gross = 0;
	/**
	 * @var int
	 */
	protected $gateway_fee = 0;
	/**
	 * @var int
	 */
	protected $transaction_type = 0;
	/**
	 * @var string
	 */
	protected $gateway = '';
	/**
	 * @var string
	 */
	protected $card_last_4_digits = '';
	/**
	 * @var string
	 */
	protected $card_expires_at = '0000-00-00';
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
	 * TVA_Transaction constructor.
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
		$sql        = 'SELECT * FROM ' . $this->wpdb->prefix . TVA_Const::DB_PREFIX . TVA_Const::TRANSACTIONS_TABLE_NAME . ' WHERE ID = %d';
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
	 * Save the data
	 *
	 * @return bool
	 */
	public function save() {
		$transaction_id = $this->get_transaction_id();

		if ( empty( $transaction_id ) ) {
			return false;
		}

		$data = get_object_vars( $this );
		unset( $data['wpdb'] );
		unset( $data['ID'] );

		$types = array(
			'%d',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%d',
			'%s',
			'%s',
			'%s',
			'%s',
		);

		if ( ! $this->get_id() ) {

			do_action( 'tva_before_sendowl_insert_order_item', $data, $types, $this );

			$result = $this->wpdb->insert(
				$this->wpdb->prefix . TVA_Const::DB_PREFIX . TVA_Const::TRANSACTIONS_TABLE_NAME,
				$data,
				$types
			);

			if ( $result ) {
				$this->set_id( $result );
			}


		} else {

			do_action( 'tva_before_sendowl_update_order_item', $data, $types, $this );

			$result = $this->wpdb->update(
				$this->wpdb->prefix . TVA_Const::DB_PREFIX . TVA_Const::TRANSACTIONS_TABLE_NAME,
				$data,
				array( 'ID' => $this->get_id() ),
				$types,
				array( '%d' )
			);
		}

		do_action( 'tva_after_sendowl_order_item_db', $data, $types, $this );

		return $result;
	}

	/**
	 * @return null
	 */
	public function get_ID() {
		return $this->ID;
	}

	/**
	 * @param null $ID
	 */
	public function set_ID( $ID ) {
		$this->ID = $ID;
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
		$this->order_id = $order_id;
	}

	/**
	 * @return string
	 */
	public function get_transaction_id() {
		return $this->transaction_id;
	}

	/**
	 * @param string $transaction_id
	 */
	public function set_transaction_id( $transaction_id ) {
		$this->transaction_id = $transaction_id;
	}

	/**
	 * @return string
	 */
	public function get_currency() {
		return $this->currency;
	}

	/**
	 * @param string $currency
	 */
	public function set_currency( $currency ) {
		$this->currency = $currency;
	}

	/**
	 * @return int
	 */
	public function get_price() {
		return $this->price;
	}

	/**
	 * @param int $price
	 */
	public function set_price( $price ) {
		$this->price = $price;
	}

	/**
	 * @return int
	 */
	public function get_price_gross() {
		return $this->price_gross;
	}

	/**
	 * @param int $price_gross
	 */
	public function set_price_gross( $price_gross ) {
		$this->price_gross = $price_gross;
	}

	/**
	 * @return int
	 */
	public function get_gateway_fee() {
		return $this->gateway_fee;
	}

	/**
	 * @param int $gateway_fee
	 */
	public function set_gateway_fee( $gateway_fee ) {
		$this->gateway_fee = $gateway_fee;
	}

	/**
	 * @return int
	 */
	public function get_transaction_type() {
		return $this->transaction_type;
	}

	/**
	 * @param int $transaction_type
	 */
	public function set_transaction_type( $transaction_type ) {
		$this->transaction_type = $transaction_type;
	}

	/**
	 * @return string
	 */
	public function get_gateway() {
		return $this->gateway;
	}

	/**
	 * @param string $gateway
	 */
	public function set_gateway( $gateway ) {
		$this->gateway = $gateway;
	}

	/**
	 * @return string
	 */
	public function get_card_last_4_digits() {
		return $this->card_last_4_digits;
	}

	/**
	 * @param string $card_last_4_digits
	 */
	public function set_card_last_4_digits( $card_last_4_digits ) {
		$this->card_last_4_digits = $card_last_4_digits;
	}

	/**
	 * @return string
	 */
	public function get_card_expires_at() {
		return $this->card_expires_at;
	}

	/**
	 * @param string $card_expires_at
	 */
	public function set_card_expires_at( $card_expires_at ) {
		$this->card_expires_at = $card_expires_at;
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


}
