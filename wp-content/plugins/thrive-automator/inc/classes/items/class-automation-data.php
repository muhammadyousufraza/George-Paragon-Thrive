<?php

namespace Thrive\Automator\Items;

use ArrayAccess;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Automation
 */
class Automation_Data implements ArrayAccess {
	private $data;

	private $raw_data;

	public function __construct( $data ) {
		foreach ( $data as $key => $data_object ) {
			$this->set( $key, $data_object );
		}
	}

	public function set( $key, $object ) {
		$this->data[ $key ] = $object;
	}

	public function get( $key ) {
		return empty( $this->data[ $key ] ) ? null : $this->data[ $key ];
	}

	public function get_all() {
		return $this->data;
	}

	public function get_raw_data() {
		return $this->raw_data;
	}

	public function set_raw_data( $data ) {
		$this->raw_data = $data;
	}

	#[\ReturnTypeWillChange]
	public function offsetExists( $offset ) {
		return ! empty( $this->get( $offset ) );
	}

	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		return $this->get( $offset );
	}

	#[\ReturnTypeWillChange]
	public function offsetSet( $offset, $value ) {
		$this->set( $offset, $value );
	}

	#[\ReturnTypeWillChange]
	public function offsetUnset( $offset ) {
	}
}
