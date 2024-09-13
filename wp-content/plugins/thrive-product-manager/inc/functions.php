<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-product-manager
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}


/**
 *
 * Replacement for WordPress's set_transient.
 * There are cases when set_transient() will simply fail if an external cache plugin declares the global $_wp_using_ext_object_cache
 * ( e.g. using memcached ) BUT the memcached server is not reachable.
 * In this case both set_transient() and get_transient() will not work.
 * Use this only if you really want the transient functionality to work regardless of caching plugins.
 * To be used in critical circumstances, e.g. storing licensing data - as it will add the option with autoload = 'yes', so don't use to store huge amounts of data!
 *
 * @param string $transient  Transient name
 * @param mixed  $value      Transient value
 * @param int    $expiration Optional. Time until expiration in seconds. Default null (no expiration).
 *
 * @return bool True if the value was set, false otherwise.
 */
function tpm_set_transient( $transient, $value, $expiration = null ) {

	/**
	 * Filter the expiration value
	 *
	 * @param int $expiration expiration time, in seconds
	 */
	$expiration = (int) apply_filters( "thrive_transient_expiration_{$transient}", (int) $expiration );

	/**
	 * Filter the transient value
	 *
	 * @param mixed $value
	 */
	$value = apply_filters( "thrive_transient_value_{$transient}", $value );

	$option_name = "_thrive_tr_{$transient}";

	if ( $expiration !== 0 ) {
		$expiration = time() + $expiration;
	}

	$data = get_option( $option_name );
	if ( false === $data ) {
		// does not exist. add it
		$result = add_option( $option_name, [
			'value' => $value,
			'exp'   => $expiration,
		] );
	} else {
		// transient found, update it
		$data['value'] = $value;
		$data['exp']   = $expiration;
		$result        = update_option( $option_name, $data );
	}

	return $result;
}

/**
 * To be used in conjunction with `thrive_set_transient`
 *
 * @param string $transient
 *
 * @return bool
 * @see thrive_set_transient()
 *
 */
function tpm_delete_transient( $transient ) {
	return delete_option( "_thrive_tr_{$transient}" );
}

/**
 * Replacement for WordPress's get_transient()
 * There are cases when get_transient() will simply fail if an external cache plugin declares
 * the global $_wp_using_ext_object_cache ( e.g. using memcached ) BUT the memcached server is not reachable.
 * In this case both set_transient() and get_transient() will not work.
 *
 * @param string $transient Transient name
 *
 * @return mixed transient value, or false if transient is not set or is expired
 */
function tpm_get_transient( $transient ) {
	$data = get_option( "_thrive_tr_{$transient}" );

	$value = is_array( $data ) && isset( $data['value'], $data['exp'] ) ? $data['value'] : false;

	/* if data has the correct format, then check expiration - if not zero and in the past, return false */
	if ( $value !== false && $data['exp'] && $data['exp'] < time() ) {
		$value = false;
	}

	return $value;
}
