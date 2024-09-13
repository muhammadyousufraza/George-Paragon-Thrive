<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\ConditionalDisplay\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

trait Item {
	protected static $registered_items = [];

	/**
	 * Get a list of all the registered items
	 *
	 * @param bool $sort_by_order
	 *
	 * @return  static[]
	 */
	public static function get( $sort_by_order = false ) {
		$items = static::$registered_items;

		if ( $sort_by_order ) {
			uasort( $items, static function ( $item1, $item2 ) {
				/* @var Item $item1 , $item2 */
				return $item1::get_display_order() - $item2::get_display_order();
			} );
		}

		return $items;
	}

	/**
	 * @param  $item
	 */
	public static function register( $item ) {
		if ( is_subclass_of( $item, static::class ) ) {
			if ( ! empty( $item::get_key() ) ) {
				static::$registered_items[ $item::get_key() ] = $item;
			} else {
				trigger_error( $item . ' does not have an ID.' );
			}
		} else {
			trigger_error( 'Argument ' . $item . ' must be a subclass of ' . static::class );
		}
	}

	/**
	 * @param string $key
	 * @param array  $extra_data
	 *
	 * @return mixed
	 */
	public static function get_instance( $key = '', $extra_data = [] ) {
		$items    = static::get();
		$instance = null;

		if ( isset( $items[ $key ] ) ) {
			$instance = new $items[ $key ]( $extra_data );
		}

		return $instance;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return __CLASS__;
	}
}
