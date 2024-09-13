<?php

namespace TVD\Cache;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Cache Exception class. Thrown when Cache instances are not constructed properly
 */
class Cache_Exception extends \RuntimeException {

}

/**
 * Wrapper over WP's *_metadata() functions.
 * Allows one-line access to an easy db metadata cache implementation
 */
class Meta_Cache {
	/**
	 * Stores what type of metadata should be handled. Defaults to `post`. Accepted values: comment, post, term, user
	 *
	 * @var string
	 */
	protected $object_type = 'post';

	/**
	 * Metadata key to use
	 *
	 * @var string
	 */
	protected $key = '';

	/**
	 * Whether the cache has been hit during the last call
	 *
	 * @var bool
	 */
	protected $hit = false;

	/**
	 * Stores an array of meta cache instances
	 *
	 * @var Meta_Cache[]
	 */
	private static $instances = [];

	/**
	 * Handles instantiating the class and storing the instance in a registry of instances.
	 * The registry key is composed of object type + key
	 *
	 * @param string|\WP_Post|\WP_Term $object_type @see self::parse_type()
	 * @param null|string              $key         metadata key
	 *
	 * @return self
	 */
	public static function instance( $object_type, $key = null ) {
		$object_type  = static::parse_type( $object_type );
		$instance_key = "{$object_type}--{$key}";

		if ( ! isset( static::$instances[ $instance_key ] ) ) {
			static::$instances[ $instance_key ] = new self( $object_type, $key );
		} else {
			static::$instances[ $instance_key ]->reset();
		}

		return static::$instances[ $instance_key ];
	}

	/**
	 * Parses the type into a string format
	 *
	 * @param string|\WP_Post|\WP_Term $type Accepted values: comment, post, term, user, or instances of the equivalent classes
	 *
	 * @return string
	 */
	public static function parse_type( $type ) {

		switch ( true ) {
			case is_string( $type ):
				if ( ! in_array( $type, [ 'post', 'user', 'comment', 'term' ] ) ) {
					throw new Cache_Exception( 'TD Meta cache: invalid type provided' );
				}
				$parsed_type = $type;
				break;

			case $type instanceof \WP_Post:
				$parsed_type = 'post';
				break;

			case $type instanceof \WP_Term:
				$parsed_type = 'term';
				break;

			case $type instanceof \WP_Comment:
				$parsed_type = 'comment';
				break;

			case $type instanceof \WP_Post_Type:
				$parsed_type = 'post_type';
				break;

			case $type instanceof \WP_User:
				$parsed_type = 'user';
				break;
			default:
				throw new Cache_Exception( 'TD Meta cache: invalid type provided' );
		}

		return $parsed_type;
	}

	/**
	 * @param string|\WP_Post|\WP_Term|\WP_User|\WP_Comment $type metadata type
	 * @param string                                        $key  Optional, it can be passed at each cache-related call
	 */
	private function __construct( $type, $key = null ) {
		$this->object_type = $type;
		$this->key         = $key;
	}

	/**
	 * Reset class instance
	 */
	public function reset() {
		$this->hit = false;
	}

	/**
	 * Set the `hit` flag on the instance
	 *
	 * @param mixed $value
	 */
	protected function record_hit( $value ) {
		$this->hit = $value !== false && $value !== null && $value !== '';
	}

	/**
	 * Allows setting the key on the fly
	 *
	 * @param string $key
	 *
	 * @return $this
	 */
	public function set_key( $key ) {
		$this->key = $key;

		return $this;
	}

	/**
	 * Get something from metadata cache
	 *
	 * @param int    $id
	 * @param string $key by default use the key from the class instance
	 */
	public function get( $id, $key = null ) {

		$value = get_metadata( $this->object_type, $id, $key ?: $this->key, true );

		$this->record_hit( $value );

		return $value;
	}

