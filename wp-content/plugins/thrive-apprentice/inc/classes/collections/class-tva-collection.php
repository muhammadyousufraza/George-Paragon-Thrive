<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 4/24/2019
 * Time: 13:13
 */

class TVA_Collection {

	/**
	 * @var string
	 */
	protected $id_key = 'ID';

	/**
	 * @var array
	 */
	protected $items = array();

	/**
	 * @var null
	 */
	protected $model = 'TVA_Model';

	/**
	 * Create a new collection.
	 *
	 * TVA_Collection constructor.
	 *
	 * @param array $items
	 */
	public function __construct( $items ) {

		if ( ! class_exists( $this->model ) || ! is_subclass_of( $this->model, 'TVA_Model' ) ) {
			return new WP_Error( 'invalid-model', 'Invalid model provided' );
		}

		$items = $this->get_items_as_array( $items );

		foreach ( $items as $item ) {
			/** @var TVA_Model $model */
			$model = new $this->model( $item );

			$this->add( $model, $model->get_collection_key() );
		}
	}

	/**
	 * @param $items
	 *
	 * @return array|mixed
	 */
	protected function get_items_as_array( $items ) {

		if ( is_array( $items ) ) {
			return $items;
		} elseif ( $items instanceof self ) {
			return $items->get_items();
		}

		return (array) $items;
	}

	/**
	 * Create a new collection instance
	 *
	 * @param  mixed $items
	 *
	 * @return static
	 */
	public static function make( $items = array() ) {
		return new static( $items );
	}

	/**
	 * @param      $value
	 * @param null $key
	 *
	 * @return array
	 */
	public function pluck( $value, $key = null ) {
		return array_values( wp_list_pluck( $this->items, $value, $key ) );
	}

	/**
	 * Add an item to the collection.
	 *
	 * @param      $item
	 * @param null $key
	 *
	 * @return $this
	 */
	public function add( $item, $key = null ) {
		null === $key
			? $this->items[] = $item
			: $this->items[ $key ] = $item;

		return $this;
	}

	/**
	 * Check weather the collection is empty or not.
	 *
	 * @return bool
	 */
	public function is_empty() {
		return empty( $this->items );
	}

	/**
	 * Return instance items
	 *
	 * @return array|mixed
	 */
	public function get_items() {
		return $this->items;
	}

	/**
	 * Set a value
	 *
	 * @param      $key
	 * @param null $value
	 */
	public function set( $key, $value = null ) {
		$this->items[ $key ] = $value;
	}

	/**
	 * Run a filter over each of the items.
	 *
	 * @param $callback
	 *
	 * @return static
	 */
	public function filter( $callback ) {
		if ( is_callable( $callback ) ) {

			return new static(
				array_filter(
					$this->get_items(),
					$callback
				)
			);
		}

		return new static( $this->items );
	}

	/**
	 * @param $id
	 *
	 * @return TVA_Collection
	 */
	public function get_by_id( $id ) {
		$key = $this->id_key;

		return $this->filter(
			function ( $item ) use ( $id, $key ) {
				return isset( $item->$key ) && $item->$key === $id;
			}
		)->first();
	}

	/**
	 * @return mixed|string
	 */
	public function first() {
		$items = array_values( $this->get_items() );

		return isset( $items[0] ) ? $items[0] : '';
	}

	/**
	 * Remove an item from the collection
	 *
	 * @param $key
	 */
	public function remove( $key ) {
		unset( $this->items[ $key ] );
	}

	/**
	 * Count collection items
	 *
	 * @return int
	 */
	public function count() {
		return count( $this->items );
	}

	/**
	 * Update an item in collection with it's new value
	 *
	 * @param $item
	 */
	public function update_item( $item ) {
		/** @var TVA_Model $item */
		$this->remove( $item->get_id() );
		$this->add( $item, $item->get_id() );
	}

	/**
	 * Returns a value at a given key
	 *
	 * @param $key
	 *
	 * @return mixed|string
	 */
	public function get_from_key( $key ) {
		return isset( $this->items[ $key ] ) ? $this->items[ $key ] : '';
	}
}
