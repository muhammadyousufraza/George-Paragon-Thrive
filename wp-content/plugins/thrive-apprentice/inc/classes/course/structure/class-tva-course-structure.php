<?php

namespace TVA\Course\Structure;

use TVA_Post;

class TVA_Course_Structure implements \Countable, \Iterator, \JsonSerializable {

	private $current_position = 0;

	/**
	 * @var TVA_Post[]
	 */
	private $items = array();

	/**
	 * @param TVA_Post $post
	 *
	 * @return void
	 */
	public function add( $post ) {
		if ( $post instanceof TVA_Post ) {
			$this->items[] = $post;
		}
	}

	public function remove( $item ) {
	}

	#[\ReturnTypeWillChange]
	public function count() {
		return count( $this->items );
	}

	#[\ReturnTypeWillChange]
	public function current() {
		return $this->items[ $this->current_position ];
	}

	#[\ReturnTypeWillChange]
	public function next() {
		++ $this->current_position;
	}

	#[\ReturnTypeWillChange]
	public function key() {
		return $this->current_position;
	}

	#[\ReturnTypeWillChange]
	public function valid() {
		return isset( $this->items[ $this->current_position ] );
	}

	#[\ReturnTypeWillChange]
	public function rewind() {
		$this->current_position = 0;
	}

	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return $this->items;
	}

	public function pluck( $args ) {
		$items = array();

		foreach ( $this->items as $item ) {
			$items[] = $item->pluck( $args );
		}

		return $items;
	}
}
