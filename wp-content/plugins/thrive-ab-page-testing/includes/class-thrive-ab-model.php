<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ab-page-testing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

abstract class Thrive_AB_Model {

	/**
	 * @var wpdb
	 */
	protected $wpdb;

	/**
	 * @var array associative which has indexes columns from DB
	 */
	protected $_data;

	/**
	 * Thrive_AB_Model constructor.
	 * If model is int then a db request is made and _data is initialized
	 *
	 * @param array|int $model of data to be save in DB
	 */
	public function __construct( $model = array() ) {

		global $wpdb;

		$this->wpdb = $wpdb;

		$defaults = $this->_get_default_data();

		if ( ! is_array( $model ) && is_int( $model ) ) {
			$this->id = $model;
			$model    = array( 'id' => $model );
			$this->init();
			$model = array_merge( $model, $this->_data );
		}

		$this->_data = array_merge( $defaults, $model );
	}

	/**
	 * Magic call of prop from _data
	 *
	 * @param $key
	 *
	 * @return mixed|null
	 */
	public function __get( $key ) {

		if ( method_exists( $this, $key ) ) {
			$value = call_user_func( array( $this, $key ) );
		} else {
			$value = isset( $this->_data[ $key ] ) ? $this->_data[ $key ] : null;
		}

		return $value;
	}

	/**
	 * Magic setter
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return mixed
	 */
	public function __set( $key, $value ) {

		if ( method_exists( $this, 'set_' . $key ) ) {

			return call_user_func( array( $this, 'set_' . $key ), $value );
		}

		$this->_data[ $key ] = $value;

		return $this->_data[ $key ];
	}

	/**
	 * Read from DB the row with id
	 *
	 * id prop has to exists in $this->_data
	 *
	 * @throws Exception
	 * @return $this
	 */
	public function init() {

		if ( ! $this->id ) {
			throw new Exception( __( 'Invalid model id', 'thrive-ab-page-testing' ) );
		}

		$data = $this->wpdb->get_row( 'SELECT * FROM ' . $this->_table_name() . ' WHERE id = ' . $this->id, ARRAY_A );

		if ( ! empty( $data ) ) {
			foreach ( $data as $key => $value ) {
				$this->$key = $value;
			}
		}

		return $this;
	}

	/**
	 * Based on what exists in $this->_data insert or update the DB
	 *
	 * @return $this
	 * @throws Exception if model is not valid or is not saved in DB
	 */
	public function save() {

		if ( ! $this->is_valid() ) {
			throw new Exception( __( sprintf( 'Invalid model %s with data: %s', get_class( $this ), var_export( $this->_data, true ) ), 'thrive-ab-page-testing' ) );
		}

		$data = $this->_prepare_data();

		if ( isset( $data['unique'] ) ) {
			unset( $data['unique'] );
		}

		if ( $this->id ) {
			$saved = $this->wpdb->update( $this->_table_name(), $data, array( 'id' => $this->id ) );
		} else {
			$saved = $this->wpdb->insert( $this->_table_name(), $data );
		}

		if ( is_wp_error( $saved ) || $saved === false ) {
			throw new Exception( __( 'Model could not be saved', 'thrive-ab-page-testing' ) );
		}

		if ( isset( $this->wpdb->insert_id ) && $this->wpdb->insert_id && empty( $data['id'] ) ) {
			$this->id = $this->wpdb->insert_id;
		}

		return $this;
	}

	/**
	 * Delete by id
	 *
	 * @return false|int
	 * @throws Exception
	 */
	public function delete() {
		if ( ! is_numeric( $this->id ) ) {
			throw new Exception( __( 'Invalid input for delete', 'thrive-ab-page-testing' ) );
		}

		return $this->wpdb->delete( $this->_table_name(), array( 'id' => $this->id ) );
	}

	/**
	 * If some data is not send at initialization
	 * default data can be stored in db.
	 *
	 * To be overwritten by specific models
	 *
	 * @return array
	 */
	protected function _get_default_data() {

		return array();
	}

	/**
	 * Access the all _data
	 *
	 * @return array
	 */
	public function get_data() {

		return $this->_data;
	}

	/**
	 * Called before save. This data is stored in DB
	 *
	 * @return array
	 */
	protected function _prepare_data() {

		return $this->_data;
	}

	/**
	 * Return what should be localized for JS
	 *
	 * @return mixed
	 */
	public function json_data() {

		return $this->_data;
	}

	/**
	 * Returns the table name for data to be saved
	 *
	 * @return string
	 */
	abstract protected function _table_name();

	/**
	 * Called before saving _data in DB
	 *
	 * @return mixed
	 */
	abstract protected function is_valid();
}
