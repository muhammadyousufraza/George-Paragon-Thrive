<?php

namespace Thrive\Automator\Traits;

use Thrive\Automator\Items\File_Loader;
use Thrive\Automator\Utils;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

trait Automation_Item {

	protected static $registered_items = [];

	/**
	 * In case we need to class name, mostly for trigger_error
	 *
	 * @return string
	 */
	public function __toString() {
		return __CLASS__;
	}

	/**
	 * Get list of all registered items
	 *
	 * @return  static[]
	 */
	public static function get(): array {
		return static::$registered_items;
	}

	/**
	 * Get the id of the current automation which uses the current class
	 *
	 * @return int|mixed
	 */
	public function get_automation_id() {
		return $this->aut_id ?? 0;
	}

	/**
	 * Get one from registered items
	 *
	 * @return  object
	 */
	public static function get_by_id( $id = null ) {
		return $id && isset( static::$registered_items[ $id ] ) ? static::$registered_items[ $id ] : null;
	}

	/**
	 * Get one from registered items based on the app id
	 *
	 * @param $app_id
	 *
	 * @return array
	 */
	public static function get_all_by_app( $app_id, $with_data = true ) {
		$items = [];
		foreach ( static::$registered_items as $item ) {
			if ( $item::get_app_id() === $app_id ) {
				if ( $with_data ) {
					$items[ $item::get_id() ] = $item::get_info();
				} else {
					$items[ $item::get_id() ] = $item::get_id();
				}
			}
		}

		return $items;
	}

	/**
	 * If the action is valid, register it
	 *
	 * @param  $automation_item
	 */
	public static function register( $automation_item ) {
		if ( is_subclass_of( $automation_item, static::class ) ) {
			if ( ! empty( $automation_item::get_id() ) && static::validate_item( $automation_item ) ) {
				static::$registered_items[ $automation_item::get_id() ] = $automation_item;
			} else {
				Utils::trigger_error( $automation_item . ' does not pass validation. Please check required_properties.' );
			}
		} else {
			Utils::trigger_error( 'Argument ' . $automation_item . ' must be a subclass of ' . static::class );
		}
	}

	/**
	 * Load local items
	 */
	public static function load( $type ) {

		foreach ( File_Loader::load_local_items( $type ) as $item ) {
			static::register( $item );
		}
	}

	/**
	 * Get list of all items to localize
	 *
	 * @return array
	 */
	public static function localize_all(): array {
		$items = [];

		foreach ( static::get() as $key => $class ) {
			$items[ $key ] = $class::get_info();
		}

		return $items;
	}


	/**
	 * Create specific action instance
	 *
	 *
	 * @param string $key
	 * @param array  $automation_data
	 *
	 * @return mixed
	 */
	public static function get_instance( string $key = '', array $automation_data = [], int $automation_id = 0 ) {
		$items    = static::get();
		$instance = null;
		if ( isset( $items[ $key ] ) ) {
			$instance = new $items[ $key ]( $automation_data, $automation_id );
		}

		return $instance;
	}

	/**
	 * Validate if item has all needed methods and properties
	 *
	 * @param  $item_class
	 *
	 * @return bool
	 */
	public static function validate_item( $item_class ): bool {
		$properties = $item_class::required_properties();

		if ( empty( $properties ) ) {
			return false;
		}

		if ( ! empty( static::$registered_items[ $item_class::get_id() ] ) ) {
			Utils::trigger_error( $item_class::get_id() . ' is already registered' );

			return false;
		}

		$valid = true;

		foreach ( $properties as $property => $type ) {
			if ( gettype( $item_class::$property() ) !== $type ) {
				Utils::trigger_error( $property . ' from ' . $item_class . ' returns wrong value type' );

				$valid = false;
			}
		}

		return $valid;
	}
}
