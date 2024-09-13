<?php
/**
 * Created by PhpStorm.
 * User: dan bilauca
 * Date: 12-Apr-19
 * Time: 01:54 PM
 */

class TVA_Integration_Item {

	/**
	 * @var string|int
	 */
	protected $_id;

	protected $_name;

	public function __construct( $id, $name ) {

		if ( empty( $id ) || empty( $name ) || ( ! is_int( $id ) && ! is_string( $id ) ) ) {
			throw new Exception( 'Invalid properties for an Integration Item' );
		}

		$this->_id   = $id;
		$this->_name = $name;
	}

	public function get_id() {

		return $this->_id;
	}

	public function get_name() {

		return $this->_name;
	}
}
