<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Transfer_Archive_Data
 */
class Thrive_Transfer_Archive_Data implements ArrayAccess {
	use Thrive_Singleton;

	/**
	 * All the data from the website that will end up in the archive
	 *
	 * @var array
	 */
	private $container;

	/**
	 * Thrive_Transfer_Archive_Data singleton constructor.
	 */
	private function __construct() {
		$this->container = [];
	}

	/**
	 * Return the array container with all its data
	 *
	 * @return array
	 */
	public function get_data() {
		return $this->container;
	}

	/**
	 * Reset instance for the new export
	 */
	public static function reset() {
		static::$_instance = null;
	}

	/**
	 * Below are functions that we need because the class implements ArrayAccess -> we can access class properties as indexes
	 */

	/**
	 * Assign a value to the specified offset
	 *
	 * @param mixed $offset
	 * @param mixed $value
	 */
	#[\ReturnTypeWillChange]
	public function offsetSet( $offset, $value ) {
		if ( is_null( $offset ) ) {
			$this->container[] = $value;
		} else {
			$this->container[ $offset ] = $value;
		}
	}

	/**
	 * Whether an offset exists
	 *
	 * @param mixed $offset
	 *
	 * @return bool
	 */
	#[\ReturnTypeWillChange]
	public function offsetExists( $offset ) {
		return isset( $this->container[ $offset ] );
	}

	/**
	 * Unset an offset
	 *
	 * @param mixed $offset
	 */
	#[\ReturnTypeWillChange]
	public function offsetUnset( $offset ) {
		unset( $this->container[ $offset ] );
	}

	/**
	 * Offset to retrieve
	 *
	 * @param mixed $offset
	 *
	 * @return mixed|null
	 */
	#[\ReturnTypeWillChange]
	public function offsetGet( $offset ) {
		return isset( $this->container[ $offset ] ) ? $this->container[ $offset ] : null;
	}

}
