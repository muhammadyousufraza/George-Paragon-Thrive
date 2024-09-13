<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Trait TD_Magic_Methods
 */
trait TD_Magic_Methods {

	protected $_data = array();

	/**
	 * @param $name
	 *
	 * @return bool
	 */
	public function __isset( $name ) {

		return isset( $this->_data[ $name ] );
	}

	/**
	 * @param $key
	 * @param $value
	 *
	 * @return mixed
	 */
	public function __set( $key, $value ) {

		$this->_data[ $key ] = $value;

		return $this->_data[ $key ];
	}

	/**
	 * @param $param
	 *
	 * @return mixed|null
	 */
	public function __get( $param ) {

		$value = null;

		if ( isset( $this->_data[ $param ] ) ) {
			$value = $this->_data[ $param ];
		}

		return $value;
	}
}