	/**
	 * Store something in meta
	 *
	 * @param int    $id
	 * @param mixed  $value
	 * @param string $key by default use the key from the class instance
	 *
	 * @return Meta_Cache
	 */
	public function set( $id, $value, $key = null ) {
		update_metadata( $this->object_type, $id, $key ?: $this->key, $value );

		return $this;
	}

	/**
	 * Store something in meta. Alias for set
	 *
	 * @param int    $id
	 * @param mixed  $value
	 * @param string $key by default use the key from the class instance
	 *
	 * @return Meta_Cache
	 */
	public function store( $id, $value, $key = null ) {
		return $this->set( $id, $value, $key );
	}

	/**
	 * Delete all metadata entries for an optional id, with an optional key
	 *
	 * @param null $id
	 * @param null $key
	 *
	 * @return self
	 */
	public function clear( $id = null, $key = null ) {
		$delete_all = empty( $id );
		delete_metadata( $this->object_type, $id, $key ?: $this->key, '', $delete_all );

		$this->reset(); // just to be sure

		return $this;
	}

	/**
	 * Alias for clear
	 *
	 * @param null $id
	 * @param null $key
	 *
	 * @return self
	 */
	public function delete( $id = null, $key = null ) {
		return $this->clear( $id, $key );
	}

	/**
	 * Retrieve a value and, if not found, persist the results of the callback function in cache
	 *
	 * @param int         $id
	 * @param callable    $callback_getter
	 * @param null|string $key
	 *
	 * @return mixed
	 */
	public function get_or_store( $id, $callback_getter = null, $key = null ) {
		$value = $this->get( $id, $key );

		if ( ! $this->hit && is_callable( $callback_getter ) ) {
			$value = $callback_getter();
			$this->set( $id, $value );
		}

		return $value;
	}

	/**
	 * Returns whether the cache has been hit or not during the last `get()` call
	 *
	 * @return bool
	 */
	public function hit() {
		return $this->hit;
	}
}

/**
 * This trait offers convenience methods to be used directly from a class
 */
trait Has_Meta_Cache {
	/**
	 * Override in classes - this is the key to be used with cache
	 *
	 * @var string
	 */
	protected $cache_key = '';

	/**
	 * Get something from cache or store it directly
	 *
	 * @param string   $object_type Object type. @see Meta_Cache::parse_type()
	 * @param int      $id          id of object
	 * @param callable $getter      callback to apply when no cached results are found - the results of the function call are stored in cache
	 * @param string   $key         meta key name where the cached data will be stored
	 *
	 * @return array|false|mixed
	 */
	public function cache_get( $object_type, $id, $getter = null, $key = null ) {
		$key = $key ?: $this->cache_key;

		return meta_cache( $object_type, $key )->get_or_store( $id, $getter, $key );
	}

	/**
	 * @param string $object_type Object type. @see Meta_Cache::parse_type()
	 * @param int    $id          id of object
	 * @param string $key         meta key name where the cached data will be stored
	 *
	 * @return Meta_Cache
	 */
	public function cache_clear( $object_type, $id = null, $key = null ) {
		$key = $key ?: $this->cache_key;

		return meta_cache( $object_type, $key )->clear( $id, $key );
	}

	/**
	 * Retrieve a Meta_cache object
	 *
	 * @param string $object_type Object type. @see Meta_Cache::parse_type()
	 * @param string $key         meta key name where the cached data will be stored
	 *
	 * @return Meta_Cache
	 */
	public function cache_instance( $object_type, $key = null ) {
		return meta_cache( $object_type, $key ?: $this->cache_key );
	}
}

/**
 * Helper function - allows retrieving a cache instance
 *
 * @param      $type
 * @param null $key
 *
 * @return Meta_Cache
 */
function meta_cache( $type, $key = null ) {
	return Meta_Cache::instance( $type, $key );
}

/**
 * Helper function to retrieve a cache instance for content sets
 *
 * @param string|\WP_Post|\WP_Term $type
 */
function content_set_cache( $type ) {
	return Meta_Cache::instance( $type, 'thrive_content_set' );
}
