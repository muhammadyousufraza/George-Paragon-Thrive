<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 4/24/2019
 * Time: 15:01
 */

class TVA_Model {

	protected $id_key = 'ID';

	protected $data;

	protected $errors = array();

	public function __construct( $data ) {
		$this->data = $data;
		$this->init();
	}

	/**
	 * Init model data
	 */
	public function init() {
		$this->set_public_fields();
	}

	public function set_public_fields() {
		foreach ( $this->get_public_fields() as $key => $field ) {
			$this->$field = isset( $this->data[ $field ] ) ? $this->data[ $field ] : '';
		}
	}

	/**
	 * @return array
	 */
	public function get_public_fields() {
		return array();
	}

	/**
	 * @return array
	 */
	public function get_extra_fields() {
		return array();
	}

	/**
	 * @return array
	 */
	public function to_array() {
		if ( ! $this instanceof self ) {
			return array();
		}

		$response = array();
		$fields   = array_merge( $this->get_public_fields(), $this->get_extra_fields() );

		foreach ( $fields as $key => $field ) {
			$response[ $field ] = isset( $this->$field ) ? $this->$field : '';
		}

		return $response;
	}

	public function get_id() {
		$id = $this->id_key;

		return $this->$id;
	}

	/**
	 * Return the value of this instance in it's collection
	 *
	 * @return string|null
	 */
	public function get_collection_key() {
		$key = $this->id_key;

		return isset( $this->$key ) ? $this->$key : null;
	}
}
