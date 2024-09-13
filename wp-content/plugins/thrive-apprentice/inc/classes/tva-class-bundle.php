<?php

/**
 * Class TVA_Bundle
 * - bundle model which holds a group of courses
 *
 * @property int    id
 * @property string name
 * @property array  products
 * @property string edited
 * @property string number
 * @property int    $total_customers
 */
class TVA_Bundle implements JsonSerializable {

	/**
	 * @var array of bundle props
	 */
	private $_data = array();

	/**
	 * TVA_Bundle constructor.
	 *
	 * @param int|array $data
	 */
	public function __construct( $data ) {

		if ( is_int( $data ) ) {
			$this->_data['id'] = $data;
			$this->_init_from_db();
		} else {
			$this->_data = (array) $data;
		}
	}

	/**
	 * Magic getter
	 *
	 * @param string $key
	 *
	 * @return mixed|null
	 */
	public function __get( $key ) {

		$value = null;

		if ( isset( $this->_data[ $key ] ) ) {
			$value = $this->_data[ $key ];
		}

		return $value;
	}

	/**
	 * Magic setter
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	public function __set( $key, $value ) {

		$this->_data[ $key ] = $value;
	}

	/**
	 * Table name where the bundles are stored
	 *
	 * @return string
	 */
	public static function table_name() {

		global $wpdb;

		return $wpdb->base_prefix . 'tva_bundles';
	}

	/**
	 * Updates or Inserts new row bundle in DB based on ID
	 *
	 * @return int|bool|WP_Error
	 */
	public function save() {


		if ( empty( $this->_data['id'] ) ) {

			$id = $this->_insert( $this->_data );

			if ( is_wp_error( $id ) ) {
				return $id;
			}


			return $this->_data['id'] = (int) $id;
		}

		return $this->_update( $this->_data );
	}

	/**
	 * Persists props in DB by updating
	 *
	 * @param array $data
	 *
	 * @return bool|WP_Error
	 */
	private function _update( $data ) {

		global $wpdb;

		if ( empty( $data['id'] ) ) {
			return new WP_Error( 'bundle_not_updated', esc_html__( 'Missing bundle id to be updated', 'thrive-apprentice' ) );
		}

		if ( empty( $data['products'] ) ) {
			$data['products'] = array();
		}

		$data['products'] = maybe_serialize( $data['products'] );
		$data['edited']   = date( 'Y-m-d H:i:s' );

		$updated = $wpdb->update( self::table_name(), $data, array( 'id' => $data['id'] ) );

		return $updated === false ? new WP_Error( 'bundle_not_update', $wpdb->last_error ) : true;
	}

	/**
	 * Adds new bundle in DB
	 *
	 * @param array $data
	 *
	 * @return int|WP_Error
	 */
	private function _insert( $data ) {

		global $wpdb;

		if ( empty( $data['products'] ) ) {
			$data['products'] = array();
		}

		$data['products'] = maybe_serialize( $data['products'] );
		$data['created']  = date( 'Y-m-d H:i:s' );
		$data['number']   = uniqid( 'course.bundle.' );

		$inserted = $wpdb->insert( self::table_name(), $data );

		if ( false === $inserted ) {
			return new WP_Error( 'bundle_not_inserted', $wpdb->last_error );
		}

		$this->number = $data['number'];

		return (int) $wpdb->insert_id;
	}

	/**
	 * Fetches data from DB and assign it to current instance
	 *
	 * @return array|false
	 */
	private function _init_from_db() {

		global $wpdb;

		if ( empty( $this->_data['id'] ) ) {
			return false;
		}

		$sql = 'SELECT * FROM ' . self::table_name() . ' WHERE id = %d';

		$data = $wpdb->get_row( $wpdb->prepare( $sql, array( (int) $this->_data['id'] ) ), ARRAY_A );

		$data['products'] = maybe_unserialize( $data['products'] );

		return is_array( $data ) ? $this->_data = $data : false;
	}

	/**
	 * Expose which data for current instance should be used on serialize(json_encode)
	 *
	 * @return array
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {

		return $this->_data;
	}

	/**
	 * Fetches from DB
	 *
	 * @return TVA_Bundle[]
	 */
	public static function get_list() {

		global $wpdb;

		$bundles = array();
		$sql     = 'SELECT * FROM ' . self::table_name();
		$rows    = $wpdb->get_results( $sql, ARRAY_A );

		foreach ( $rows as $item ) {
			$item['id']       = (int) $item['id'];
			$item['products'] = maybe_unserialize( $item['products'] );
			$bundles[]        = new TVA_Bundle( $item );
		}

		return $bundles;
	}

	/**
	 * Deletes a row from DB based on ID
	 *
	 * @param int $id
	 *
	 * @return bool|WP_Error
	 */
	public static function delete( $id ) {

		global $wpdb;

		$deleted = $wpdb->delete( self::table_name(), array( 'id' => (int) $id ) );

		if ( is_wp_error( $deleted ) ) {
			/** @var $deleted WP_Error */
			return $deleted;
		}

		return true;
	}

	/**
	 * Checks if product id exists in bundled products list
	 *
	 * @param int|string $product_id
	 *
	 * @return bool
	 */
	public function contains_product( $product_id ) {

		return in_array( $product_id, $this->products );
	}

	/**
	 * Counts purchases of current bundle
	 *
	 * @return int
	 */
	public function count_purchases() {
		if ( ! empty( $this->_data['purchases'] ) ) {
			return $this->_data['purchases'];
		}

		global $wpdb;
		$params = array();

		$sql = 'SELECT (*) FROM ' . TVA_Order::get_table_name() . ' AS o WHERE o.status =  %s';

		return 0;
	}

	/**
	 * Init a course bundle by its number
	 *
	 * @param string $number
	 *
	 * @return TVA_Bundle|WP_Error
	 */
	public static function init_by_number( $number ) {

		global $wpdb;

		$number = (string) $number;

		$sql  = 'SELECT * FROM ' . self::table_name() . ' WHERE number = %s';
		$data = $wpdb->get_row( $wpdb->prepare( $sql, array( $number ) ), ARRAY_A );

		if ( ! empty( $data ) && is_array( $data ) ) {
			$data['id']       = (int) $data['id'];
			$data['products'] = maybe_unserialize( $data['products'] );

			return new TVA_Bundle( $data );
		}

		return new WP_Error( 'no_bundle_found', sprintf( esc_html__( 'No bundle found with number %s', 'thrive-apprentice' ), $number ) );
	}

	/**
	 * Counts total users who have bought or have been assigned manually this bundle manually
	 *
	 * @return int
	 */
	public function count_customers() {

		global $wpdb;

		$sql    = "select distinct count(u.ID) FROM " . $wpdb->prefix . "users" . " u
				inner join " . TVA_Order::get_table_name() . " o ON u.ID = o.user_id
				inner join " . TVA_Order_Item::get_table_name() . " i ON o.ID = i.order_id
				where i.product_id = '%s'
				AND o.status = 1
				AND i.status = 1
				";
		$params = array( $this->number );

		$count                 = (int) $wpdb->get_var( $wpdb->prepare( $sql, $params ) );
		$this->total_customers = $count;

		return $count;
	}

	/**
	 * Removes one product from bundle
	 *
	 * @param int|string $id
	 *
	 * @return bool|int|WP_Error
	 */
	public function remove_product( $id ) {

		array_splice( $this->_data['products'], array_search( $id, $this->_data['products'] ), 1 );

		return $this->save();
	}
}
