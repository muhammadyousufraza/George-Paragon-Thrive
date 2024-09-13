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
 * Runtime cache class
 *
 * Used in Thrive Apprentice - products
 */
trait Runtime_Cache {

	private static $__global_rt_cache = [];

	/**
	 * @param array|string $key
	 *
	 * @return string
	 */
	public static function format_cache_key( $key ) {
		if ( is_array( $key ) ) {
			return implode( '|', $key );
		}

		return (string) $key;
	}

	/**
	 * @param string|array $key
	 * @param              $closure - Closure function
	 *
	 * @return mixed
	 */
	public static function get_from_global_cache( $key, $closure ) {
		$key = static::format_cache_key( $key );

		if ( false === array_key_exists( $key, static::$__global_rt_cache ) ) {
			static::$__global_rt_cache[ $key ] = $closure();
		}

		return static::$__global_rt_cache[ $key ];
	}

	/**
	 * Flush global runtime cache
	 *
	 * @param string|array $key
	 *
	 * @return void
	 */
	public static function flush_global_cache( $key ) {
		$key = static::format_cache_key( $key );

		unset( static::$__global_rt_cache[ $key ] );
	}
}
